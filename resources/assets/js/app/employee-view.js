'use strict';

$(function () {
  var basicInfoForm = $('#basicInfoForm');
  var workInfoForm = $('#workInfoForm');
  var profilePictureForm = $('#profilePictureForm');

  //Sales Targets
  $('#period').datepicker({
    format: 'yyyy',
    viewMode: 'years',
    minViewMode: 'years',
    autoclose: true,
    clearBtn: true,
    startDate: new Date(new Date().getFullYear(), 0, 1)
  });

  $('#incentiveType').on('change', function () {
    var value = this.value;
    if (value === 'none') {
      $('#amountDiv').hide();
      $('#percentageDiv').hide();
    } else if (value === 'fixed') {
      $('#amountDiv').show();
      $('#percentageDiv').hide();
    } else if (value === 'percentage') {
      $('#amountDiv').hide();
      $('#percentageDiv').show();
    } else {
      $('#amountDiv').hide();
      $('#percentageDiv').hide();
    }
  });

  $(document).on('click', '.edit-target', function () {
    var targetId = $(this).data('id');

    fetch(`${baseUrl}employees/getTargetByIdAjax/${targetId}`)
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        var target = data.data;
        $('#targetId').val(target.id);
        $('#period').val(target.period);
        $('#targetType').val(target.target_type).trigger('change');
        $('#targetAmount').val(target.target_amount);
        $('#incentiveAmount').val(target.incentive_amount);
        $('#incentivePercentage').val(target.incentive_percentage);
        $('#incentiveType').val(target.incentive_type).trigger('change');
      });

    console.log(targetId);
  });

  window.editAdjustment = function (adjustment) {
    console.log(adjustment);
    $('#offcanvasPayrollAdjustmentLabel').text('Edit Payroll Adjustment');
    $('#adjustmentId').val(adjustment.id);
    $('#adjustmentName').val(adjustment.name);
    $('#adjustmentType').val(adjustment.type).trigger('change');
    $('#adjustmentAmount').val(adjustment.amount);
    $('#adjustmentPercentage').val(adjustment.percentage);
    $('#adjustmentNotes').val(adjustment.notes);

    if (adjustment.amount) {
      $('#adjustmentCategory').val('fixed');
      $('#adjustmentAmount').parent().removeClass('d-none');
      $('#adjustmentPercentage').parent().addClass('d-none');
    } else {
      $('#adjustmentCategory').val('percentage');
      $('#adjustmentPercentage').parent().removeClass('d-none');
      $('#adjustmentAmount').parent().addClass('d-none');
    }

    $('#adjustmentSubmitBtn').text('Update Adjustment');
  };

  $('#adjustmentCategory').on('change', function () {
    if ($(this).val() === 'percentage') {
      $('#adjustmentPercentage').parent().removeClass('d-none');
      $('#adjustmentAmount').parent().addClass('d-none');
    } else {
      $('#adjustmentAmount').parent().removeClass('d-none');
      $('#adjustmentPercentage').parent().addClass('d-none');
    }
  });

  $('#addPayrollAdjustment').on('click', function () {
    $('#offcanvasPayrollAdjustmentLabel').text('Add Payroll Adjustment');
    $('#adjustmentId').val('');
    $('#adjustmentName').val('');
    $('#adjustmentAmount').val('');
    $('#adjustmentPercentage').val('');
    $('#adjustmentCategory').val('fixed');
    $('#adjustmentAmount').parent().removeClass('d-none');
    $('#adjustmentPercentage').parent().addClass('d-none');
    $('#adjustmentNotes').val('');

    $('#adjustmentSubmitBtn').text('Add Adjustment');
  });

  //Sales Targets

  var userRole = role;

  $('#ipGroupDiv').hide();
  $('#qrGroupDiv').hide();
  $('#dynamicQrDiv').hide();
  $('#siteDiv').hide();
  $('#geofenceGroupDiv').hide();
  $('#dynamicQrDiv').hide();

  if (attendanceType !== 'open') {
    console.log('Attendance Type: ' + attendanceType);
    switch (attendanceType) {
      case 'geofence':
        $('#geofenceGroupDiv').show();
        getGeofenceGroups();
        break;
      case 'ip_address':
        $('#ipGroupDiv').show();
        getIpGroups();
        break;
      case 'qr_code':
        $('#qrGroupDiv').show();
        getQrGroups();
        break;
      case 'site':
        $('#siteDiv').show();
        getSites();
        break;
      case 'dynamic_qr':
        $('#dynamicQrDiv').show();
        getDynamicQrDevices();
        break;
      default:
        break;
    }
  }

  $('#attendanceType').on('change', function () {
    var value = this.value;
    console.log(value);

    $('#ipGroupDiv').hide();
    $('#qrGroupDiv').hide();
    $('#dynamicQrDiv').hide();
    $('#siteDiv').hide();
    $('#geofenceGroupDiv').hide();
    $('#dynamicQrDiv').hide();

    if (value === 'geofence') {
      $('#geofenceGroupDiv').show();
      getGeofenceGroups();
    } else if (value === 'ipAddress') {
      $('#ipGroupDiv').show();
      getIpGroups();
    } else if (value === 'staticqr') {
      $('#qrGroupDiv').show();
      getQrGroups();
    } else if (value == 'site') {
      $('#siteDiv').show();
      getSites();
    } else if (value == 'dynamicqr') {
      $('#dynamicQrDiv').show();
      getDynamicQrDevices();
    } else {
      $('#geofenceGroupDiv').hide();
      $('#ipGroupDiv').hide();
      $('#qrGroupDiv').hide();
      $('#siteDiv').hide();
      $('#dynamicQrDiv').hide();
    }
  });

  window.loadSelectList = function () {
    var roleSelector = $('#role'),
      teamSelector = $('#teamId'),
      shiftSelector = $('#shiftId'),
      reportingToSelector = $('#reportingToId'),
      designationSelector = $('#designationId');

    //Load Roles
    getRoles().then(function (roles) {
      roleSelector.empty();
      roleSelector.append('<option value="">Select Role</option>');
      roles.forEach(function (role) {
        roleSelector.append(
          `<option value="${role.name}" ${userRole === role.name ? 'selected' : ''}>${role.name}</option>`
        );
      });
    });

    getTeams().then(function (teams) {
      teamSelector.empty();
      teamSelector.append('<option value="">Select Team</option>');
      teams.forEach(function (team) {
        teamSelector.append(
          `<option value="${team.id}" ${team.id === user.team_id ? 'selected' : ''}>${team.code}-${team.name}</option>`
        );
      });
    });

    getShifts().then(function (shifts) {
      shiftSelector.empty();
      shiftSelector.append('<option value="">Select Shift</option>');
      shifts.forEach(function (shift) {
        shiftSelector.append(
          `<option value="${shift.id}" ${shift.id === user.shift_id ? 'selected' : ''}>${shift.code}-${shift.name}</option>`
        );
      });
    });

    getReportingToUsers().then(function (employeeTypes) {
      reportingToSelector.empty();
      reportingToSelector.append('<option value="">Select Reporting To</option>');

      //Remove the current user from the list
      employeeTypes = employeeTypes.filter(function (employee) {
        return employee.id !== user.id;
      });

      employeeTypes.forEach(function (employeeType) {
        reportingToSelector.append(
          `<option value="${employeeType.id}" ${employeeType.id === user.reporting_to_id ? 'selected' : ''}>${employeeType.first_name} ${employeeType.last_name}</option>`
        );
      });
    });

    getDesignations().then(function (designations) {
      designationSelector.empty();
      designationSelector.append('<option value="">Select Designation</option>');
      designations.forEach(function (designation) {
        designationSelector.append(
          `<option value="${designation.id}" ${designation.id === user.designation_id ? 'selected' : ''}>${designation.name}</option>`
        );
      });
    });

    roleSelector.select2({
      placeholder: 'Select Role',
      dropdownParent: workInfoForm
    });

    teamSelector.select2({
      placeholder: 'Select Team',
      dropdownParent: workInfoForm
    });

    shiftSelector.select2({
      placeholder: 'Select Shift',
      dropdownParent: workInfoForm
    });

    reportingToSelector.select2({
      placeholder: 'Select Reporting To',
      dropdownParent: workInfoForm
    });

    designationSelector.select2({
      placeholder: 'Select Designation',
      dropdownParent: workInfoForm
    });

    setupWorkInfoFormValidator();
  };

  window.setupWorkInfoFormValidator = function () {
    console.log('Loading Work Info form validator');
    var workInfoForm = document.getElementById('workInfoForm');
    if (workInfoForm.length > 0) {
      var fv = FormValidation.formValidation(workInfoForm, {
        fields: {
          role: {
            validators: {
              notEmpty: {
                message: 'The Role is required'
              }
            }
          },
          teamId: {
            validators: {
              notEmpty: {
                message: 'The Team is required'
              }
            }
          },
          shiftId: {
            validators: {
              notEmpty: {
                message: 'The Shift is required'
              }
            }
          },
          designationId: {
            validators: {
              notEmpty: {
                message: 'The Designation is required'
              }
            }
          },
          doj: {
            validators: {
              notEmpty: {
                message: 'The Joining Date is required'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          /* bootstrap5: new FormValidation.plugins.Bootstrap5({
             // Use this for enabling/changing valid/invalid class
             eleValidClass: '',
             rowSelector: function (field, ele) {
               return '.mb-6';
             }
           }),*/
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      }).on('core.form.valid', function () {
        console.log('Form Submitted');
        workInfoForm.submit();
      });
    }

    console.log('Form validator loaded!');
  };

  window.loadEditBasicInfo = function () {
    console.log('Loading Basic Info');

    var basicInfoForm = document.getElementById('basicInfoForm');

    $('#gender').select2({
      dropdownParent: basicInfoForm
    });

    var fv = FormValidation.formValidation(basicInfoForm, {
      fields: {
        firstName: {
          validators: {
            notEmpty: {
              message: 'The First name is required'
            },
            stringLength: {
              min: 2,
              max: 30,
              message: 'The name must be more than 3 and less than 30 characters long'
            },
            regexp: {
              regexp: /^[a-zA-Z0-9 ]+$/,
              message: 'The name can only consist of alphabetical, number and space'
            }
          }
        },
        lastName: {
          validators: {
            notEmpty: {
              message: 'The last name is required'
            }
          }
        },
        email: {
          validators: {
            notEmpty: {
              message: 'The Email is required'
            },
            emailAddress: {
              message: 'The value is not a valid email address'
            },
            remote: {
              url: `${baseUrl}employees/checkEmailValidationAjax`,
              message: 'The email is already taken',
              method: 'GET',
              data: function () {
                return {
                  id: basicInfoForm.querySelector('[name="id"]').value
                };
              }
            }
          }
        },
        phone: {
          validators: {
            notEmpty: {
              message: 'The Phone is required'
            },
            stringLength: {
              min: 1,
              max: 15,
              message: 'The Phone must be 15 characters long'
            },
            remote: {
              url: `${baseUrl}employees/checkPhoneValidationAjax`,
              message: 'The phone is already taken',
              method: 'GET',
              data: function () {
                return {
                  id: basicInfoForm.querySelector('[name="id"]').value
                };
              }
            }
          }
        },
        altPhone: {
          validators: {
            stringLength: {
              min: 1,
              max: 15,
              message: 'The Phone must be 10 characters long'
            }
          }
        },
        gender: {
          validators: {
            notEmpty: {
              message: 'Please choose'
            }
          }
        },
        dob: {
          validators: {
            notEmpty: {
              message: 'The Date of Birth is required'
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
      console.log('Form Submitted');
      basicInfoForm.submit();
    });

    console.log('Form validator loaded!');
  };

  window.toggleUploadForm = function (formId) {
    const form = document.getElementById(formId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  };

  //Profile Update
  const profilePictureInput = document.getElementById('file');
  const changeProfilePictureButton = document.getElementById('changeProfilePictureButton');

  changeProfilePictureButton.addEventListener('click', function () {
    profilePictureInput.click();
  });

  profilePictureInput.addEventListener('change', function () {
    console.log('Profile Picture Changed');
    if (profilePictureInput.files.length > 0) {
      $(profilePictureForm).submit();
    }
  });

  var maritalStatusSelector = $('#maritalStatus');

  var maritalStatus = maritalStatusSelector.val();
  if (maritalStatus === 'married') {
    $('#marriedDiv').show();
  } else {
    $('#marriedDiv').hide();
  }

  maritalStatusSelector.on('change', function () {
    var maritalStatus = $(this).val();
    if (maritalStatus === 'married') {
      $('#marriedDiv').show();
    } else {
      $('#marriedDiv').hide();
    }
  });
});
