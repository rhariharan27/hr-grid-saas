'use strict';

$(function () {
  // jQuery document ready

  // --- Selectors ---
  // Add Manual Adjustment Modal & Form
  const manualAdjustmentModalElement = $('#manualAdjustmentModal');
  const manualAdjustmentModal = manualAdjustmentModalElement.length
    ? new bootstrap.Modal(manualAdjustmentModalElement[0])
    : null;
  const manualAdjustmentForm = $('#manualAdjustmentForm');
  const submitManualAdjustmentBtn = $('#submitManualAdjustmentBtn');
  const manualAdjustmentNameInput = $('#manualAdjustmentName');
  const manualAdjustmentTypeSelect = $('#manualAdjustmentType'); // Select2
  const manualAdjustmentAmountInput = $('#manualAdjustmentAmount'); // CleaveJS
  const manualAdjustmentNotesInput = $('#manualAdjustmentNotes');
  const manualAdjustmentGeneralError = $('#manualAdjustmentForm .general-error-message');

  // Adjustment Table
  const adjustmentsTableBody = $('#adjustmentsLogTable tbody');
  const noAdjustmentsMessage = $('#noAdjustmentsMessage'); // Assuming you add this ID to the <p> tag

  // Salary Summary Display TDs
  const displayBasicSalary = $('#displayBasicSalary');
  const displayTotalBenefits = $('#displayTotalBenefits');
  const displayGrossSalary = $('#displayGrossSalary');
  const displayTotalDeductions = $('#displayTotalDeductions');
  const displayTaxAmount = $('#displayTaxAmount');
  const displayNetSalary = $('#displayNetSalary');

  // Status Action Buttons (Placeholders)
  const markCompletedBtn = $('#markCompletedBtn');
  const markPaidBtn = $('#markPaidBtn');
  const cancelPayrollBtn = $('#cancelPayrollBtn');

  // Cancel Modal & Form
  const cancelPayrollModalElement = $('#cancelPayrollModal');
  const cancelPayrollModal = cancelPayrollModalElement.length
    ? new bootstrap.Modal(cancelPayrollModalElement[0])
    : null;
  const cancelPayrollForm = $('#cancelPayrollForm');
  const cancelReasonInput = $('#cancel_reason');
  const submitCancelBtn = $('#submitCancelBtn');
  const cancelGeneralError = $('#cancelPayrollForm .general-error-message');

  // --- CSRF Setup ---
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } }); // Assumes csrfToken is global via Blade

  // --- Initialize Plugins for Modal ---
  function initializeManualAdjustmentModalPlugins() {
    if (manualAdjustmentModalElement.length) {
      manualAdjustmentTypeSelect.select2({
        placeholder: 'Select Type',
        dropdownParent: manualAdjustmentModalElement, // Attach to modal
        minimumResultsForSearch: Infinity
      });
      /*     if (manualAdjustmentAmountInput.length && typeof Cleave !== 'undefined') {
            new Cleave(manualAdjustmentAmountInput[0], {
              // Use native element
              numeral: true,
              numeralDecimalScale: 2,
              numeralThousandsGroupStyle: 'thousand'
            });
          } */
    }
  }

  initializeManualAdjustmentModalPlugins(); // Init on page load

  // --- Helper Functions ---
  /*   function showSuccessToast(message) {
      /!* Assumed global *!/
      console.log('Success:', message);
      showSuccessMessage(message || 'Operation successful.');
    }

    function showErrorToast(message) {
      /!* Assumed global *!/
      console.error('Error:', message);
      showErrorMessage(message || 'An error occurred.');
    } */

  function resetModalValidation(formEl) {
    if (!formEl || !formEl.length) return;
    const jqForm = $(formEl); // Ensure jQuery object
    jqForm.find('.is-invalid').removeClass('is-invalid');
    jqForm.find('.invalid-feedback').text('').hide();
    jqForm.find('.select2-container').removeClass('is-invalid');
    jqForm.find('.general-error-message').text('').hide();
  }

  function displayModalValidationErrors(formEl, errors) {
    const jqForm = $(formEl); // Ensure jQuery object
    if (!jqForm.length || !errors) return;
    resetModalValidation(jqForm);
    let firstErrorElement = null;
    const generalErrorDiv = jqForm.find('.general-error-message');

    console.log('Validation Errors:', errors);
    for (const fieldName in errors) {
      let inputName = fieldName;
      let errorMessage = errors[fieldName][0];
      const inputElement = jqForm.find(`[name="${inputName}"]`);
      let targetElement = inputElement;
      let feedbackElement = targetElement.closest('.mb-3').find('.invalid-feedback');
      if (!inputElement.length) {
        generalErrorDiv.append(`<div>${escapeHtml(inputName)}: ${escapeHtml(errorMessage)}</div>`).show();
        if (!firstErrorElement) firstErrorElement = generalErrorDiv;
        continue;
      }
      if (inputElement.hasClass('select2-hidden-accessible')) {
        targetElement = inputElement.siblings('.select2-container');
      }
      if (targetElement.length) {
        targetElement.addClass('is-invalid');
        if (feedbackElement.length) {
          feedbackElement.text(errorMessage).show();
        } else {
          targetElement.after(`<div class="invalid-feedback d-block">${errorMessage}</div>`);
        }
        if (!firstErrorElement) firstErrorElement = targetElement;
      }
    }
    if (firstErrorElement) {
      if (firstErrorElement.hasClass('select2-container')) {
        firstErrorElement.prev('select').select2('open');
      } else if (firstErrorElement.is(':visible')) {
        firstErrorElement.focus();
      }
    }
  }

  // Manual Adjustment Modal Helpers
  function resetManualAdjustmentFormValidation() {
    resetModalValidation(manualAdjustmentForm);
  }

  // Reset Cancel Modal Form
  function resetCancelForm() {
    resetModalValidation(cancelPayrollForm);
    cancelPayrollForm[0]?.reset();
    $(submitCancelBtn).text('Confirm Cancellation').prop('disabled', false);
  }

  // Set Cancel Button Loading State
  function setCancelButtonLoading(isLoading) {
    $(submitCancelBtn).prop('disabled', isLoading);
    $(submitCancelBtn).html(
      isLoading ? '<span class="spinner-border spinner-border-sm"></span> Processing...' : 'Confirm Cancellation'
    );
  }

  function resetManualAdjustmentForm() {
    resetManualAdjustmentFormValidation();
    manualAdjustmentForm[0]?.reset();
    manualAdjustmentTypeSelect.val('benefit').trigger('change'); // Default type maybe
    manualAdjustmentGeneralError.text('').hide();
    submitManualAdjustmentBtn.text('Add Adjustment').prop('disabled', false);
  }

  function setManualAdjustmentButtonLoading(isLoading) {
    const buttonText = 'Add Adjustment';
    submitManualAdjustmentBtn.prop('disabled', isLoading);
    submitManualAdjustmentBtn.html(
      isLoading ? '<span class="spinner-border spinner-border-sm"></span> Saving...' : buttonText
    );
  }

  function displayManualAdjustmentValidationErrors(errors) {
    displayModalValidationErrors(manualAdjustmentForm, errors);
  }

  // --- NEW Helper: Update Salary Summary Display ---
  function updateSalarySummary(summaryData) {
    const currency = summaryData?.currencySymbol || '$'; // Get currency symbol or default
    displayBasicSalary.text(currency + (summaryData?.basicSalary?.toFixed(2) ?? '0.00'));
    displayTotalBenefits.text('+ ' + currency + (summaryData?.totalBenefits?.toFixed(2) ?? '0.00'));
    displayGrossSalary.text(currency + (summaryData?.grossSalary?.toFixed(2) ?? '0.00'));
    displayTotalDeductions.text('- ' + currency + (summaryData?.totalDeductions?.toFixed(2) ?? '0.00'));
    displayTaxAmount.text('(- ' + currency + (summaryData?.taxAmount?.toFixed(2) ?? '0.00') + ')');
    displayNetSalary.text(currency + (summaryData?.netSalary?.toFixed(2) ?? '0.00'));
  }

  // --- NEW Helper: Add Adjustment Row to Table ---
  function addAdjustmentRowToTable(logData) {
    console.log('Log Data: ' + JSON.stringify(logData));
    if (!logData || !adjustmentsTableBody.length) return;
    // Hide "no adjustments" message if it was visible
    noAdjustmentsMessage?.hide();

    const typeBadge =
      logData.type === 'benefit'
        ? '<span class="badge bg-label-success">Benefit</span>'
        : '<span class="badge bg-label-danger">Deduction</span>';

    // Assuming currency symbol is globally available or passed somehow
    const currency = typeof settings !== 'undefined' ? settings.currency_symbol : '$';
    const amountFormatted = currency + parseFloat(logData.amount || 0).toFixed(2);

    // Action button only if is_manual is true
    const deleteButton = logData.is_manual
      ? `<button class="btn btn-xs btn-icon text-danger delete-manual-adjustment"
                     data-id="${logData.id}"
                     data-url="${adjustmentsBaseUrl}/${logData.id}"
                     title="Delete Manual Adjustment">
                 <i class="bx bx-trash"></i>
             </button>`
      : '<span class="text-muted" title="System Generated"><i class="bx bx-cog"></i></span>';

    const notesHtml = logData.log_message
      ? `<br><small class="text-muted fst-italic">${escapeHtml(logData.log_message)}</small>`
      : '';
    const manualBadge = logData.is_manual ? '<span class="badge bg-label-info ms-1">Manual</span>' : '';

    const newRow = `
            <tr data-log-id="${logData.id}">
                <td>${escapeHtml(logData.name)}${notesHtml}${manualBadge}</td>
                <td>${typeBadge}</td>
                <td class="text-end">${amountFormatted}</td>
                <td class="text-center">${deleteButton}</td>
            </tr>`;

    adjustmentsTableBody.append(newRow); // Append new row
  }

  // --- Event Listeners ---

  // Reset Add form when modal is hidden
  if (manualAdjustmentModalElement.length) {
    manualAdjustmentModalElement.on('hidden.bs.modal', resetManualAdjustmentForm);
  }

  // Add Manual Adjustment Button Click (Already handled by data-bs-toggle)
  // Optionally reset form when shown:
  // if(manualAdjustmentModalElement.length) {
  //     manualAdjustmentModalElement.on('show.bs.modal', resetManualAdjustmentForm);
  // }

  // Add Manual Adjustment Form Submission
  if (manualAdjustmentForm.length) {
    manualAdjustmentForm.on('submit', function (e) {
      e.preventDefault();
      resetManualAdjustmentFormValidation();

      const formData = new FormData(manualAdjustmentForm[0]);
      // No method override needed, route is POST

      setManualAdjustmentButtonLoading(true);

      $.ajax({
        url: storeManualAdjustmentUrl, // URL from Blade vars
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            manualAdjustmentModal?.hide();
            showSuccessToast(response.message);
            if (response.new_log && response.summary) {
              addAdjustmentRowToTable(response.new_log); // Add row dynamically
              updateSalarySummary(response.summary); // Update summary display
            } else {
              location.reload(); // Fallback reload if data missing
            }
          } else {
            showErrorToast(response.message || 'Failed to add adjustment.');
            manualAdjustmentGeneralError.text(response.message || 'Failed to add adjustment.').show();
          }
        },
        error: function (jqXHR) {
          if (jqXHR.status === 422 && jqXHR.responseJSON?.errors) {
            displayModalValidationErrors(manualAdjustmentForm[0], jqXHR.responseJSON.errors);
            showErrorToast('Validation failed.');
          } else {
            showErrorToast(jqXHR.responseJSON?.message || 'An error occurred.');
            manualAdjustmentGeneralError.text(jqXHR.responseJSON?.message || 'An error occurred.').show();
          }
        },
        complete: function () {
          setManualAdjustmentButtonLoading(false);
        }
      });
    });
  } // end if manualAdjustmentForm

  // --- Delete Manual Adjustment ---
  adjustmentsTableBody.on('click', '.delete-manual-adjustment', function () {
    const button = $(this);
    const logId = button.data('id');
    const deleteUrl = `${adjustmentsBaseUrl}/${logId}`; // Use base url + ID

    Swal.fire({
      title: 'Delete Adjustment?',
      text: 'Remove this manual adjustment?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Delete',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.isConfirmed) {
        button.prop('disabled', true); // Disable delete button
        $.ajax({
          url: deleteUrl,
          method: 'POST',
          data: { _method: 'DELETE' },
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              showSuccessToast(response.message);
              button.closest('tr').remove(); // Remove table row
              if (response.summary) {
                updateSalarySummary(response.summary); // Update summary display
              }
              if (adjustmentsTableBody.find('tr').length === 0) {
                noAdjustmentsMessage?.show(); // Show message if table empty
              }
            } else {
              showErrorToast(response.message || 'Failed to delete.');
              button.prop('disabled', false);
            }
          },
          error: function (jqXHR) {
            showErrorToast(jqXHR.responseJSON?.message || 'Deletion failed.');
            button.prop('disabled', false);
          }
        });
      }
    });
  });

  // --- Status Change Button Handlers ---

  // Generic AJAX handler for status buttons (excluding cancel)
  function handleStatusUpdate(button, url, confirmTitle) {
    Swal.fire({
      title: confirmTitle,
      text: 'Update payroll record status?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, Update',
      customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.isConfirmed) {
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
        $.ajax({
          url: url,
          method: 'POST',
          data: { _method: 'PUT' }, // Use PUT for status updates
          dataType: 'json',
          success: function (response) {
            if (response.success) {
              showSuccessToast(response.message);
              location.reload();
            } else {
              showErrorToast(response.message || 'Failed.');
              button.prop('disabled', false).text(button.data('original-text'));
            }
          }, // Reset text
          error: function (jqXHR) {
            showErrorToast(jqXHR.responseJSON?.message || 'Error.');
            button.prop('disabled', false).text(button.data('original-text'));
          }
        });
      }
    });
  }

  // Mark Completed Button
  markCompletedBtn?.on('click', function () {
    $(this).data('original-text', $(this).text()); // Store original text
    handleStatusUpdate($(this), markCompletedUrl, 'Mark as Completed?');
  });

  // Mark Paid Button
  markPaidBtn?.on('click', function () {
    $(this).data('original-text', $(this).text());
    handleStatusUpdate($(this), markPaidUrl, 'Mark as Paid?');
  });

  // Cancel Payroll Form Submission
  if (cancelPayrollForm.length) {
    cancelPayrollForm.on('submit', function (e) {
      e.preventDefault();
      resetModalValidation(cancelPayrollForm[0]);
      const reason = $('#cancel_reason').val()?.trim(); // Use jQuery selector

      if (!reason) {
        // Simple client validation
        displayModalValidationErrors(cancelPayrollForm[0], { cancel_reason: ['Cancellation reason is required.'] });
        return;
      }

      setCancelButtonLoading(true); // Use specific helper

      $.ajax({
        url: cancelRecordUrl,
        method: 'POST',
        data: { cancel_reason: reason }, // Send reason
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            cancelPayrollModal?.hide();
            showSuccessToast(response.message);
            location.reload();
          } else {
            showErrorToast(response.message || 'Failed to cancel.');
          }
        },
        error: function (jqXHR) {
          if (jqXHR.status === 422) {
            displayModalValidationErrors(cancelPayrollForm[0], jqXHR.responseJSON.errors);
            showErrorToast('Validation failed.');
          } else if (jqXHR.status === 409) {
            showErrorToast(jqXHR.responseJSON?.message || 'Cannot cancel.');
          } // Handle conflict
          else {
            showErrorToast(jqXHR.responseJSON?.message || 'An error occurred.');
          }
        },
        complete: function () {
          setCancelButtonLoading(false);
        } // Use specific helper
      });
    });
  }

  function escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Add similar handlers for Mark Completed and Cancel buttons, pointing to respective backend routes/methods
}); // End Document Ready
