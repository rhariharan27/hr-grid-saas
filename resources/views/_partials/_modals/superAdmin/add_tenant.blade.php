<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCreateTenant"
     aria-labelledby="offcanvasCreateTenantLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasCreateTenantLabel" class="offcanvas-title">@lang('Create Tenant')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="pt-0" id="createTenantForm" action="{{route('tenant.store')}}" method="POST">
      @csrf
      <div class="col-12 mb-6">
        <label class="form-label" for="name">@lang('Name')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" placeholder="@lang('Enter name')"
               name="name" />
      </div>
      <div class="col-12 mb-6">
        <label class="form-label" for="companyName">@lang('Company Name')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="companyName" placeholder="@lang('Enter company name')"
               name="companyName" />
      </div>
      <div class="col-12 mb-6">
        <label class="form-label" for="emailDomain">@lang('Email Domain')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="emailDomain" placeholder="@lang('Enter email domain')"
               name="emailDomain" />
      </div>
      <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Create')</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
    </form>
  </div>
</div>
