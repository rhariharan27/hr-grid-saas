<!-- Offcanvas to add new user -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddUserLabel" class="offcanvas-title">@lang('Add User')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="add-new-user pt-0" id="addNewUserForm">
      <input type="hidden" name="userId" id="userId">
      <div class="mb-6">
        <label class="form-label" for="firstName">@lang('First Name')</label>
        <input type="text" class="form-control" id="firstName" placeholder="@lang('Enter your first name')"
               name="firstName" autofocus/>
      </div>
      <div class="mb-6">
        <label class="form-label" for="firstName">@lang('Last Name')</label>
        <input type="text" class="form-control" id="lastName" placeholder="@lang('Enter your last name')"
               name="lastName"/>
      </div>
      <div class="mb-6">
        <label for="gender" class="form-label">@lang('Gender')</label>
        <select class="form-control" id="gender" name="gender">
          <option value="male" {{old('gender') == 'male'? 'selected' :  ''}}>Male</option>
          <option value="female" {{old('gender') == 'female'? 'selected' :  ''}}>Female</option>
          <option value="other" {{old('gender') == 'other'? 'selected' :  ''}}>Other</option>
        </select>
      </div>
      <div class="mb-6">
        <label class="form-label" for="email">@lang('Email')</label>
        <input type="text" id="email" class="form-control" placeholder="@lang('Enter your email')"
               name="email"/>
      </div>
      <div class="mb-6">
        <label for="phone" class="form-label">@lang('Phone Number')</label>
        <input type="number" class="form-control" id="phone" name="phone" placeholder="@lang('Enter your phone number')"
               value="{{old('phone')}}">
      </div>
      {{--
      <div class="mb-6">
        <label class="form-label" for="role">@lang('User Role')</label>
        <select id="role" name="role" class="form-select">
          <option value="" selected>@lang('Select a role')</option>
        </select>
      </div>--}}
      <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Submit')</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
    </form>
  </div>
</div>
