'use strict';

$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // --- URLs & Elements passed from Blade ---
  // urls object: ajax, store, update, destroy, toggleStatus, getMember
  // defaultImagePreview: Path to default avatar placeholder

  const dtElement = $('.datatables-team-members');
  const offcanvasElement = document.getElementById('offcanvasTeamMemberForm');
  const offcanvas = new bootstrap.Offcanvas(offcanvasElement);
  const memberForm = document.getElementById('teamMemberForm');
  const formMethodInput = document.getElementById('formMethod');
  const memberIdInput = document.getElementById('member_id');
  const imagePreview = $('#imagePreview');
  const imageFileInput = $('#member_image'); // File input for main image
  const socialLinksContainer = $('#socialLinksContainer');
  const saveBtn = $('#saveMemberBtn');

  // --- Helper: Get URL with ID ---
  function getUrl(template, id) {
    if (!template) {
      console.error('URL template is undefined');
      return '#';
    }
    return template.replace('{id}', id);
  }

  // --- Helper: Reset Form Validation ---
  function resetFormValidation(form) {
    const jqForm = $(form);
    jqForm.find('.is-invalid').removeClass('is-invalid');
    jqForm.find('.invalid-feedback').text('');
    // Clear general errors if you have a container for them
    $('#socialLinksError').text(''); // Clear social links general error
  }

  // --- Helper: Reset Offcanvas Form ---
  function resetOffcanvas() {
    resetFormValidation(memberForm);
    memberForm.reset();
    memberIdInput.value = '';
    formMethodInput.value = 'POST';
    memberForm.action = urls.store;
    $('#offcanvasTeamMemberFormLabel').text('Add Team Member');
    imagePreview.html(`<img src="${defaultImagePreview}" alt="Default Preview">`); // Reset preview to default
    $('#remove_image_flag').val('0');
    socialLinksContainer.html(''); // Clear dynamic rows
    $('#member_is_active').prop('checked', true);
    saveBtn.prop('disabled', false).html('Save Member');
  }

  // --- Image Preview Logic ---
  function setupImagePreview(inputId, previewId) {
    const fileInput = $('#' + inputId);
    const previewElement = $('#' + previewId);
    if (!fileInput.length || !previewElement.length) return;

    fileInput.on('change', function () {
      const file = this.files[0];
      const removeFlagInput = $('#remove_' + (this.name || '') + '_flag'); // Assumes flag id like remove_image_flag
      const feedbackDiv = fileInput.siblings('.invalid-feedback').first();
      const removeBtn = previewElement.closest('.image-preview-container').find('.remove-image-btn'); // Assumes button is inside a container

      fileInput.removeClass('is-invalid');
      if (feedbackDiv.length) feedbackDiv.text('');
      if (removeBtn.length) removeBtn.show();

      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewElement.html('<img src="' + e.target.result + '" alt="Preview">'); // Update preview content
          if (removeFlagInput.length) removeFlagInput.val('0');
          // Add remove button if not already present
          if (previewElement.closest('.image-preview-container').find('.remove-image-btn').length === 0) {
            previewElement.after(
              `<button type="button" class="btn-close remove-image-btn" data-preview="${previewId}" data-db-field="${fileInput.attr('name')}" aria-label="Remove"></button>`
            );
          }
        };
        reader.readAsDataURL(file);
      } else if (file) {
        previewElement.html(
          `<img src="${previewElement.data('default') || defaultImagePreview}" alt="Default Preview">`
        ); // Reset preview
        if (removeFlagInput.length) removeFlagInput.val('0');
        fileInput.val('');
        if (!feedbackDiv.length) fileInput.after('<div class="invalid-feedback"></div>');
        fileInput.addClass('is-invalid').siblings('.invalid-feedback').text('Invalid file type.');
        if (removeBtn.length) removeBtn.hide();
      }
    });
  }

  setupImagePreview('member_image', 'imagePreview'); // Initialize for the member image

  // --- Remove Image Logic ---
  $(document).on('click', '.remove-image-btn', function () {
    try {
      const previewId = $(this).data('preview');
      const dbFieldName = $(this).data('db-field');
      if (!previewId || !dbFieldName) return;

      const previewElement = $('#' + previewId);
      const defaultSrc = defaultImagePreview; // Use global default
      const removeFlagInputId = '#remove_' + dbFieldName + '_flag';
      const fileInput = $('input[type="file"][name="' + dbFieldName + '"]');

      if (!previewElement.length || $(removeFlagInputId).length === 0) return;

      $(removeFlagInputId).val('1');
      previewElement.html(`<img src="${defaultSrc}" alt="Default Preview">`); // Reset preview content
      if (fileInput.length > 0) {
        fileInput.val('');
        fileInput.removeClass('is-invalid').siblings('.invalid-feedback').text('');
      }
      $(this).remove(); // Remove the button itself after clicking
    } catch (error) {
      console.error('Error in remove image handler:', error);
    }
  });

  // --- Dynamic Social Links ---
  function addSocialLinkRow(platform = '', url = '') {
    // Platform options - Add more as needed
    const platforms = ['LinkedIn', 'Twitter', 'Facebook', 'Instagram', 'GitHub', 'Website', 'Other'];
    let optionsHtml = platforms
      .map(
        p =>
          `<option value="${p.toLowerCase()}" ${platform.toLowerCase() === p.toLowerCase() ? 'selected' : ''}>${p}</option>`
      )
      .join('');

    const row = `
             <div class="row mb-2 social-link-row">
                 <div class="col-5">
                     <input type="text" name="social_platform[]" class="form-control form-control-sm" placeholder="Platform (e.g., LinkedIn)" value="${platform}" list="socialPlatformsList">
                      <datalist id="socialPlatformsList">
                           ${platforms.map(p => `<option value="${p}">`).join('')}
                       </datalist>
                       <div class="invalid-feedback"></div>
                 </div>
                 <div class="col-6">
                     <input type="url" name="social_url[]" class="form-control form-control-sm" placeholder="Full URL (https://...)" value="${url}">
                     <div class="invalid-feedback"></div>
                 </div>
                 <div class="col-1 d-flex align-items-center justify-content-end">
                     <button type="button" class="btn btn-icon btn-sm btn-outline-danger remove-social-row p-1"><i class="bx bx-x"></i></button>
                 </div>
             </div>`;
    socialLinksContainer.append(row);
  }

  $('#addSocialLinkBtn').on('click', function () {
    addSocialLinkRow();
  });
  socialLinksContainer.on('click', '.remove-social-row', function () {
    $(this).closest('.social-link-row').remove();
  });

  // --- DataTables Init ---
  let dtMemberTable;
  if (dtElement.length && typeof urls.ajax !== 'undefined') {
    dtMemberTable = dtElement.DataTable({
      processing: true,
      serverSide: true,
      ajax: { url: urls.ajax, type: 'POST', headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } },
      columns: [
        { data: 'id', name: 'id' },
        { data: 'image_preview', name: 'image', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'title', name: 'title' },
        { data: 'social', name: 'social', orderable: false, searchable: false },
        { data: 'sort_order', name: 'sort_order' },
        { data: 'is_active', name: 'is_active', className: 'text-center' },
        { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
      ],
      order: [
        [5, 'asc'],
        [0, 'desc']
      ], // Sort by sort_order, then ID
      responsive: true,
      language: {
        search: '',
        searchPlaceholder: 'Search..',
        paginate: {
          next: '<i class="bx bx-chevron-right bx-sm"></i>',
          previous: '<i class="bx bx-chevron-left bx-sm"></i>'
        }
      }
    });
  } else {
    console.error('DataTable element or ajaxUrl not defined.');
  }

  // --- Offcanvas Show/Hide/Reset ---
  $('.add-member').on('click', resetOffcanvas);
  if (offcanvasElement) offcanvasElement.addEventListener('hidden.bs.offcanvas', resetOffcanvas);

  // --- Edit Member Button ---
  dtElement.on('click', '.edit-member', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.getMember, id);
    resetOffcanvas();
    $('#offcanvasTeamMemberFormLabel').text('Edit Team Member');
    formMethodInput.value = 'PUT';
    memberForm.action = getUrl(urls.update, id);

    $.get(url, function (data) {
      memberIdInput.value = data.id;
      $('#member_name').val(data.name);
      $('#member_title').val(data.title);
      $('#member_sort_order').val(data.sort_order);
      $('#member_is_active').prop('checked', data.is_active);

      // Populate image preview and add remove button
      imagePreview.html(''); // Clear first
      const currentImageUrl = data.image_url; // URL from accessor
      if (currentImageUrl) {
        imagePreview.html(
          `<div class="image-preview-container position-relative">` +
            `<img src="${currentImageUrl}" alt="Current Image">` +
            `<button type="button" class="btn-close remove-image-btn" data-preview="imagePreview" data-db-field="image" aria-label="Remove"></button>` +
            `</div>`
        );
      } else {
        imagePreview.html(`<img src="${defaultImagePreview}" alt="Default Preview">`); // Show default if no image
      }

      // Populate social links
      socialLinksContainer.html(''); // Clear existing before adding
      if (data.social_links && typeof data.social_links === 'object') {
        $.each(data.social_links, function (platform, url) {
          addSocialLinkRow(platform, url);
        });
      }

      offcanvas.show();
    }).fail(function () {
      Swal.fire('Error', 'Could not fetch member details.', 'error');
    });
  });

  // --- Form Submission ---
  memberForm.addEventListener('submit', function (e) {
    e.preventDefault();
    resetFormValidation(this);

    const formData = new FormData(this);
    const url = this.action;
    const method = 'POST';
    const memberId = memberIdInput.value;

    // Manually remove default social link placeholders if empty, FormData might send them
    // Iterate backwards to safely remove during iteration if needed
    const socialPlatforms = formData.getAll('social_platform[]');
    const socialUrls = formData.getAll('social_url[]');
    formData.delete('social_platform[]'); // Remove original arrays
    formData.delete('social_url[]');

    for (let i = 0; i < socialPlatforms.length; i++) {
      const platform = socialPlatforms[i] ? socialPlatforms[i].trim() : '';
      const urlValue = socialUrls[i] ? socialUrls[i].trim() : '';
      // Only include if both platform and a valid-looking URL are provided
      if (platform && urlValue && urlValue.startsWith('http')) {
        formData.append('social_platform[]', platform);
        formData.append('social_url[]', urlValue);
      }
    }

    if (memberId) {
      formData.append('_method', 'PUT');
    }

    var originalButtonText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

    $.ajax({
      url: url,
      type: method,
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.code === 200) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: response.message,
            timer: 1500,
            showConfirmButton: false
          });
          dtMemberTable.ajax.reload(null, false);
          offcanvas.hide();
        } else {
          Swal.fire('Error', response.message || 'Save failed.', 'error');
        }
      },
      error: function (jqXHR) {
        console.error('Save Member Error:', jqXHR);
        let message = 'An error occurred.';
        let errors = jqXHR.responseJSON?.errors;

        if (jqXHR.status === 422 && errors) {
          message = jqXHR.responseJSON.message || 'Please correct the errors.';
          $.each(errors, function (key, value) {
            // Handle nested validation keys for social links (e.g., "social_url.0")
            let input;
            if (key.startsWith('social_')) {
              let parts = key.split('.');
              if (parts.length === 2) {
                let fieldName = parts[0] + '[]'; // e.g., social_url[]
                let index = parseInt(parts[1], 10);
                input = socialLinksContainer
                  .find('.social-link-row')
                  .eq(index)
                  .find('[name="' + fieldName + '"]');
              }
            } else {
              input = $('[name="' + key + '"]');
              if (input.length === 0 && key === 'image') input = $('#member_image'); // Target file input for image
            }

            if (input && input.length > 0) {
              input.addClass('is-invalid');
              let errorDiv = input.siblings('.invalid-feedback').first();
              if (!errorDiv.length) errorDiv = $('<div class="invalid-feedback"></div>').insertAfter(input);
              errorDiv.text(value[0]);
            } else {
              console.warn(`Validation error for unknown field: ${key}`);
              // Display general error for unhandled fields
              $('#socialLinksError').append(`<div>${value[0]}</div>`);
            }
          });
          Swal.fire({ icon: 'error', title: 'Validation Error', html: message });
          $('.is-invalid').first().focus();
        } else {
          message = jqXHR.responseJSON?.message || message;
          Swal.fire({ icon: 'error', title: 'Error', html: message });
        }
      },
      complete: function () {
        saveBtn.prop('disabled', false).html(originalButtonText);
      }
    });
  });

  // --- Toggle Status ---
  dtElement.on('click', '.status-toggle', function () {
    var id = $(this).data('id');
    var checkbox = $(this);
    var url = getUrl(urls.toggleStatus, id);
    $.ajax({
      url: url,
      type: 'POST',
      success: function (response) {
        if (response.code !== 200) {
          Swal.fire('Error', response.message || 'Could not update status.', 'error');
          checkbox.prop('checked', !checkbox.prop('checked'));
        }
      },
      error: function (err) {
        console.error('Toggle Status Error:', jqXHR);
        Swal.fire('Error', 'Failed to update status.', 'error');
        checkbox.prop('checked', !checkbox.prop('checked'));
      }
    });
  });

  // --- Delete Member ---
  dtElement.on('click', '.delete-member', function () {
    var id = $(this).data('id');
    var url = getUrl(urls.destroy, id);
    Swal.fire({
      title: 'Are you sure?',
      text: 'Delete this team member?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        Swal.fire({
          title: 'Deleting...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        $.ajax({
          url: url,
          type: 'DELETE',
          success: function (response) {
            Swal.close();
            if (response.code === 200) {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: response.message,
                timer: 1500,
                showConfirmButton: false
              });
              dtMemberTable.ajax.reload(null, false); // Use correct table instance
            } else {
              Swal.fire('Error', response.message || 'Could not delete.', 'error');
            }
          },
          error: function (jqXHR) {
            Swal.close();
            console.error('Delete Error:', jqXHR);
            Swal.fire('Error', jqXHR.responseJSON?.message || 'An error occurred.', 'error');
          }
        });
      }
    });
  });
}); // End document ready
