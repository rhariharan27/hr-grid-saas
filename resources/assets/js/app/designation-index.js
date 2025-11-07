$(function () {
  var dt_table = $('.datatables-designations');
  loadDepartments();

  // Ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  if (dt_table.length) {
    var dt_designation = dt_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'designations/indexAjax',
        error: function (xhr, error, code) {
          console.log('Error: ' + error);
          console.log('Code: ' + code);
          console.log('Response: ' + xhr.responseText);
        }
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'name' },
        { data: 'code' },
        { data: 'department_name' },
        { data: 'notes' },
        { data: 'is_approver_text', name: 'is_approver_text' },
        { data: 'status' },
        { data: 'action' }
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
          // Render the first column index (if needed)
          targets: 0,
          render: function (data, type, full, meta) {
            return '<span>' + full.DT_RowIndex + '</span>';
          }
        },
        {
          // You can also customize other columns if needed
          targets: 6,
          className: 'text-start',
          render: function (data, type, full, meta) {
            return '<span>' + data+'' + '</span>';
          }
        },
        {
          // Render status as a toggle switch
          targets: 7,
          render: function (data, type, full, meta) {
            var status = full['status'].toLowerCase();
            var checked = status === 'active' ? 'checked' : '';
            return `
              <div class="d-flex justify-content-left">
                <label class="switch mb-0">
                  <input type="checkbox" class="switch-input status-toggle" data-id="${full['id']}" ${checked} />
                  <span class="switch-toggle-slider">
                    <span class="switch-on"><i class="bx bx-check"></i></span>
                    <span class="switch-off"><i class="bx bx-x"></i></span>
                  </span>
                </label>
              </div>
            `;
          }
        },
        {
          // Actions column rendering
          targets: 8,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return full.action;
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
        searchPlaceholder: 'Search Designation',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      },
      buttons: [
        // Your export buttons configuration
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
  }

  var offCanvasForm = $('#offcanvasAddOrUpdateDesignation');

  $(document).on('click', '.add-new', function () {
    $('#id').val('');
    $('#name').val('');
    $('#code').val('');
    $('#notes').val('');
    $('#offcanvasDesignationLabel').html('Add Designation');
    fv.resetForm(true);

  });



  const addDesignationForm = document.getElementById('designationForm');

  $(document).on('click', '.edit-record', function () {
    var id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // changing the title of offcanvas
    $('#offcanvasDesignationLabel').html('Edit Leave Type');

    // get data
    $.get(`${baseUrl}designations\/getByIdAjax\/${id}`, function (data) {
      console.log(data);
      $('#id').val(data.id);
      $('#name').val(data.name);
      $('#code').val(data.code);
      $('#notes').val(data.notes);
      $('#department_id').val(data.department_id);
      //Is Approver checked
      if (data.is_approver) {
        $('#is_approver').prop('checked', true);
      } else {
        $('#is_approver').prop('checked', false);
      }
    });
  });

  const fv = FormValidation.formValidation(addDesignationForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'The name is required'
          }
        }
      },
      code: {
        validators: {
          notEmpty: {
            message: 'The code is required'
          },
          remote: {
            url: `${baseUrl}designations/checkCodeValidationAjax`,
            message: 'The code is already taken',
            method: 'GET',
            data: function () {
              return {
                id: addDesignationForm.querySelector('[name="id"]').value
              };
            }
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          return '.mb-6';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    // adding or updating user when form successfully validate
    $.ajax({
      data: $('#designationForm').serialize(),
      url: `${baseUrl}designations/addOrUpdateAjax`,
      type: 'POST',
      success: function (response) {
        if (response.status === 'success') {
          offCanvasForm.offcanvas('hide');
          // sweetalert
          Swal.fire({
            icon: 'success',
            title: `Successfully ${response.data}!`,
            text: `Designation ${response.data} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });

          dt_designation.draw();
        }
      },
      error: function (err) {
        var responseJson = JSON.parse(err.responseText);
        console.log('Error Response: ' + JSON.stringify(responseJson));
        if (err.code === 400) {
          Swal.fire({
            title: 'Unable to create designation',
            text: `${responseJson.data}`,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        } else {
          Swal.fire({
            title: 'Unable to create designation',
            text: 'Please try again',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }
    });
  });

  // clearing form data when offcanvas hidden
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    $('#notes').val('');
  });



  // To delete the Designation
  $(document).on('click', '.delete-record', function () {
    var id = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    // Hide responsive modal on small screens
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // SweetAlert for delete confirmation
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        deleteDesignation(id);
      }
    });
  });

  //  To change the status

  $(document).on('change', '.status-toggle', function () {
    var id = $(this).data('id');
    var status = $(this).is(':checked') ? 'Active' : 'Inactive';

    $.ajax({
      url: `${baseUrl}designations/changeStatus/${id}`,
      type: 'POST',
      data: {
        status: status,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        console.log(response);

        dt_designation.draw();
      },
      error: function (response) {
        console.log(response);
      }
    });
  });

  function deleteDesignation(designationId) {
    // AJAX request to delete department
    $.ajax({
      type: 'DELETE',
      url: `${baseUrl}designations/deleteAjax/${designationId}`,
      success: function () {
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'The Designation has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
        // Redraw DataTable or reload the data
        dt_designation.draw();
      },
      error: function (error) {
        // console.log(error);
        Swal.fire({
          title: 'Error',
          text: 'Something went wrong',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  }




  async function getDepartments() {
    try {
      var response = await $.ajax({
        url: `${baseUrl}departments/getListAjax`,
        type: 'GET'
      });
      if (response && response.status === 'success') {
        return response.data;
      } else {
        return [];
      }
    } catch (error) {
      console.error('Error fetching departments:', error);
      return [];
    }

  }
  function loadDepartments() {
    getDepartments().then(function (departments) {
      if (departments.length > 0) {
        $('#department_id').empty();
        $('#department_id').append('<option value="" selected>Select department</option>');
        departments.forEach(function (department) {
          $('#department_id').append(`<option value="${department.id}">${department.name}</option>`);
        });
      }
    });
  }


});

