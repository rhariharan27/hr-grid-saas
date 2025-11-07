'use strict';

$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // --- URLs & Elements ---
  // urls object (ajax, store, update, destroy, toggleStatus, getReview) from Blade
  // defaultAvatarPreview, defaultLogoPreview from Blade (passed via data-default on img tags)
  const dtElement = $('.datatables-landing-reviews');
  const offcanvasElement = document.getElementById('offcanvasReviewForm');
  const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
  const reviewForm = document.getElementById('reviewForm');
  const formMethodInput = document.getElementById('formMethod');
  const reviewIdInput = document.getElementById('review_id');
  const avatarPreview = $('#avatarPreview');
  const logoPreview = $('#logoPreview');
  const saveBtn = $('#saveReviewBtn');

  // --- Helpers ---
  function getUrl(template, id) {
    if (!template) { console.error("URL template is undefined"); return '#'; }
    return template.replace('{id}', id);
  }

  function resetFormValidation(form) {
    const jqForm = $(form);
    jqForm.find('.is-invalid').removeClass('is-invalid');
    jqForm.find('.invalid-feedback').text('');
  }

  function resetOffcanvas() {
    resetFormValidation(reviewForm);
    reviewForm.reset();
    reviewIdInput.value = '';
    formMethodInput.value = 'POST';
    reviewForm.action = urls.store;
    $('#offcanvasReviewFormLabel').text('Add Review');
    avatarPreview.html(''); // Clear preview content
    logoPreview.html('');
    // Reset previews to default if default source is set on img tag
    avatarPreview.attr('src', avatarPreview.data('default') || '');
    logoPreview.attr('src', logoPreview.data('default') || '');
    $('#review_is_active').prop('checked', true);
    $('#remove_customer_avatar_flag').val('0');
    $('#remove_company_logo_flag').val('0');
    $('.remove-image-btn').hide(); // Hide remove buttons initially
    saveBtn.prop('disabled', false).html('Save Review');
  }

  // --- Image Preview Logic ---
  function setupImagePreview(inputId, previewId) {
    const fileInput = $('#' + inputId);
    const previewElement = $('#' + previewId);
    if (!fileInput.length || !previewElement.length) return;

    fileInput.on('change', function () {
      const file = this.files[0];
      const removeFlagInput = $('#remove_' + (this.name || '') + '_flag');
      const feedbackDiv = fileInput.siblings('.invalid-feedback').first();
      const removeBtn = previewElement.closest('.image-preview-container').find('.remove-image-btn'); // Find related remove btn

      fileInput.removeClass('is-invalid');
      if (feedbackDiv.length) feedbackDiv.text('');
      if (removeBtn.length) removeBtn.show(); // Make sure remove button is visible

      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewElement.attr('src', e.target.result);
          if (removeFlagInput.length) removeFlagInput.val('0');
        }
        reader.readAsDataURL(file);
      } else if (file) {
        previewElement.attr('src', previewElement.data('default') || '');
        if (removeFlagInput.length) removeFlagInput.val('0');
        fileInput.val('').addClass('is-invalid');
        if (feedbackDiv.length === 0) { $('<div class="invalid-feedback"></div>').insertAfter(fileInput); }
        fileInput.siblings('.invalid-feedback').text('Invalid file type.');
        if (removeBtn.length) removeBtn.hide();
      }
    });
  }
  setupImagePreview('customer_avatar', 'avatarPreview');
  setupImagePreview('company_logo', 'logoPreview');

  // --- Remove Image Logic ---
  // Handles clicks on any button with class 'remove-image-btn' within the document
  $(document).on('click', '.remove-image-btn', function() {
    try {
      const previewId = $(this).data('preview');
      const dbFieldName = $(this).data('db-field'); // DB column name (e.g., 'customer_avatar')

      if (!previewId || !dbFieldName) {
        console.error('Remove button missing data attributes'); return;
      }

      const previewElement = $('#' + previewId);
      const defaultSrc = previewElement.data('default') || '';
      const removeFlagInputId = '#remove_' + dbFieldName + '_flag';
      // Find file input associated with this button/preview
      const fileInput = $('input[type="file"][name="' + dbFieldName + '"]');

      if (!previewElement.length || $(removeFlagInputId).length === 0) {
        console.error('Preview or remove flag input not found for', previewId, dbFieldName); return;
      }

      // 1. Set hidden input flag to 1
      $(removeFlagInputId).val('1');
      // 2. Reset preview to default
      previewElement.attr('src', defaultSrc);
      // 3. Clear the file input field
      if (fileInput.length > 0) {
        fileInput.val('');
        fileInput.removeClass('is-invalid').siblings('.invalid-feedback').text('');
      }
      // 4. Hide the remove button itself
      $(this).hide();

    } catch (error) {
      console.error("Error in remove image handler:", error);
      Swal.fire('Error', 'Could not process image removal request.', 'error');
    }
  });


  // --- DataTables Init ---
  let dtReviewTable;
  if (dtElement.length && typeof urls.ajax !== 'undefined') {
    dtReviewTable = dtElement.DataTable({ // Use dtReviewTable variable
      processing: true, serverSide: true,
      ajax: { url: urls.ajax, type: 'POST', headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} },
      columns: [
        { data: 'id', name: 'id' },
        { data: 'avatar_preview', name: 'customer_avatar', orderable: false, searchable: false },
        { data: 'customer_name', name: 'customer_name' },
        { data: 'customer_title', name: 'customer_title', orderable: false },
        { data: 'rating', name: 'rating', className: 'text-center' },
        { data: 'review_text', name: 'review_text', orderable: false },
        { data: 'sort_order', name: 'sort_order' },
        { data: 'is_active', name: 'is_active', className: 'text-center' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
      ],
      order: [[6, 'asc'],[0, 'desc']],
      responsive: true,
      language: { search: '', searchPlaceholder: 'Search..', paginate: {next: '<i class="bx bx-chevron-right bx-sm"></i>', previous: '<i class="bx bx-chevron-left bx-sm"></i>'} }
    });
  } else { console.error("DataTable element or ajaxUrl not defined."); }

  // --- Offcanvas Show/Hide/Reset ---
  $('.add-review').on('click', resetOffcanvas);
  if(offcanvasElement) offcanvasElement.addEventListener('hidden.bs.offcanvas', resetOffcanvas);

  // --- Edit Review Button ---
  dtElement.on('click', '.edit-review', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.getReview, id); // Use correct URL key
    resetOffcanvas();
    $('#offcanvasReviewFormLabel').text('Edit Review');
    formMethodInput.value = 'PUT';
    reviewForm.action = getUrl(urls.update, id);

    $.get(url, function(data){
      reviewIdInput.value = data.id;
      $('#customer_name').val(data.customer_name);
      $('#customer_title').val(data.customer_title);
      $('#review_text').val(data.review_text);
      $('#rating').val(data.rating);
      $('#review_sort_order').val(data.sort_order);
      $('#review_is_active').prop('checked', data.is_active);

      // Populate previews and show remove buttons if images exist
      const avatarUrl = data.customer_avatar_url;
      const logoUrl = data.company_logo_url;
      const defaultAvatar = avatarPreview.data('default') || '';
      const defaultLogo = logoPreview.data('default') || '';

      avatarPreview.html(''); // Clear first
      if(avatarUrl) {
        avatarPreview.html('<img src="' + avatarUrl + '" alt="Current Avatar"><button type="button" class="btn-close remove-image-btn" data-db-field="customer_avatar" data-preview="avatarPreview" aria-label="Remove"></button>');
      } else {
        avatarPreview.html('<img src="'+ defaultAvatar +'" alt="Avatar Preview">'); // Show default if no image
      }

      logoPreview.html(''); // Clear first
      if(logoUrl) {
        logoPreview.html('<img src="' + logoUrl + '" alt="Current Logo"><button type="button" class="btn-close remove-image-btn" data-db-field="company_logo" data-preview="logoPreview" aria-label="Remove"></button>');
      } else {
        logoPreview.html('<img src="'+ defaultLogo +'" alt="Logo Preview">'); // Show default if no image
      }

      offcanvas.show();
    }).fail(function(){ Swal.fire('Error', 'Could not fetch review details.', 'error'); });
  });

  // --- Form Submission ---
  reviewForm.addEventListener('submit', function(e) {
    e.preventDefault();
    resetFormValidation(this);
    const formData = new FormData(this);
    const url = this.action;
    const method = 'POST';
    if (reviewIdInput.value) formData.append('_method', 'PUT');

    var originalButtonText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.ajax({
      url: url, type: method, data: formData, processData: false, contentType: false,
      success: function(response) {
        if(response.code === 200) {
          Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1500, showConfirmButton: false });
          dtReviewTable.ajax.reload(null, false); // Use correct table instance
          offcanvas.hide();
        } else { Swal.fire('Error', response.message || 'Save failed.', 'error'); }
      },
      error: function(jqXHR) {
        console.error("Save Review Error:", jqXHR);
        let message = 'An error occurred. Please try again.';
        let errors = null;
        if (jqXHR.responseJSON) { message = jqXHR.responseJSON.message || message; errors = jqXHR.responseJSON.errors;}

        if (jqXHR.status === 422 && errors) {
          message = jqXHR.responseJSON.message || 'Please correct the errors below.';
          $.each(errors, function (key, value) {
            let input = $('[name="' + key + '"]');
            input.addClass('is-invalid');
            let errorDiv = input.siblings('.invalid-feedback').first();
            if (!errorDiv.length) errorDiv = $('<div class="invalid-feedback"></div>').insertAfter(input);
            errorDiv.text(value[0]);
          });
          Swal.fire({ icon: 'error', title: 'Validation Error', html: message });
          $('.is-invalid').first().focus();
        } else { Swal.fire({ icon: 'error', title: 'Error', html: message }); }
      },
      complete: function() { saveBtn.prop('disabled', false).html(originalButtonText); }
    });
  });


  // --- Toggle Status ---
  dtElement.on('click', '.status-toggle', function () {
    var id = $(this).data('id');
    var checkbox = $(this);
    var url = getUrl(urls.toggleStatus, id);

    $.ajax({
      url: url, type: 'POST',
      success: function (response) {
        if (response.code !== 200) {
          Swal.fire('Error', response.message || 'Could not update status.', 'error');
          checkbox.prop('checked', !checkbox.prop('checked'));
        }
      },
      error: function (jqXHR) {
        console.error("Toggle Status Error:", jqXHR);
        Swal.fire('Error', 'Failed to update status.', 'error');
        checkbox.prop('checked', !checkbox.prop('checked'));
      }
    });
  });

  // --- Delete Review ---
  dtElement.on('click', '.delete-review', function () { // Changed selector
    var id = $(this).data('id');
    var url = getUrl(urls.destroy, id);

    Swal.fire({
      title: 'Are you sure?', text: "Delete this review?", icon: 'warning',
      showCancelButton: true, confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        Swal.fire({ title: 'Deleting...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
        $.ajax({
          url: url, type: 'DELETE',
          success: function (response) {
            Swal.close();
            if (response.code === 200) {
              Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, timer: 1500, showConfirmButton: false });
              dtReviewTable.ajax.reload(null, false); // Use correct table instance
            } else { Swal.fire('Error', response.message || 'Could not delete.', 'error'); }
          },
          error: function (jqXHR) {
            Swal.close();
            console.error("Delete Error:", jqXHR);
            Swal.fire('Error', jqXHR.responseJSON?.message || 'An error occurred.', 'error');
          }
        });
      }
    });
  });

}); // End document ready
