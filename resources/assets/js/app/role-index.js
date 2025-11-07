/**
 * Add new role Modal JS
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  var roleAdd = document.querySelector('.add-new-role'),
    roleEdit = document.querySelectorAll('.edit-role'),
    roleTitle = document.querySelector('.role-title');

  var offCanvasForm = $('#addRoleForm');
  var addRoleModel = $('#addOrUpdateRoleModal');

  roleAdd.onclick = function () {
    roleTitle.innerHTML = 'Add New Role';
    $('#id').val('');
    $('#name').val('');
    $('#isMultiCheckInEnabled').prop('checked', false);
    $('#mobileAppAccess').prop('checked', false);
    $('#webAppAccess').prop('checked', false);
    $('#locationActivityTracking').prop('checked', false);
  };

  document.querySelectorAll('.edit').forEach(function (element) {
    element.addEventListener('click', function () {
      var role = JSON.parse(element.getAttribute('data-value'));

      $('#id').val(role['id']);
      $('#name').val(role['name']);
      $('#isMultiCheckInEnabled').prop('checked', role['is_multiple_check_in_enabled']);
      $('#mobileAppAccess').prop('checked', role['is_mobile_app_access_enabled']);
      $('#webAppAccess').prop('checked', role['is_web_access_enabled']);
      $('#locationActivityTracking').prop('checked', role['is_location_activity_tracking_enabled']);
      $('.role-title').text('Update Role');
      addRoleModel.modal('show');
    });
  });

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  (function () {
    // add role form validation
    FormValidation.formValidation(document.getElementById('addRoleForm'), {
      fields: {
        name: {
          validators: {
            notEmpty: {
              message: 'Please enter role name'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.col-12'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Submit the form when all fields are valid
        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () {
      $.ajax({
        data: $('#addRoleForm').serialize(),
        url: `${baseUrl}roles/addOrUpdateAjax`,
        type: 'POST',
        success: function (status) {
          console.log('Response: ' + JSON.stringify(status));

          // close the modal
          addRoleModel.modal('hide');

          // reset form
          offCanvasForm.trigger('reset');

          showSuccessToast('Role added/updated successfully');

          setTimeout(() => {
            location.reload();
          }, 1000);
        },
        error: function (err) {
          console.log(err);

          //Get Response
          var response = err.responseJSON;
          Swal.fire({
            title: 'Failed',
            text: response.data,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });
    });
  })();

  window.deleteRole = function (id) {
    // sweetalert for confirmation of delete
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
        // delete the data
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}roles/deleteAjax/${id}`,
          success: function () {
            // success sweetalert
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'The role has been deleted!',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            }).then(function () {
              location.reload();
            });
          },
          error: function (error) {
            console.log(error);
            // error sweetalert
            var response = error.responseJSON;
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: response.data,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });
      }
    });
  };
});
