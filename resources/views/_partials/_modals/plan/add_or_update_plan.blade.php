<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOrUpdatePlan"
    aria-labelledby="offcanvasCreatePlanLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasPlanLabel" class="offcanvas-title">@lang('Create Plan')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
        <form class="pt-0" id="planForm">
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="status" id="status">
            <div class="mb-6">
                <label class="form-label" for="name">@lang('Name')<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" placeholder="@lang('Enter name')"
                    name="name" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="duration">@lang('Duration')<span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="duration" placeholder="@lang('Enter duration')"
                    name="duration" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="includedUsers">@lang('Included Users')</label>
                <input type="number" class="form-control" id="includedUsers" placeholder="@lang('Enter included users')"
                    name="includedUsers" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="basePrice">@lang('Base Price')</label>
                <input type="number" class="form-control" id="basePrice" placeholder="@lang('Enter base price')"
                    name="basePrice" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="perUserPrice">@lang('Per User Price')</label>
                <input type="number" class="form-control" id="perUserPrice" placeholder="@lang('Enter per user price')"
                    name="perUserPrice" />
            </div>
            <div class="mb-6">
                <label class="form-label" for="durationType">@lang('Duration Type')<span class="text-danger">*</span></label>
                <select class="form-select" id="durationType" name="durationType">
                    <option value="">Select Duration Type</option>
                    <option value="days">Days</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="form-label" for="description">@lang('Description')</label>
                <textarea class="form-control" id="description" placeholder="@lang('Enter description')" name="description"></textarea>
            </div>

            <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Create')</button>
            <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>

        </form>
    </div>
</div>
