/* Attendance Index */

'use strict';

$(function () {
  console.log('Attendance Index JS Loaded');

  // Initialize Flatpickr for the date input
  const datePicker = $("#date").flatpickr({
    dateFormat: "Y-m-d",
    defaultDate: "today", // Set default to today
    onChange: function(selectedDates, dateStr, instance) {
      // Trigger DataTable reload when date changes
      dataTable.draw();
    }
  });

  // Initialize Select2 for the user dropdown
  const userSelect = $('#userId').select2({
    placeholder: "Select an Employee",
    allowClear: true
  });

  // DataTable Initialization
  var dataTable = $('#attendanceTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'attendance/indexAjax', // Make sure this route exists and points to indexAjax method
      data: function (d) {
        // Pass filter values to the backend
        d.userId = $('#userId').val();
        d.date = $('#date').val(); // Get value from Flatpickr input
      }
    },
    columns: [
      { data: 'id', name: 'id', orderable: true, searchable: false },
      { data: 'user', name: 'user.first_name', orderable: true, searchable: true }, // Allow searching by user name
      { data: 'shift', name: 'shift.name', orderable: false, searchable: false }, // Assuming shift relationship exists
      { data: 'first_check_in', name: 'first_check_in', orderable: true, searchable: false },
      { data: 'last_check_out', name: 'last_check_out', orderable: true, searchable: false },
      { data: 'duration', name: 'duration', orderable: false, searchable: false },
      { data: 'status', name: 'status', orderable: true, searchable: true }, // Allow searching/sorting by status
      { data: 'log_count', name: 'log_count', orderable: false, searchable: false },
      // { data: 'actions', name: 'actions', orderable: false, searchable: false } // Uncomment if you add actions
    ],
    order: [[0, 'desc']], // Default order by ID descending
    dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>', // Standard DataTables DOM structure
    displayLength: 10,
    lengthMenu: [10, 25, 50, 100],
    buttons: [
      // Add export buttons etc. if needed
      // {
      //   extend: 'collection',
      //   className: 'btn btn-label-primary dropdown-toggle me-2',
      //   text: '<i class="ti ti-file-export me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
      //   buttons: [
      //     { extend: 'print', /* config */ },
      //     { extend: 'csv', /* config */ },
      //     { extend: 'excel', /* config */ },
      //     { extend: 'pdf', /* config */ },
      //     { extend: 'copy', /* config */ }
      //   ]
      // }
    ],
    responsive: {
        details: {
            display: $.fn.dataTable.Responsive.display.modal({
                header: function (row) {
                    var data = row.data();
                    return 'Details for ' + (data.user ? $(data.user).find('.fw-medium').text() : 'N/A'); // Extract name from HTML
                }
            }),
            type: 'column',
            renderer: $.fn.dataTable.Responsive.renderer.tableAll({ tableClass: 'table' })
        }
    }
  });

  // Set the title in the DataTable header
  $('div.head-label').html('<h5 class="card-title mb-0">Attendance Records</h5>');

  // --- Event Listeners for Filters ---

  // Reload DataTable when user selection changes
  $('#userId').on('change', function () {
    console.log('User filter changed:', $(this).val());
    dataTable.draw();
  });

  // Date change is handled by Flatpickr's onChange event

});
