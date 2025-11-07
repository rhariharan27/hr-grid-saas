<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasOfflineRequestDetails"
    aria-labelledby="offcanvasOfflineRequestLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasOfflineRequestLabel" class="offcanvas-title">@lang('Offline Request Details')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <div class="row mb-6">
            <div class="col-4 fw-bold">User:</div>
            <div class="col-8">
                <div class="user-info" id="userName"></div>
                <div class="user-info user-email" id="userEmail" style ="font-size: 12px"></div>
            </div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">Type:</div>
            <div class="col-8" id="type" ></div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">Plan Type:</div>
            <div class="col-8" id="planName"></div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold" style>Additional Users:</div>
            <div class="col-8" id="additionalUsers" ></div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">Total Amount:</div>
            <div class="col-8" id="totalAmount"></div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">Created At:</div>
            <div class="col-8" id="createdAt"></div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">Status:</div>
            <div class="col-8" id="statusDiv">

            </div>
        </div>

        <form action="{{ route('offlineRequests.actionAjax') }}" method="POST" id="offlineRequestForm"
            style="display: none;">
            @csrf
            <input type="hidden" name="id" id="id">
            <div class="row mb-6" id="statusDDDiv">
                <label for="status" class="col-4 fw-bold">Action:</label>
                <div class="col-8">
                    <select class="form-select" id="status" name="status">
                    </select>
                </div>
            </div>
            <div class="row mb-6">
                <label for="notes" class="col-4 fw-bold">Admin Notes:</label>
                <div class="col-8">
                    <textarea class="form-control" id="adminNotes" name="adminNotes"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-3 data-submit"
                    id=actionButton>@lang('Submit')</button>
                <button type="reset" class="btn btn-label-danger"
                    data-bs-dismiss="offcanvas">@lang('Cancel')</button>

        </form>
    </div>
</div>
