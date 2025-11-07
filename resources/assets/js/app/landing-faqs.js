'use strict';

$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // --- URLs & Elements from Blade ---
  // urls object: ajax, store, update, destroy, toggleStatus, getFaq, updateOrder
  const dtElement = $('.datatables-landing-faqs');
  const offcanvasElement = document.getElementById('offcanvasFaqForm');
  const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
  const faqForm = document.getElementById('faqForm');
  const formMethodInput = document.getElementById('formMethod');
  const faqIdInput = document.getElementById('faq_id');
  const sortableList = document.getElementById('faqSortableList'); // tbody for SortableJS
  const saveBtn = $('#saveFaqBtn');

  // --- Helpers ---
  function getUrl(template, id) {
    if (!template) {
      console.error('URL template is undefined');
      return '#';
    }
    return template.replace('{id}', id);
  }

  function resetFormValidation(form) {
    const jqForm = $(form);
    jqForm.find('.is-invalid').removeClass('is-invalid');
    jqForm.find('.invalid-feedback').text('');
  }

  function resetOffcanvas() {
    resetFormValidation(faqForm);
    faqForm.reset();
    faqIdInput.value = '';
    formMethodInput.value = 'POST'; // Default to POST for store
    faqForm.action = urls.store;
    $('#offcanvasFaqFormLabel').text('Add FAQ');
    $('#faq_sort_order').val('0'); // Reset hidden sort order
    $('#faq_is_active').prop('checked', true);
    saveBtn.prop('disabled', false).html('Save FAQ');
    // If using WYSIWYG editor for 'answer', reset it here:
    // Example: tinymce.get('faq_answer')?.setContent('');
  }

  // --- DataTables Init ---
  let dtFaqTable;
  if (dtElement.length && typeof urls.ajax !== 'undefined') {
    dtFaqTable = dtElement.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: urls.ajax, type: 'POST', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } },
      columns: [
        // { data: 'handle', name: 'handle', orderable: false, searchable: false, className: 'text-center p-1' }, // Handle
        { data: 'id', name: 'id' },
        { data: 'question', name: 'question' },
        { data: 'answer', name: 'answer', orderable: false },
        // { data: 'sort_order', name: 'sort_order' }, // Hide if using drag-drop
        { data: 'is_active', name: 'is_active', className: 'text-center' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
      ],
      // Disable default ordering if relying solely on sortable
      // order: [], // No initial order needed if using sortable
      order: [[0, 'asc']], // Order by sort_order defined in controller query
      // Set row ID for SortableJS
      rowId: function (data) {
        return 'faq-' + data.id;
      },
      // Disable sorting on the handle column
      columnDefs: [{ orderable: false, targets: 0 }],
      drawCallback: function () {
        // Reinitialize SortableJS after table draw (for pagination etc.)
        //initializeSortable();
      },
      responsive: true,
      language: {
        search: '',
        searchPlaceholder: 'Search..',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      }
    });
  } else {
    console.error('DataTable element or ajaxUrl not defined.');
  }

  /*
    // --- SortableJS Initialization for Drag & Drop ---
    let sortableInstance = null;

    function initializeSortable() {
      if (sortableInstance) {
        sortableInstance.destroy(); // Destroy previous instance if exists
      }
      if (sortableList && typeof urls.updateOrder !== 'undefined') {
        sortableInstance = Sortable.create(sortableList, {
          animation: 150,
          handle: '.sort-handle', // Class for the drag handle icon
          ghostClass: 'sortable-ghost', // Class for the visual ghost effect
          onEnd: function (evt) {
            var itemOrder = [];
            // Get the new order of IDs from the data-id attribute of rows
            $(sortableList)
              .children('tr')
              .each(function () {
                // Extract ID from the rowId set by DataTables (e.g., "faq-5")
                const id = $(this).attr('id')?.replace('faq-', '');
                if (id) {
                  itemOrder.push(id);
                }
              });

            // Send the new order to the backend
            $.ajax({
              url: urls.updateOrder,
              type: 'POST',
              data: { order: itemOrder }, // Send array of IDs in order
              success: function (response) {
                if (response.code === 200) {
                  toastr.success(response.message || 'Order updated!'); // Use Toastr for brief feedback
                  dtFaqTable.ajax.reload(null, false); // Reload to confirm order
                } else {
                  toastr.error(response.message || 'Failed to update order.');
                }
              },
              error: function (jqXHR) {
                console.error('Order Update Error:', jqXHR);
                toastr.error('Error updating order.');
              }
            });
          }
        });
      }
    }
  */

  // Initialize sortable on page load
  // initializeSortable(); // Initialized in drawCallback now

  // --- Offcanvas Show/Hide/Reset ---
  $('.add-faq').on('click', resetOffcanvas);
  if (offcanvasElement) offcanvasElement.addEventListener('hidden.bs.offcanvas', resetOffcanvas);

  // --- Edit FAQ Button ---
  dtElement.on('click', '.edit-faq', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.getFaq, id); // Use correct URL key
    resetOffcanvas();
    $('#offcanvasFaqFormLabel').text('Edit FAQ');
    formMethodInput.value = 'PUT'; // Signal update method
    faqForm.action = getUrl(urls.update, id); // Set update URL

    $.get(url, function (data) {
      faqIdInput.value = data.id;
      $('#faq_question').val(data.question);
      $('#faq_answer').val(data.answer); // Set textarea value
      // If using WYSIWYG: tinymce.get('faq_answer')?.setContent(data.answer);
      $('#faq_sort_order').val(data.sort_order);
      $('#faq_is_active').prop('checked', data.is_active);
      offcanvas.show();
    }).fail(function () {
      Swal.fire('Error', 'Could not fetch FAQ details.', 'error');
    });
  });

  // --- Form Submission ---
  faqForm.addEventListener('submit', function (e) {
    e.preventDefault();
    resetFormValidation(this);

    // If using WYSIWYG, update the textarea value before getting FormData
    // Example: if (typeof tinymce !== 'undefined' && tinymce.get('faq_answer')) { $('#faq_answer').val(tinymce.get('faq_answer').getContent()); }

    const formData = new FormData(this);
    const url = this.action;
    const method = 'POST';
    const faqId = faqIdInput.value;

    if (faqId) {
      formData.append('_method', 'PUT');
    } // Add method spoofing

    var originalButtonText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.ajax({
      url: url,
      type: method,
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.code === 200) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: response.message,
            timer: 1500,
            showConfirmButton: false
          });
          dtFaqTable.ajax.reload(null, false); // Reload table
          offcanvas.hide();
        } else {
          Swal.fire('Error', response.message || 'Save failed.', 'error');
        }
      },
      error: function (jqXHR) {
        console.error('Save FAQ Error:', jqXHR);
        let message = 'An error occurred.';
        let errors = jqXHR.responseJSON?.errors;

        if (jqXHR.status === 422 && errors) {
          message = jqXHR.responseJSON.message || 'Please correct the errors below.';
          $.each(errors, function (key, value) {
            let input = $('[name="' + key + '"]');
            input.addClass('is-invalid');
            let errorDiv = input.siblings('.invalid-feedback').first();
            if (!errorDiv.length) errorDiv = $('<div class="invalid-feedback"></div>').insertAfter(input);
            errorDiv.text(value[0]);
          });
          Swal.fire({ icon: 'error', title: 'Validation Error', html: message });
          $('.is-invalid').first().focus();
        } else {
          message = jqXHR.responseJSON?.message || message;
          Swal.fire({ icon: 'error', title: 'Error', html: message });
        }
      },
      complete: function () {
        saveBtn.prop('disabled', false).html(originalButtonText);
      }
    });
  });

  // --- Toggle Status ---
  dtElement.on('click', '.status-toggle', function () {
    var id = $(this).data('id');
    var checkbox = $(this);
    var url = getUrl(urls.toggleStatus, id);
    $.ajax({
      url: url,
      type: 'POST',
      success: function (response) {
        if (response.code !== 200) {
          Swal.fire('Error', response.message || 'Could not update status.', 'error');
          checkbox.prop('checked', !checkbox.prop('checked'));
        }
        // Maybe add a toastr success notification
      },
      error: function (err) {
        Swal.fire('Error', 'Failed to update status.', 'error');
        checkbox.prop('checked', !checkbox.prop('checked'));
      }
    });
  });

  // --- Delete FAQ ---
  dtElement.on('click', '.delete-faq', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.destroy, id);
    Swal.fire({
      title: 'Are you sure?',
      text: 'Delete this FAQ?',
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
              dtFaqTable.ajax.reload(null, false);
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
