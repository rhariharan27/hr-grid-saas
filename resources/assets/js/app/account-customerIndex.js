'use strict';

$(function () {
  var dt_user_table = $('.datatables-users'),
    userView = baseUrl + 'account/viewUser/';

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  if (dt_user_table.length) {
    var dt_user = dt_user_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'account/customerIndexAjax'
      },
      // Define columns using the keys from the server response.
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, // fake_id
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'subscription', name: 'subscription', orderable: false, searchable: false },
        { data: 'plan_info', name: 'plan_info', orderable: false, searchable: false },
        { data: 'email', name: 'email' },
        { data: 'email_verified_at', name: 'email_verified_at' },
        { data: 'action', name: 'action', orderable: false, searchable: false }
      ],
      columnDefs: [
        {
          // Responsive control column
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // Render the fake id (from DT_RowIndex)
          targets: 0,
          render: function (data, type, full, meta) {
            return '<span>' + data + '</span>';
          }
        },
        {
          // Render user full name with avatar (Column 2)
          targets: 2,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var name = data;
            // Generate a random color state for the avatar badge
            var stateNum = Math.floor(Math.random() * 6);
            var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            var state = states[stateNum];
            var initials = (name.match(/\b\w/g) || []);
            initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
            var avatarHtml = '<span class="avatar-initial rounded-circle bg-label-' + state + '">' + initials + '</span>';
            var output = '<div class="d-flex justify-content-start align-items-center user-name">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar avatar-sm me-4">' +
              avatarHtml +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column">' +
              '<a href="' + userView + full['id'] + '" class="text-heading text-truncate"><span class="fw-medium">' + name + '</span></a>' +
              '</div>' +
              '</div>';
            return output;
          }
        },
        {
          // Render subscription column (Column 3)
          targets: 3,
          render: function (data, type, full, meta) {
            if (data) {
              var output = '<div class="col">';
              output += '<div class="d-flex align-items-center gap-2">' +
                '<i class="bx bx-check-shield text-success"></i>' +
                '<span class="text-success">Subscribed</span>' +
                '</div>';
              output += '</div>';
              return output;
            } else {
              return '<span class="text-danger">No Subscription</span>';
            }
          }
        },
        {
          // Render plan_info column (Column 4)
          targets: 4,
          render: function (data, type, full, meta) {
            if (data) {
              return '<div class="d-flex flex-column">' +
                '<span class="fw-bold text-primary">' + data.plan + '</span>' +
                '<div class="mt-1">' +
                '<strong>Validity:</strong> ' +
                '<span class="text-muted">' + data.end_date + '</span>' +
                '</div>' +
                '<div>' +
                '<strong>Users:</strong> ' +
                '<span class="text-muted">Included: ' + data.included_users + ', Additional: ' + data.additional_users + '</span>' +
                '</div>' +
                '</div>';
            } else {
              return '<span class="text-danger">No Subscription</span>';
            }
          }
        },
        {
          // Render email column (Column 5)
          targets: 5,
          render: function (data, type, full, meta) {
            return '<span class="user-email">' + data + '</span>';
          }
        },
        {
          // Render email verification (Column 6)
          targets: 6,
          className: 'text-center',
          render: function (data, type, full, meta) {
            return data ? '<i class="bx fs-4 bx-check-shield text-success"></i>' : '<i class="bx fs-4 bx-shield-x text-danger"></i>';
          }
        },
        {
          // Render actions column (Column 7)
          targets: 7,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return '<div class="d-flex align-items-center gap-50">' +
              '<a href="' + userView + full['id'] + '" class="btn btn-sm btn-icon edit-record" data-id="' + full['id'] + '"><i class="bx bx-show"></i></a>' +
              '</div>';
          }
        }
      ],
      order: [[1, 'desc']],
      dom:
        '<"row"' +
        '<"col-md-2"<"ms-n2"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"fB>>' +
        '>t' +
        '<"row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [7, 10, 20, 50, 70, 100],
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search User',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      },
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-secondary dropdown-toggle mx-4',
          text: '<i class="bx bx-export me-2 bx-sm"></i>Export',
          buttons: [
            {
              extend: 'print',
              title: 'Users',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                format: {
                  body: function (inner, coldex, rowdex) {
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      result += item.innerText || item.textContent;
                    });
                    return result;
                  }
                }
              }
            },
            // Additional export buttons (CSV, Excel, PDF, Copy) can be configured similarly
          ]
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== ''
                ? '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '"><td>' + col.title + ':</td> <td>' + col.data + '</td></tr>'
                : '';
            }).join('');
            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      }
    });
    // Remove default btn-secondary class from export buttons if needed
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  }
});
