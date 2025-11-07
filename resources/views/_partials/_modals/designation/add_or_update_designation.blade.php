<!-- Offcanvas to add new department -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOrUpdateDesignation"
    aria-labelledby="offcanvasCreateDesignationLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasDesignationLabel" class="offcanvas-title">@lang('Add Designation')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
        <form class="pt-0" id="designationForm">
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="status" id="status">

            <div class="mb-6">
                <label class="form-label" for="name">@lang('Name') <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" placeholder="@lang('Enter Designation Name')" name="name"
                    required />
            </div>

            <div class="mb-6">
                <label class="form-label" for="name">@lang('Department')</label>
                <select class="form-select " id="department_id" name="department_id">
                    <option value="" disabled selected>Select department</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="form-label" for="code">@lang('Code') <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" placeholder="@lang('Enter Department Code')" name="code"
                    required />
            </div>

            <div class="mb-6">
                <label class="form-label" for="notes">@lang('Description')</label>
                <textarea class="form-control" id="notes" name="notes" placeholder="@lang('Enter Description')" rows="3"></textarea>
            </div>

          <div class="mb-6">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="is_approver" name="is_approver">
              <label class="form-check-label" for="is_approver">@lang('Is Approver')</label>
            </div>
          </div>

            <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Submit')</button>
            <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
        </form>
    </div>
</div>
