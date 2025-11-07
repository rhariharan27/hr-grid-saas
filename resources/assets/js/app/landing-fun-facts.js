// resources/assets/js/app/landingpage/landing-fun-facts.js
"use strict"

$(function() {
  $.ajaxSetup({ headers: { "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content") } })

  // --- URLs & Elements from Blade ---
  // urls object: ajax, store, update, destroy, toggleStatus, getFunFact, updateOrder
  // defaultIconPreview (path to a default placeholder image if needed for file uploads)
  const dtElement = $(".datatables-landing-funfacts")
  const offcanvasElement = document.getElementById("offcanvasFunFactForm")
  const offcanvas = new bootstrap.Offcanvas(offcanvasElement)
  const funFactForm = document.getElementById("funFactForm")
  const formMethodInput = document.getElementById("formMethod")
  const funFactIdInput = document.getElementById("funfact_id")
  const iconPreview = $("#iconPreview")
  const iconFileInput = $("#icon_file")
  const iconClassInput = $("#funfact_icon")
  const saveBtn = $("#saveFunFactBtn")
  const sortableList = document.getElementById("funfactSortableList")

  // --- Helpers ---
  function getUrl(template, id) {
    if (!template) {
      console.error("URL template is undefined")
      return "#"
    }
    return template.replace("{id}", id)
  }

  function resetFormValidation(form) {
    const jqForm = $(form)
    jqForm.find(".is-invalid").removeClass("is-invalid")
    jqForm.find(".invalid-feedback").text("")
    // Clear potential specific errors if containers exist
    // $('#iconError, #generalFormError').text('');
  }

  function resetOffcanvas() {
    resetFormValidation(funFactForm)
    funFactForm.reset()
    funFactIdInput.value = ""
    formMethodInput.value = "POST"
    funFactForm.action = urls.store
    $("#offcanvasFunFactFormLabel").text("Add Fun Fact")
    iconPreview.html("") // Clear preview
    $("#funfact_is_active").prop("checked", true)
    $("#remove_icon_flag").val("0") // Reset remove flag
    saveBtn.prop("disabled", false).html("Save Fun Fact")
  }

  // --- Icon/Image Preview ---
  function displayIconPreview(iconValue) {
    iconPreview.empty() // Clear previous preview
    if (!iconValue) return

    if (iconValue.startsWith("bx ")) { // It's a BoxIcon class
      iconPreview.html("<i class=\"" + iconValue + " fs-1 text-primary\"></i>")
    } else if (iconValue) { // Assume it's a URL or path needs constructing
      // Check if it's a full URL or needs base path (depends on getFunFactAjax response)
      let src = iconValue.startsWith("http") ? iconValue : (baseUrl ? baseUrl + "/storage/" + iconValue : iconValue) // Construct URL if base needed and available
      if (src) {
        iconPreview.html(`<div class="image-preview-container position-relative"><img src="${src}" alt="Current Icon"><button type="button" class="btn-close remove-image-btn" data-preview="iconPreview" data-db-field="icon" aria-label="Remove"></button></div>`)
      }
    }
  }

  iconFileInput.on("change", function() {
    const file = this.files[0]
    const feedbackDiv = $(this).siblings(".invalid-feedback").first()
    resetFormValidation(funFactForm) // Clear other errors potentially

    if (file && file.type.startsWith("image/")) {
      const reader = new FileReader()
      reader.onload = function(e) {
        iconPreview.html(`<div class="image-preview-container position-relative"><img src="${e.target.result}" alt="Preview"><button type="button" class="btn-close remove-image-btn" data-preview="iconPreview" data-db-field="icon" aria-label="Remove"></button></div>`) // Add remove btn dynamically
      }
      reader.readAsDataURL(file)
      iconClassInput.val("") // Clear class input
      $("#remove_icon_flag").val("0") // Ensure remove flag is off
    } else if (file) {
      iconPreview.html("")
      $(this).val("").addClass("is-invalid")
      if (!feedbackDiv.length) {
        $("<div class=\"invalid-feedback\"></div>").insertAfter(this)
      }
      $(this).siblings(".invalid-feedback").text("Invalid file type.")
    }
  })

  iconClassInput.on("input", function() {
    if ($(this).val().trim().startsWith("bx ")) {
      iconFileInput.val("") // Clear file input
      iconFileInput.removeClass("is-invalid").siblings(".invalid-feedback").text("")
      displayIconPreview($(this).val().trim()) // Show class preview
      $("#remove_icon_flag").val("0") // Clear remove flag
    } else if ($(this).val().trim() === "") {
      iconPreview.html("") // Clear preview if class is removed
    }
  })

  // --- Remove Icon/Image Logic ---
  $(document).on("click", ".remove-image-btn", function() {
    try {
      const dbFieldName = $(this).data("db-field") // Should be 'icon'
      if (!dbFieldName) return

      const removeFlagInputId = "#remove_" + dbFieldName + "_flag"
      const fileInput = $("input[type=\"file\"][name=\"" + dbFieldName + "_file\"]") // Target specific file input
      const classInput = $("#funfact_icon") // Target class input

      $(removeFlagInputId).val("1") // Signal removal
      iconPreview.html("") // Clear preview
      if (fileInput.length) fileInput.val("") // Clear file input
      if (classInput.length) classInput.val("") // Clear class input
      $(this).remove() // Remove the button

    } catch (error) {
      console.error("Error removing icon:", error)
    }
  })

  // --- DataTables Init ---
  let dtFunFactTable
  if (dtElement.length && typeof urls.ajax !== "undefined") {
    dtFunFactTable = dtElement.DataTable({
      processing: true, serverSide: true,
      ajax: {
        url: urls.ajax,
        type: "POST",
        headers: { "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content") }
      },
      columns: [
        { data: "handle", name: "handle", orderable: false, searchable: false, className: "text-center p-1" },
        { data: "id", name: "id" },
        { data: "icon_preview", name: "icon", orderable: false, searchable: false },
        { data: "title", name: "title" },
        { data: "description", name: "description", orderable: false },
        // { data: 'sort_order', name: 'sort_order' }, // Hidden if using drag-drop
        { data: "is_active", name: "is_active", className: "text-center" },
        { data: "actions", name: "actions", orderable: false, searchable: false, className: "text-center" }
      ],
      order: [], // Disable initial ordering for SortableJS
      // Set row ID for SortableJS
      rowId: function(data) {
        return "funfact-" + data.id
      },
      columnDefs: [{ orderable: false, targets: [0, 1, 2, 3, 5, 6] }], // Disable ordering on non-sortable columns
      drawCallback: function() {
        // initializeSortable()
      },
      responsive: true,
      language: {
        search: "",
        searchPlaceholder: "Search..",
        paginate: {
          next: "<i class=\"bx bx-chevron-right bx-sm\"></i>",
          previous: "<i class=\"bx bx-chevron-left bx-sm\"></i>"
        }
      }
    })
  } else {
    console.error("DataTable element or ajaxUrl not defined.")
  }

  // --- SortableJS Initialization ---
  let sortableInstance = null

  function initializeSortable() {
    if (sortableInstance) sortableInstance.destroy()
    if (sortableList && typeof urls.updateOrder !== "undefined") {
      sortableInstance = Sortable.create(sortableList, {
        animation: 150, handle: ".sort-handle", ghostClass: "sortable-ghost",
        onEnd: function(evt) {
          var itemOrder = []
          $(sortableList).children("tr").each(function() {
            const id = $(this).attr("id")?.replace("funfact-", "")
            if (id) itemOrder.push(id)
          })
          $.ajax({
            url: urls.updateOrder, type: "POST", data: { order: itemOrder },
            success: function(response) {
              if (response.code === 200) toastr.success(response.message || "Order updated!")
              else toastr.error(response.message || "Failed to update order.")
              dtFunFactTable.ajax.reload(null, false) // Reload to confirm order
            },
            error: function() {
              toastr.error("Error updating order.")
            }
          })
        }
      })
    }
  }

  // Initial call handled by drawCallback

  // --- Offcanvas Show/Hide/Reset ---
  $(".add-funfact").on("click", resetOffcanvas)
  if (offcanvasElement) offcanvasElement.addEventListener("hidden.bs.offcanvas", resetOffcanvas)

  // --- Edit Button ---
  dtElement.on("click", ".edit-funfact", function() {
    var id = $(this).data("id")
    var url = getUrl(urls.getFunFact, id) // Use correct key
    resetOffcanvas()
    $("#offcanvasFunFactFormLabel").text("Edit Fun Fact")
    formMethodInput.value = "PUT"
    funFactForm.action = getUrl(urls.update, id)

    $.get(url, function(data) {
      funFactIdInput.value = data.id
      $("#funfact_title").val(data.title)
      $("#funfact_description").val(data.description)
      $("#funfact_icon").val(data.icon?.startsWith("bx ") ? data.icon : "") // Set class if applicable
      $("#border_color_class").val(data.border_color_class)
      $("#funfact_sort_order").val(data.sort_order)
      $("#funfact_is_active").prop("checked", data.is_active)

      // Display icon preview (class or image)
      displayIconPreview(data.icon_url || data.icon) // Prioritize URL, fallback to raw icon value

      offcanvas.show()
    }).fail(function() {
      Swal.fire("Error", "Could not fetch details.", "error")
    })
  })

  // --- Form Submission ---
  funFactForm.addEventListener("submit", function(e) {
    e.preventDefault()
    resetFormValidation(this)

    const formData = new FormData(this)
    const url = this.action
    const method = "POST"
    const funFactId = funFactIdInput.value

    // If file input is empty AND class input is empty, but it was required maybe add validation?
    // Backend handles this primarily

    if (funFactId) {
      formData.append("_method", "PUT")
    }

    var originalButtonText = saveBtn.html()
    saveBtn.prop("disabled", true).html("<span class=\"spinner-border spinner-border-sm\"></span> Saving...")

    $.ajax({
      url: url, type: method, data: formData, processData: false, contentType: false,
      success: function(response) {
        if (response.code === 200) {
          Swal.fire({
            icon: "success",
            title: "Success",
            text: response.message,
            timer: 1500,
            showConfirmButton: false
          })
          dtFunFactTable.ajax.reload(null, false)
          offcanvas.hide()
        } else {
          Swal.fire("Error", response.message || "Save failed.", "error")
        }
      },
      error: function(jqXHR) { /* ... standard validation/error handling ... */
      },
      complete: function() {
        saveBtn.prop("disabled", false).html(originalButtonText)
      }
    })
  })

  // --- Toggle Status ---
  dtElement.on("click", ".status-toggle", function() {
    var id = $(this).data("id")
    var checkbox = $(this)
    var url = getUrl(urls.toggleStatus, id)
    $.ajax({
      url: url, type: "POST",
      success: function(response) {
        if (response.code !== 200) {
          Swal.fire("Error", response.message || "Could not update status.", "error")
          checkbox.prop("checked", !checkbox.prop("checked"))
        }
      },
      error: function(err) {
        Swal.fire("Error", "Failed to update status.", "error")
        checkbox.prop("checked", !checkbox.prop("checked"))
      }
    })
  })

  // --- Delete Fun Fact ---
  dtElement.on("click", ".delete-funfact", function() {
    var id = $(this).data("id")
    var url = getUrl(urls.destroy, id)
    Swal.fire({
      title: "Are you sure?",
      text: "This action cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Delete",
      customClass: { confirmButton: "btn btn-danger me-3", cancelButton: "btn btn-label-secondary" },
      buttonsStyling: false
    }).then(function(result) {
      if (result.value) {
        Swal.fire({
          title: "Deleting...",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading()
          }
        })
        $.ajax({
          url: url, type: "DELETE",
          success: function(response) {
            Swal.close()
            if (response.code === 200) {
              Swal.fire({
                icon: "success",
                title: "Deleted!",
                text: response.message,
                timer: 1500,
                showConfirmButton: false
              })
            } else {
              Swal.fire("Error", response.message || "Could not delete.", "error")
            }
            dtFunFactTable.ajax.reload(null, false)
          },
          error: function(jqXHR) {
            Swal.close()
            Swal.fire("Error", jqXHR.responseJSON?.message || "An error occurred.", "error")
          }
        })
      }
    })
  })

}) // End document ready
