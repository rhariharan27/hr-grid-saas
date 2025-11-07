<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasShowVisitDetails"
     aria-labelledby="offcanvasVisitLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasVisitLabel" class="offcanvas-title">@lang('Visit Details')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body px-4">
    <div class="row mb-4">
      <div class="col-4 fw-bold">User:</div>
      <div class="col-8">
        <div class="d-flex flex-column">
          <span id="userName" class="fw-bold text-body"></span>
          <small id="userCode" class="text-muted"></small>
        </div>
      </div>
    </div>
    <hr class="my-3">
    <div class="row mb-4">
      <div class="col-4 fw-bold">Client Name:</div>
      <div class="col-8">
        <span id="client" class="fw-medium"></span>
      </div>
    </div>
    <hr class="my-3">
    <div class="row mb-4">
      <div class="col-4 fw-bold">Created At:</div>
      <div class="col-8">
        <span id="createdAt"></span>
      </div>
    </div>
    <hr class="my-3">
    <div class="row mb-4">
      <div class="col-4 fw-bold">Address:</div>
      <div class="col-8">
        <p id="address" class="text-wrap mb-0"></p>
      </div>
    </div>
    <hr class="my-3">
    <div class="row mb-4">
      <div class="col-4 fw-bold">Notes:</div>
      <div class="col-8">
        <p id="remarks" class="text-wrap mb-0"></p>
      </div>
    </div>
    <hr class="my-3">
    <div class="row">
      <div class="col-4 fw-bold">Image:</div>
      <div class="col-8">
        <img id="imageUrl" class="img-fluid rounded border shadow-sm" src="https://placehold.co/100x100" alt="Details"
             width="100" height="100">
      </div>
    </div>
  </div>

</div>



