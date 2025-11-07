'use strict';

$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // --- URLs & Data from Blade ---
  // urls object: ajax, show, updateStatus, destroy
  // submissionStatuses (Array of strings)

  // --- Elements ---
  const dtElement = $('.datatables-contact-submissions');
  const filterStatus = $('#filterStatus');
  // const filterDateRange = $('#filterDateRange'); // Uncomment if date filter is added
  const filterBtn = $('#filterBtn');
  const resetBtn = $('#resetBtn');
  const offcanvasElement = document.getElementById('offcanvasViewSubmission');
  const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
  const submissionIdInput = $('#submission_id');
  const updateStatusSelect = $('#updateStatusSelect');
  const updateStatusBtn = $('#updateStatusBtn');

  // --- Helpers ---
  function getUrl(template, id) {
    if (!template) {
      console.error('URL template is undefined');
      return '#';
    }
    return template.replace('{id}', id);
  }

  function resetOffcanvas() {
    submissionIdInput.val('');
    $('#detail_date, #detail_name, #detail_email, #detail_status_badge').text('...');
    $('#submission_message_area').html('<p class="text-muted">Loading message...</p>'); // Clear message area
    updateStatusSelect.empty().append('<option value="">Select Status</option>'); // Reset dropdown
    updateStatusBtn.prop('disabled', false); // Re-enable button
  }

  // --- Initialize Filters ---
  if (filterStatus.length) {
    filterStatus.select2({ minimumResultsForSearch: -1 });
  }
  // Initialize date range picker if added
  // if (filterDateRange.length) { filterDateRange.flatpickr({ mode: 'range', dateFormat: "Y-m-d" }); }

  // --- DataTables Init ---
  let dtSubmissionsTable;
  if (dtElement.length && typeof urls.ajax !== 'undefined') {
    dtSubmissionsTable = dtElement.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: urls.ajax,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        // Send Filter Data
        data: function (d) {
          d.status = filterStatus.val() || 'all';
          // const dateRange = filterDateRange.val(); // Uncomment if using date filter
          // if(dateRange && dateRange.includes(' to ')) {
          //     const dates = dateRange.split(' to ');
          //     d.start_date = dates[0]; d.end_date = dates[1];
          // }
        }
      },
      columns: [
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'email', name: 'email' },
        { data: 'status', name: 'status' },
        { data: 'created_at', name: 'created_at' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
      ],
      order: [[4, 'desc']], // Default sort by Received At descending
      responsive: true,
      language: {
        search: '',
        searchPlaceholder: 'Search Name/Email...',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      }
    });
  } else {
    console.error('DataTable element or ajaxUrl not defined.');
  }

  // --- Filter/Reset Buttons ---
  filterBtn.on('click', function () {
    if (dtSubmissionsTable) dtSubmissionsTable.ajax.reload();
  });
  resetBtn.on('click', function () {
    // filterDateRange.flatpickr().clear(); // Uncomment if using date filter
    filterStatus.val('all').trigger('change');
    if (dtSubmissionsTable) dtSubmissionsTable.ajax.reload();
  });

  // --- Offcanvas Handling ---
  if (offcanvasElement) offcanvasElement.addEventListener('hidden.bs.offcanvas', resetOffcanvas);

  // --- View Submission Button ---
  dtElement.on('click', '.view-submission', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.show, id);
    resetOffcanvas(); // Reset before showing new data
    $('#offcanvasViewSubmissionLabel').text('Loading Submission...');

    $.get(url, function (data) {
      $('#offcanvasViewSubmissionLabel').text('Submission Details #' + data.id);
      submissionIdInput.val(data.id); // Store ID for status update/delete

      // Populate details
      $('#detail_date').text(data.created_at ? new Date(data.created_at).toLocaleString() : '-');
      $('#detail_name').text(data.name || '-');
      $('#detail_email').html(data.email ? '<a href="mailto:' + data.email + '">' + data.email + '</a>' : '-'); // Make email clickable

      // Status Badge (using logic from controller)
      let status = data.status || 'new';
      let badgeClass = 'bg-label-warning'; // Default to pending/new style
      switch (status.toLowerCase()) {
        case 'new':
          badgeClass = 'bg-label-info';
          break;
        case 'read':
          badgeClass = 'bg-label-secondary';
          break;
        case 'replied':
          badgeClass = 'bg-label-success';
          break;
        case 'archived':
          badgeClass = 'bg-label-dark';
          break;
      }
      $('#detail_status_badge').html(
        `<span class="badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`
      );

      // Message (escape HTML for safety, preserve newlines)
      let messageText = data.message || 'No message content.';
      $('#submission_message_area').text(messageText); // Use .text() to prevent HTML injection

      // Populate Status Update Dropdown
      updateStatusSelect.empty().append('<option value="">Change Status...</option>');
      submissionStatuses.forEach(function (statusOption) {
        // Don't allow setting back to 'new'? Maybe.
        // if (statusOption === 'new' && status !== 'new') return;
        let selected = statusOption === status ? 'selected' : '';
        updateStatusSelect.append(
          `<option value="${statusOption}" ${selected}>${statusOption.charAt(0).toUpperCase() + statusOption.slice(1)}</option>`
        );
      });

      offcanvas.show();
    }).fail(function (jqXHR) {
      Swal.fire('Error', jqXHR.responseJSON?.message || 'Could not load submission details.', 'error');
    });
  });

  // --- Update Status Button ---
  updateStatusBtn.on('click', function () {
    const selectedStatus = updateStatusSelect.val();
    const submissionId = submissionIdInput.val();
    const button = $(this);

    if (!submissionId || !selectedStatus) {
      toast('Please select a status to update.', { backgroundColor: '#f44336' }); // Simple toast error
      return;
    }

    const url = getUrl(urls.updateStatus, submissionId);
    const originalText = button.html();
    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
      url: url,
      type: 'POST',
      data: { status: selectedStatus },
      success: function (response) {
        if (response.code === 200) {
          toastr.success(response.message || 'Status updated.'); // Use Toastr for subtle feedback
          dtSubmissionsTable.ajax.reload(null, false); // Reload table
          // Update status badge in offcanvas immediately
          let badgeClass = 'bg-label-secondary';
          switch (selectedStatus.toLowerCase() /* ... update badgeClass based on selectedStatus ... */) {
          }
          $('#detail_status_badge').html(
            `<span class="badge ${badgeClass}">${selectedStatus.charAt(0).toUpperCase() + selectedStatus.slice(1)}</span>`
          );
          // Maybe hide offcanvas after update?
          // offcanvas.hide();
        } else {
          Swal.fire('Error', response.message || 'Could not update status.', 'error');
        }
      },
      error: function (jqXHR) {
        Swal.fire('Error', jqXHR.responseJSON?.message || 'An error occurred.', 'error');
      },
      complete: function () {
        button.prop('disabled', false).html(originalText); // Restore button text
      }
    });
  });

  // --- Delete Submission ---
  dtElement.on('click', '.delete-submission', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.destroy, id);

    Swal.fire({
      title: 'Are you sure?',
      text: 'Delete this contact submission?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        Swal.fire({
          title: 'Deleting...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        $.ajax({
          url: url,
          type: 'DELETE',
          success: function (response) {
            Swal.close();
            if (response.code === 200) {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: response.message,
                timer: 1500,
                showConfirmButton: false
              });
              dtSubmissionsTable.ajax.reload(null, false);
            } else {
              Swal.fire('Error', response.message || 'Could not delete.', 'error');
            }
          },
          error: function (jqXHR) {
            Swal.close();
            Swal.fire('Error', jqXHR.responseJSON?.message || 'An error occurred.', 'error');
          }
        });
      }
    });
  });
}); // End document ready
