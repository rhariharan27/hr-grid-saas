$(function () {
  var dtTable = $('.datatables-payroll');
  var dtPayroll; // DataTable instance
  const generateModalElement = $('#generatePayrollModal');
  const generateModal = generateModalElement.length ? new bootstrap.Modal(generateModalElement[0]) : null;
  const generateForm = $('#generatePayrollForm');
  const generateSubmitBtn = $('#submitGenerateBtn');
  const generatePeriodInput = $('#payrollPeriod');
  const generateGeneralError = $('#generatePayrollForm .general-error-message');
  const generateSpinner = $('#submitGenerateBtn .spinner-border');

  // --- Init Plugins ---
  $('.select2').each(function () {
    $(this).select2({ dropdownParent: $(this).parent() });
  }); // Init filters

  if (dtTable.length) {
    dtPayroll = dtTable.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'payroll/getListAjax',
        data: function (d) {
          d.dateFilter = $('#dateFilter').val();
          d.employeeFilter = $('#employeeFilter').val();
          d.statusFilter = $('#statusFilter').val();
        }
      },
      columns: [
        { data: 'id' },
        { data: 'user' },
        { data: 'period' },
        { data: 'basic_salary' },
        { data: 'gross_salary' },
        { data: 'net_salary' },
        { data: 'status' },
        { data: 'actions', orderable: false, searchable: false }
      ],
      order: [[1, 'desc']]
    });
  }

  // --- Filter Handling ---
  $('.filter-input').on('change', function () {
    dtPayroll?.draw();
  });

  // --- Generate Payroll Modal ---

  // Reset modal on hide
  if (generateModalElement.length) {
    generateModalElement.on('hidden.bs.modal', function () {
      generateForm[0].reset(); // Reset form
      generateGeneralError.text('').hide(); // Hide errors
      generateForm.find('.is-invalid').removeClass('is-invalid');
      generateSubmitBtn.prop('disabled', false).find('.spinner-border').hide(); // Reset button
    });
  }

  // Generate Payroll Form Submission
  if (generateForm.length) {
    generateForm.on('submit', function (e) {
      e.preventDefault();
      generateGeneralError.text('').hide();
      generateForm.find('.is-invalid').removeClass('is-invalid');

      const periodValue = generatePeriodInput.val();
      if (!periodValue) {
        generatePeriodInput
          .addClass('is-invalid')
          .closest('.mb-3')
          .find('.invalid-feedback')
          .text('Please select a period.');
        return;
      }

      generateSubmitBtn.prop('disabled', true);
      generateSpinner.show();

      $.ajax({
        url: payrollGenerateRoute, // Use named route
        method: 'POST',
        data: {
          period: periodValue,
          _token: csrfToken // Pass token if not using global setup
        },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            generateModal?.hide();
            showSuccessToast(response.message || 'Payroll generation started.'); // Use global toast
            dtPayroll?.ajax.reload(); // Refresh table
          } else {
            generateGeneralError.text(response.message || 'Generation failed.').show();
            showErrorToast(response.message || 'Generation failed.'); // Use global toast
          }
        },
        error: function (jqXHR) {
          console.error('Payroll Generation Error:', jqXHR);
          if (jqXHR.status === 422 && jqXHR.responseJSON?.errors) {
            // Display validation errors (likely for 'period')
            Object.keys(jqXHR.responseJSON.errors).forEach(key => {
              const inputEl = generateForm.find(`[name="${key}"]`);
              inputEl.addClass('is-invalid');
              inputEl.closest('.mb-3').find('.invalid-feedback').text(jqXHR.responseJSON.errors[key][0]).show();
            });
          } else if (jqXHR.status === 409) {
            // Conflict - already processed
            generateGeneralError.text(jqXHR.responseJSON?.message || 'Already processed.').show();
            showErrorToast(jqXHR.responseJSON?.message || 'Already processed.');
          } else {
            generateGeneralError.text(jqXHR.responseJSON?.message || 'An error occurred.').show();
            showErrorToast(jqXHR.responseJSON?.message || 'An error occurred.');
          }
        },
        complete: function () {
          generateSubmitBtn.prop('disabled', false);
          generateSpinner.hide();
        }
      });
    });
  }
});
