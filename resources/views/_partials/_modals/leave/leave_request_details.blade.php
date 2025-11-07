<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasLeaveRequestDetails"
    aria-labelledby="offcanvasLeaveRequestLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasLeaveRequestLabel" class="offcanvas-title">@lang('Leave Request Details')</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <div class="row mb-6">
            <div class="col-4 fw-bold">User:</div>
            <div class="col-8">
                <div class="user-info" id="userName"></div>
                <div class="user-info user-code" id="userCode" style ="font-size: 12px"></div>
            </div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">Leave Type:</div>
            <div class="col-8" id="leaveType"></div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">From Date:</div>
            <div class="col-8" id="fromDate"></div>
        </div>
        <div class="row mb-6">
            <div class="col-4 fw-bold">To Date:</div>
            <div class="col-8" id="toDate"></div>
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
        <div class="row mb-6">
            <div class="col-4 fw-bold">Notes:</div>
            <div class="col-8" id="userNotes"></div>
        </div>
        <div class="row mb-6" id = "documentHide" style = "display: none">
            <div class="col-4 fw-bold">Image:</div>
            <div class="col-8">
                <img id="document" class ="img-fluid" src="" width="50" height="50">
            </div>
        </div>


        <form action="{{ route('leaveRequests.actionAjax') }}" method="POST" id="leaveRequestForm"
            style="display: none;">
            @csrf
            <input type="hidden" name="id" id="id">
            <div class="row mb-6" id="statusDDDiv">
                <label for="status" class="col-4 fw-bold">Status:</label>
                <div class="col-8">
                    <select class="form-select" id="status" name="status">
                    </select>
                </div>
            </div>
            <div class="row mb-6">
                <label for="notes" class="col-4 fw-bold">Admin Notes:</label>
                <div class="col-8">
                    <textarea class="form-control" id="admiNotes" name="admiNotes"></textarea>
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
