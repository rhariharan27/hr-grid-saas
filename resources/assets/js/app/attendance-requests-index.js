/* Attendance Requests Index */

'use strict';

$(function () {
  // Initialize Flatpickr for the date input
  const datePicker = $("#date").flatpickr({
    dateFormat: "Y-m-d",
    allowInput: true,
    onChange: function(selectedDates, dateStr, instance) {
      dataTable.draw();
    }
  });

  // Initialize Select2 for the user dropdown
  const userSelect = $('#userId').select2({
    placeholder: "Select an Employee",
    allowClear: true
  });

  // Add event listener for user dropdown change
  userSelect.on('change', function() {
    dataTable.draw();
  });

  // DataTable Initialization
  var dataTable = $('#attendanceRequestsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'attendance-requests/indexAjax', // Route for AJAX data
      data: function (d) {
        d.userId = $('#userId').val();
        d.date = $('#date').val();
      }
    },
    columns: [
      { data: 'id', name: 'id', orderable: true, searchable: false },
      { data: 'employee', name: 'user.first_name', orderable: true, searchable: true },
      { data: 'date', name: 'date', orderable: true, searchable: false },
      { data: 'check_in', name: 'check_in', orderable: false, searchable: false },
      { data: 'check_out', name: 'check_out', orderable: false, searchable: false },
      { data: 'reason', name: 'reason', orderable: false, searchable: false },
      { data: 'status', name: 'status', orderable: true, searchable: true },
      { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ],
    order: [[0, 'desc']],
    dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    displayLength: 10,
    lengthMenu: [10, 25, 50, 100],
    buttons: []
  });

  // SweetAlert for Approve/Reject
  $('#attendanceRequestsTable').on('click', '.btn-approve, .btn-reject', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var action = $btn.hasClass('btn-approve') ? 'approve' : 'reject';
    var url = $btn.data('url');
    var status = action === 'approve' ? 'approved' : 'rejected';
    Swal.fire({
      title: 'Are you sure?',
      text: 'You are about to ' + action + ' this request.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, ' + action + ' it!',
      cancelButtonText: 'Cancel',
      customClass: { confirmButton: 'btn btn-success', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.isConfirmed) {
        $.post(url, { status: status, _token: $('meta[name="csrf-token"]').attr('content') }, function (response) {
          Swal.fire('Success!', response.message, 'success');
          dataTable.draw();
        }).fail(function (xhr) {
          Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong.', 'error');
        });
      }
    });
  });
});
