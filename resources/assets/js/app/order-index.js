$(function() {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  let dtTable = $('.datatables-orders');

  if (dtTable.length) {
    var dtOrders = dtTable.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'orders/getListAjax',   // e.g. your route('tenant.orders.getListAjax')
        data: function(d) {
          d.dateFilter = $('#dateFilter').val();
        },
        error: function(xhr, error, code) {
          console.log('Error: ' + error);
          console.log('Code: ' + code);
          console.log('Response: ' + xhr.responseText);
        }
      },
      columns: [
        {data: null, defaultContent: '', orderable: false, searchable: false}, // for responsive control
        {data: 'id'},
        // If you used "user" or "user_name" in your addColumn, match it here
        {data: 'user', orderable: false, searchable: false},
        {data: 'plan_display', orderable: false, searchable: false},
        {data: 'type'},
        {data: 'status'},
        {data: 'amount'},
        {data: 'created_date'},  // you named it in addColumn('created_date', function ($order) {...})
        {data: 'actions', orderable: false, searchable: false}
      ],
      order: [[1, 'desc']],
      dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
      lengthMenu: [7, 10, 20, 50, 70, 100],
      language: {
        search: '',
        searchPlaceholder: 'Search Orders',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      },
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function(row) {
              var data = row.data();
              return 'Details of Order #' + data['id'];
            }
          }),
          type: 'column',
          renderer: function(api, rowIdx, columns) {
            let data = $.map(columns, function(col, i) {
              return col.title
                ? '<tr data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">'+
                '<td>'+col.title+':</td> <td>'+col.data+'</td></tr>'
                : '';
            }).join('');
            return data ? $('<table class="table"/>').append(data) : false;
          }
        }
      }
    });

    // Hook the date filter
    $('#dateFilter').on('change', function() {
      dtOrders.draw();
    });

    // Deletion
    $(document).on('click', '.delete-record', function() {
      let id = $(this).data('id');

      Swal.fire({
        title: 'Are you sure?',
        text: "This will delete the order permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function(result) {
        if (result.value) {
          $.ajax({
            type: 'DELETE',
            url: baseUrl + 'orders/deleteAjax/' + id,
            success: function(response) {
              if (response.status === 'success') {
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  text: 'Order deleted successfully.',
                  customClass: { confirmButton: 'btn btn-success' }
                });
                dtOrders.draw();
              } else {
                Swal.fire('Error', response.message || 'Error deleting order', 'error');
              }
            },
            error: function(error) {
              Swal.fire('Error', 'An error occurred while deleting.', 'error');
            }
          });
        }
      });
    });

    // Show order details
    $(document).on('click', '.show-order-details', function() {
      let id = $(this).data('id');

      $.get(baseUrl + 'orders/getByIdAjax/' + id, function(response) {
        if (response.status === 'success') {
          let data = response.data;
          // Fill your offcanvas or modal fields
          $('#offcanvasOrderId').text(data.id);
          $('#offcanvasPlanName').text(data.planName || '-');
          $('#offcanvasUserName').text(data.userName || '-');
          $('#offcanvasStatus').text(data.status);
          $('#offcanvasAmount').text(data.amount);
          $('#offcanvasTotalAmount').text(data.totalAmount);
          $('#offcanvasPaymentGateway').text(data.paymentGateway || '-');
          $('#offcanvasCreatedAt').text(data.createdAt || '-');
          $('#offcanvasPaidAt').text(data.paidAt || '-');
          $('#offcanvasType').text(data.type);
          $('#offcanvasAdditionalUsers').text(data.additionalUsers);

          // now the offcanvas is open
        } else {
          console.log(response.message);
        }
      }).fail(function(err) {
        console.log('Error fetching order details', err);
      });
    });
  }
});
