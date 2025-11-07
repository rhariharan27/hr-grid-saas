<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOrUpdateProofType"
     aria-labelledby="offcanvasCreateProofTypeLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasProofTypeLabel" class="offcanvas-title">@lang('Create Proof Type')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="pt-0" id="proofTypeForm">
      <input type="hidden" name="id" id="id">
      <input type="hidden" name="status" id="status">
      <div class="mb-6">
        <label class="form-label" for="name">@lang('Name')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" placeholder="@lang('Enter name')"
               name="name" />
      </div>
      <div class="mb-6">
        <label class="form-label" for="code">@lang('Code')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="code" placeholder="@lang('Enter code')"
               name="code" />
      </div>
      <div class="mb-6">
        <label class="form-label" for="notes">@lang('Notes')</label>
        <textarea class="form-control" id="notes" placeholder="@lang('Enter notes')" name="notes"></textarea>
      </div>
      <div class="mb-6 d-flex justify-content-between">
        <label class="form-label m" for="isRequired">@lang('Is Required')</label>
        <div class="form-check form-switch ml-auto">
          <input class="form-check-input" type="checkbox" id="isRequiredToggle">
          <input type="hidden" name="isRequired" id="isRequired" value="0">
        </div>
      </div>
      <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Create')</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
    </form>
  </div>
</div>
