$(function () {
  var dt_table = $('.datatables-departments');

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // department datatable

  if (dt_table.length) {
    var dt_department = dt_table.DataTable({
      initComplete: function () {
        $('#loader').attr('style', 'display:none');
        $('.card-datatable').show();
      },
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'departments/indexAjax',
        error: function (xhr, error, code) {
          console.log('Error: ' + error);
          console.log('Code: ' + code);
          console.log('Response: ' + xhr.responseText);
        }
      },
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'id' },
        { data: 'name' },
        { data: 'code' },
        { data: 'parent_id' },
        { data: 'notes' },
        { data: 'status' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          // For Responsive
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
          // id
          targets: 1,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $id = full['id'];
            return '<span class="id">' + $id + '</span>';
          }
        },
        {
          // Name
          targets: 2,
          className: 'text-start',
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var $name = full['name'];
            return `<span class="department-name">${$name}</span>`;
          }
        },
        {
          // Code
          targets: 3,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $code = full['code'];
            return `<span class="department-code">${$code}</span>`;
          }
        },
        {
          // Parent Department
          targets: 4,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $parentName = full.parent_department ? full.parent_department : 'No Parent';
            return `<span class="department-parent">${$parentName}</span>`;
          }
        },
        {
          // Notes
          targets: 5,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $notes = full['notes'] ?? 'N/A';
            return `<span class="department-notes">${$notes}</span>`;
          }
        },

        {
          // Status
          targets: 6,
          className: 'text-start',
          render: function (data, type, full, meta) {
            var $status = full['status'];
            var checked = $status === 'active' ? 'checked' : '';

            return `
                        <div class="d-flex justify-content-left">
                            <label class="switch mb-0">
                                <input type="checkbox" class="switch-input status-toggle" id="statusToggle${full['id']}" data-id="${full['id']}" ${checked} />
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
          // Actions
          targets: 7,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return `
                        <div class="d-flex align-items-left gap-50">
                            <button class="btn btn-sm btn-icon edit-department" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDepartment">
                                <i class="bx bx-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-icon delete-department" data-id="${full['id']}">
                                <i class="bx bx-trash text-danger"></i>
                            </button>
                        </div>
                    `;
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
        searchPlaceholder: 'Search Department',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      },
      // Buttons with dropdown
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-secondary dropdown-toggle mx-4',
          text: '<i class="bx bx-export me-2 bx-sm"></i>Export',
          buttons: [
            {
              extend: 'print',
              title: 'Department',
              text: '<i class="bx bx-printer me-2" ></i>Print',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              },
              customize: function (win) {
                //customize print view for dark
                $(win.document.body)
                  .css('color', config.colors.headingColor)
                  .css('border-color', config.colors.borderColor)
                  .css('background-color', config.colors.body);
                $(win.document.body)
                  .find('table')
                  .addClass('compact')
                  .css('color', 'inherit')
                  .css('border-color', 'inherit')
                  .css('background-color', 'inherit');
              }
            },
            {
              extend: 'csv',
              title: 'Department',
              text: '<i class="bx bx-file me-2" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be print
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'excel',
              text: '<i class="bx bxs-file-export me-2"></i>Excel',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'pdf',
              title: 'Department',
              text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be display
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            },
            {
              extend: 'copy',
              title: 'Department',
              text: '<i class="bx bx-copy me-2" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: {
                columns: [1, 2, 3, 4, 5],
                // prevent avatar to be copy
                format: {
                  body: function (inner, coldex, rowdex) {
                    if (inner.length <= 0) return inner;
                    var el = $.parseHTML(inner);
                    var result = '';
                    $.each(el, function (index, item) {
                      if (item.classList !== undefined && item.classList.contains('name')) {
                        result = result + item.lastChild.firstChild.textContent;
                      } else if (item.innerText === undefined) {
                        result = result + item.textContent;
                      } else result = result + item.innerText;
                    });
                    return result;
                  }
                }
              }
            }
          ]
        }
      ],
      // For Responsive Popup
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
                ? '<tr data-dt-row="' +
                    col.rowIndex +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');
            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      }
    });
    // To remove default btn-secondary in export buttons
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  }

  var offCanvasForm = $('#offcanvasAddDepartment');
  // changing the title
  $('.add-new-department').on('click', function () {
    $('#departmentId').val('');
    $('#offcanvasAddDepartmentLabel').html('Add Department');
    loadDepartmentList();
    $('#parent_department').val(null).trigger('change');
  });

  const addNewDepartmentForm = document.getElementById('addNewDepartmentForm');

  // To Edit the location
  $(document).on('click', '.edit-department', function () {
    var departmentId = $(this).data('id'),
      dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    loadDepartmentList();

    // changing the title of offcanvas
    $('#offcanvasAddDepartmentLabel').html('Edit Department');

    // set department data
    setDepartmentData(departmentId);
  });

  const fv = FormValidation.formValidation(addNewDepartmentForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'The  name is required'
          }
        }
      },
      code: {
        validators: {
          notEmpty: {
            message: 'The Department code is required'
          },
          stringLength: {
            min: 3,
            max: 10,
            message: 'The code must be 3 to 10 characters'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: function (field, ele) {
          return '.mb-6';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    addOrUpdateDepartment();
  });

  // Clearing form data when offcanvas is hidden
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    $('#notes').val('');
  });

  // To Delete the department
  $(document).on('click', '.delete-department', function () {
    var departmentId = $(this).data('id'),
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
        deleteDepartment(departmentId);
      }
    });
  });

  //  To change the status

  $(document).on('change', '.status-toggle', function () {
    var id = $(this).data('id');
    var status = $(this).is(':checked') ? 'Active' : 'Inactive';

    $.ajax({
      url: `${baseUrl}departments/changeStatus/${id}`,
      type: 'POST',
      data: {
        status: status,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        console.log(response);

        dt_department.draw();
      },
      error: function (response) {
        console.log(response);
      }
    });
  });

  function loadDepartmentList() {
    $.ajax({
      url: baseUrl + 'departments/getParentDepartments',
      type: 'GET',
      success: function (response) {
        let parentDropdown = $('#parent_department');
        parentDropdown.empty();
        parentDropdown.append('<option value="" selected>Select parent department</option>');
        response.forEach(function (department) {
          parentDropdown.append(`<option value="${department.id}">${department.name}</option>`);
        });
      },
      error: function (xhr, status, error) {
        console.error('Error fetching parent departments:', error);
      }
    });
  }

  function addOrUpdateDepartment() {
    // creating of Department on success fo validation
    $.ajax({
      data: $('#addNewDepartmentForm').serialize(),
      url: `${baseUrl}departments/addOrUpdateDepartmentAjax`,
      type: 'POST',
      success: function (response) {
        console.log('Save Department Response: ' + JSON.stringify(status));
        if (response.status === 'success') {
          offCanvasForm.offcanvas('hide');
          // SweetAlert for success
          Swal.fire({
            icon: 'success',
            title: `Successfully ${response.data}!`,
            text: ` Department ${response.data} Successfully.`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });

          // time to reload
          dt_department.draw();
        }
      },
      error: function (err) {
        Swal.fire({
          title: 'Duplicate Entry!',
          text: 'Your Code number should be unique.',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  }

  function deleteDepartment(departmentId) {
    // AJAX request to delete department
    $.ajax({
      type: 'DELETE',
      url: `${baseUrl}departments/deleteAjax/${departmentId}`,
      success: function () {
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'The Department has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
        // Redraw DataTable or reload the data
        dt_department.draw();
      },
      error: function (error) {
        console.log(error);
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

  function setDepartmentData(departmentId) {
    $.get(`${baseUrl}departments/getDepartmentAjax/${departmentId}`, function (response) {
      console.log(response);
      if (response.status === 'success') {
        let data = response.data;
        $('#departmentId').val(data.id);
        $('#name').val(data.name);
        $('#code').val(data.code);
        $('#notes').val(data.notes);
        if (data.parent_id) {
          $('#parent_department').val(data.parent_id).trigger('change');
        } else {
          $('#parent_department').val('');
        }
      }
    });
  }
});
