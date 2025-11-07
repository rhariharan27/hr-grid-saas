<!--Change Password Modal-->
<!-- Add Role Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-role">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="role-title mb-2">@lang('Change Password')</h4>
          <p>@lang('Please enter your old and new password').</p>
        </div>
        <!-- Add role form -->
        <form id="changePasswordForm" class="row g-6"
              action="{{route('account.changePassword')}}" method="POST">
          @csrf
          @method('POST')
          <div class="col-12">
            <label for="oldPassword" class="form-label">@lang('Old Password')</label>
            <input type="password" class="form-control" id="oldPassword" name="oldPassword"
                   placeholder="@lang('Enter old password')"/>
          </div>
          <div class="col-12">
            <label for="newPassword" class="form-label">@lang('New Password')</label>
            <input type="password" class="form-control" id="newPassword" name="newPassword"
                   placeholder="@lang('Enter new password')"/>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-3">@lang('Submit')</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                    aria-label="Close">@lang('Cancel')
            </button>
          </div>
        </form>
        <!--/ Add role form -->
      </div>
    </div>
  </div>
</div>
<!--/ Add Role Modal -->

<!-- Change Password Modal -->
