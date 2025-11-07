<!-- Add Role Modal -->
<div class="modal fade" id="addOrUpdateRoleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-role">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="role-title mb-2">Add New Role</h4>
        </div>
        <!-- Add role form -->
        <form id="addRoleForm" class="row g-6" onsubmit="return false">
          <input type="hidden" name="id" id="id" />
          <div class="col-12">
            <label class="form-label" for="modalRoleName">Role Name</label>
            <input type="text" id="name" name="name" class="form-control"
                   placeholder="Enter a role name" tabindex="-1" autofocus />
          </div>
          <div class="col-6">
            <label class="form-label" for="isMultiCheckInEnabled">Multi Check-In/Out</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="isMultiCheckInEnabled" name="isMultiCheckInEnabled" />
              <label class="form-check-label" for="isMultiCheckInEnabled">Enable</label>
            </div>
          </div>
          <div class="col-6">
            <label class="form-label" for="mobileAppAccess">Mobile App Access</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="mobileAppAccess" name="mobileAppAccess" />
              <label class="form-check-label" for="mobileAppAccess">Enable</label>
            </div>
          </div>
          <div class="col-6">
            <label class="form-label" for="webAppAccess">Web App Access</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="webAppAccess" name="webAppAccess" />
              <label class="form-check-label" for="webAppAccess">Enable</label>
            </div>
          </div>
          <div class="col-6">
            <label class="form-label" for="locationActivityTracking">Location Activity Tracking</label>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" id="locationActivityTracking"
                     name="locationActivityTracking" />
              <label class="form-check-label" for="locationActivityTracking">Enable</label>
            </div>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-3">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel
            </button>
          </div>
        </form>
        <!--/ Add role form -->
      </div>
    </div>
  </div>
</div>
<!--/ Add Role Modal -->
