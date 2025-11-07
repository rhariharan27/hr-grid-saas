'use strict';

$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // --- Variables & URLs from Blade ---
  // urls.ajax, urls.store, urls.update, urls.destroy, urls.toggleStatus, urls.getFeature
  // defaultIconPreview (optional placeholder path)
  // scratchCardTypesIndexRoute (Not needed here, maybe leftover?) -> Remove if not used

  // --- Elements ---
  const dtElement = $('.datatables-landing-features');
  const offcanvasElement = document.getElementById('offcanvasFeatureForm');
  const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
  const featureForm = document.getElementById('featureForm');
  const formMethodInput = document.getElementById('formMethod');
  const featureIdInput = document.getElementById('feature_id');
  const iconPreview = $('#iconPreview');
  const iconFileInput = $('#icon_file');
  const iconClassInput = $('#feature_icon');
  const saveBtn = $('#saveFeatureBtn');

  // --- Helper: Get URL with ID ---
  function getUrl(template, id) {
    if (!template) {
      console.error("URL template is undefined");
      return '#'; // Return a fallback
    }
    return template.replace('{id}', id);
  }

  // --- Helper: Reset Form Validation ---
  function resetFormValidation(form) {
    const jqForm = $(form);
    jqForm.find('.is-invalid').removeClass('is-invalid');
    jqForm.find('.invalid-feedback').text('');
    // Clear general errors if you add a container for them
    // $('#formErrors').html('');
  }

  // --- Helper: Reset Offcanvas Form ---
  function resetOffcanvas() {
    resetFormValidation(featureForm);
    featureForm.reset();
    featureIdInput.value = '';
    formMethodInput.value = 'POST';
    featureForm.action = urls.store; // Default to store action
    $('#offcanvasFeatureFormLabel').text('Add Feature');
    iconPreview.html(''); // Clear preview
    $('#feature_is_active').prop('checked', true); // Default to active
    saveBtn.prop('disabled', false).html('Save Feature'); // Reset button
  }

  // --- Image Preview ---
  iconFileInput.on('change', function () {
    const file = this.files[0];
    iconPreview.empty();
    $(this).removeClass('is-invalid').siblings('.invalid-feedback').text('');
    iconClassInput.removeClass('is-invalid').siblings('.invalid-feedback').text('');

    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function (e) { iconPreview.html('<img src="'+e.target.result+'" alt="Preview" style="max-height: 60px; border-radius: 4px;">'); } // Style preview
      reader.readAsDataURL(file);
      iconClassInput.val(''); // Clear class input if file is chosen
    } else if (file) {
      iconPreview.html('');
      $(this).val('').addClass('is-invalid').siblings('.invalid-feedback').text('Invalid file type.');
    }
  });
  iconClassInput.on('input', function() {
    if($(this).val().trim() !== '') {
      iconFileInput.val(''); iconPreview.html(''); // Clear file/preview if class typed
    }
  });

  // --- DataTables Init ---
  let dtFeatureTable;
  if (dtElement.length && typeof urls.ajax !== 'undefined') {
    dtFeatureTable = dtElement.DataTable({
      processing: true, serverSide: true,
      ajax: { url: urls.ajax, type: 'POST', headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} }, // Add CSRF header for POST
      columns: [
        { data: 'id', name: 'id' },
        { data: 'icon_preview', name: 'icon', orderable: false, searchable: false },
        { data: 'title', name: 'title' },
        { data: 'description', name: 'description', orderable: false },
        { data: 'sort_order', name: 'sort_order' },
        { data: 'is_active', name: 'is_active', className: 'text-center' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
      ],
      order: [[4, 'asc'], [0, 'desc']], // Default sort by sort_order, then ID
      responsive: true,
      // Standard language options
      language: { search: '', searchPlaceholder: 'Search..', paginate: {next: '<i class="bx bx-chevron-right bx-sm"></i>', previous: '<i class="bx bx-chevron-left bx-sm"></i>'} }
    });
  } else { console.error("DataTable element or ajaxUrl not defined."); }

  // --- Offcanvas Show/Hide/Reset ---
  $('.add-feature').on('click', resetOffcanvas);
  if (offcanvasElement) {
    offcanvasElement.addEventListener('hidden.bs.offcanvas', resetOffcanvas);
  }

  // --- Edit Feature Button ---
  dtElement.on('click', '.edit-feature', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.getFeature, id);
    resetOffcanvas(); // Use the combined reset function
    $('#offcanvasFeatureFormLabel').text('Edit Feature');
    formMethodInput.value = 'PUT'; // Set method for update (will be added via FormData)
    featureForm.action = getUrl(urls.update, id);

    $.get(url, function(data){
      featureIdInput.value = data.id;
      $('#feature_title').val(data.title);
      $('#feature_description').val(data.description);
      $('#feature_icon').val(data.icon?.startsWith('bx ') ? data.icon : '');
      $('#feature_sort_order').val(data.sort_order);
      $('#feature_is_active').prop('checked', data.is_active);

      iconPreview.html('');
      if(data.icon_url) { // Image path was stored
        iconPreview.html('<img src="' + data.icon_url + '" alt="Current Icon" style="max-height: 60px; border-radius: 4px;">');
      } else if (data.icon && data.icon.startsWith('bx ')) { // Icon class was stored
        iconPreview.html('<i class="' + data.icon + ' fs-1 text-primary"></i>');
      }
      offcanvas.show();
    }).fail(function(){ Swal.fire('Error', 'Could not fetch feature details.', 'error'); });
  });

  // --- Form Submission (Create/Update) ---
  featureForm.addEventListener('submit', function(e) {
    e.preventDefault();
    resetFormValidation(this);

    const formData = new FormData(this);
    const url = this.action;
    const method = 'POST'; // Always POST with FormData
    const featureId = featureIdInput.value;

    if (featureId) { formData.append('_method', 'PUT'); } // Append method for update

    var originalButtonText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    // --- THE AJAX CALL ---
    $.ajax({
      url: url,
      type: method,
      data: formData,
      processData: false, // Needed for FormData
      contentType: false, // Needed for FormData
      success: function(response) {
        if(response.code === 200) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.message,
            timer: 1500,
            showConfirmButton: false
          });
          dtFeatureTable.ajax.reload(null, false); // Refresh DataTable
          offcanvas.hide(); // Close offcanvas
        } else {
          // Handle potential success:false response from server
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message || 'Operation failed. Please check server logs.',
            customClass: { confirmButton: 'btn btn-danger' }
          });
        }
      },
      error: function(jqXHR) {
        console.error("Save Error:", jqXHR);
        let message = 'An error occurred. Please try again.'; // Default error
        let errors = null;

        if (jqXHR.responseJSON) {
          message = jqXHR.responseJSON.message || message; // Use server message if available
          errors = jqXHR.responseJSON.errors; // Get validation errors if present
        }

        if (jqXHR.status === 422 && errors) { // Validation errors
          message = jqXHR.responseJSON.message || 'Please correct the errors below.'; // Main validation message
          // Display field-specific errors
          $.each(errors, function (key, value) {
            let input = $('[name="' + key + '"]');
            if(input.length === 0 && key === 'icon_file') input = $('#icon_file'); // Special case for file input

            if (input.length > 0) {
              input.addClass('is-invalid');
              let errorDiv = input.siblings('.invalid-feedback').first();
              if (!errorDiv.length) { // Create feedback div if it doesn't exist
                errorDiv = $('<div class="invalid-feedback"></div>').insertAfter(input);
              }
              errorDiv.text(value[0]); // Display the first error message for the field
            } else {
              // Log error if field not found, maybe show in general message
              console.warn(`Validation error for unknown field: ${key}`);
            }
          });
          // Show main validation message in Swal only if not displaying inline errors OR as additional info
          Swal.fire({ icon: 'error', title: 'Validation Error', html: message, customClass: { confirmButton: 'btn btn-danger' } });
          $('.is-invalid').first().focus(); // Focus first invalid field

        } else {
          // Show general errors (500, 403, etc.) in Swal
          Swal.fire({ icon: 'error', title: 'Error', html: message, customClass: { confirmButton: 'btn btn-danger' } });
        }
      },
      complete: function() {
        saveBtn.prop('disabled', false).html(originalButtonText); // Re-enable button
      }
    });
    // --- END AJAX CALL ---
  });

  // --- Toggle Status ---
  dtElement.on('click', '.status-toggle', function () {
    var id = $(this).data('id');
    var checkbox = $(this);
    var url = getUrl(urls.toggleStatus, id); // Use template passed from Blade

    $.ajax({
      url: url,
      type: 'POST', // Matches route definition
      // data: { _token: csrfToken }, // Token sent via ajaxSetup header
      success: function (response) {
        if (response.code === 200) {
          // Optional: Show brief success message
          // toastr.success('Status updated!'); // If using toastr
        } else {
          Swal.fire('Error', response.message || 'Could not update status.', 'error');
          checkbox.prop('checked', !checkbox.prop('checked')); // Revert toggle on error
        }
        // dtFeatureTable.ajax.reload(null, false); // Optionally reload row/table
      },
      error: function (jqXHR) {
        console.error("Toggle Status Error:", jqXHR);
        Swal.fire('Error', 'Failed to update status.', 'error');
        checkbox.prop('checked', !checkbox.prop('checked')); // Revert toggle on error
      }
    });
  });

  // --- Delete Feature ---
  dtElement.on('click', '.delete-feature', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.destroy, id); // Use template passed from Blade

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        // Show processing indicator
        Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });

        $.ajax({
          url: url,
          type: 'DELETE', // Matches route definition
          // data: { _token: csrfToken }, // Token sent via ajaxSetup header
          success: function (response) {
            Swal.close(); // Close processing indicator
            if (response.code === 200) {
              Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 1500, showConfirmButton: false });
              dtFeatureTable.ajax.reload(null, false); // Reload table
            } else {
              Swal.fire('Error', response.message || 'Could not delete feature.', 'error');
            }
          },
          error: function (jqXHR) {
            Swal.close();
            console.error("Delete Error:", jqXHR);
            Swal.fire('Error', jqXHR.responseJSON?.message || 'An error occurred during deletion.', 'error');
          }
        });
      }
    });
  });


}); // End document ready
