<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOrUpdateLeaveType"
    aria-labelledby="offcanvasCreateLeaveTypeLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasLeaveTypeLabel" class="offcanvas-title">@lang('Create Leave Type')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
        <form class="pt-0" id="leaveTypeForm">
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
                <label class="form-label" for="notes">@lang('Description')</label>
                <textarea class="form-control" id="notes" placeholder="@lang('Enter description')" name="notes"></textarea>
            </div>
            <div class="mb-6">
                <label class="form-label" for="per_month">@lang('Max Leaves Per Month')</label>
                <input type="number" class="form-control" id="per_month" name="per_month" min="0" placeholder="@lang('e.g. 2')">
            </div>
            <div class="mb-6">
                <label class="form-label" for="per_year">@lang('Max Leaves Per Year')</label>
                <input type="number" class="form-control" id="per_year" name="per_year" min="0" placeholder="@lang('e.g. 24')">
            </div>
            <div class="mb-6 d-flex justify-content-between">
                <label class="form-label m" for="isProofRequired">@lang('Is Proof Required')</label>
                <div class="form-check form-switch ml-auto">
                    <input class="form-check-input" type="checkbox" id="isProofRequiredToggle">
                    <input type="hidden" name="isProofRequired" id="isProofRequired" value="0">
                </div>
            </div>
            <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Create')</button>
            <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
        </form>
    </div>
</div>
