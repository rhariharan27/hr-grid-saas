// Add this within the existing <script> tag in @section('page-script')
$(function() {

  // --- Existing TinyMCE Init ---
  if (typeof tinymce !== 'undefined') {
    tinymce.init({ selector: 'textarea.tinymce-editor' /* ... your config ... */ });
  } else {
    console.error('TinyMCE script not loaded.');
  }

  // --- Reusable Image Preview Function ---
  function setupImagePreview(inputId, previewId) {
    const fileInput = document.getElementById(inputId);
    const previewElement = document.getElementById(previewId);
    if (!fileInput || !previewElement) return;

    fileInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      const feedback = $(fileInput).siblings('.invalid-feedback')[0];
      fileInput.classList.remove('is-invalid');
      if (feedback) feedback.textContent = '';

      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewElement.src = e.target.result;
        };
        reader.readAsDataURL(file);
      } else if (file) {
        previewElement.src = ''; // Clear preview on invalid file
        fileInput.value = '';
        fileInput.classList.add('is-invalid');
        if (feedback) feedback.textContent = 'Invalid file type.';
      }
    });
  }

  // --- Initialize Previews for Hero Images ---
  setupImagePreview('lightHeroImage', 'lightHeroPreview');
  setupImagePreview('darkHeroImage', 'darkHeroPreview');

  // --- Tab Handling Logic (Essential) ---
  const urlParams = new URLSearchParams(window.location.search);
  const activeTabParam = urlParams.get('tab'); // Get tab from URL
  const settingsMenu = document.getElementById('settingsMenu');

  // Function to activate tab based on ID
  function setActiveTab(tabId) {
    if (!tabId) return; // Exit if no ID
    const triggerEl = settingsMenu?.querySelector(`[data-bs-target="#${tabId}"]`);
    if (triggerEl) {
      const tab = bootstrap.Tab.getOrCreateInstance(triggerEl);
      tab.show();
    }
  }

  // Activate tab based on URL param (or default if no param)
  const defaultTab = 'contentSettings'; // Set your default tab ID here
  setActiveTab(activeTabParam || defaultTab);

  // Optional: Update URL hash when tabs are shown (for bookmarking)
  if (settingsMenu) {
    settingsMenu.addEventListener('shown.bs.tab', event => {
      let hash = event.target.getAttribute('data-bs-target');
      if (hash && hash.startsWith('#')) {
        // history.replaceState(null, null, `?tab=${hash.substring(1)}`); // Update URL without reload
      }
    });
  }

}); // End jQuery ready
