<!-- Edit Compensation Information Modal -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditCompInfo"
     aria-labelledby="offcanvasEditWorkInfoLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasEditCompInfoLabel" class="offcanvas-title">@lang('Edit Compensation Information')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form action="{{route('employees.updateCompensationInfo')}}" method="POST">
      @csrf
      <input type="hidden" name="id" id="id" value="{{ $user->id }}">

      <!-- Salary -->
      <div class="mb-4">
        <label class="form-label" for="baseSalary">@lang('Base Salary')</label>
        <input type="number" name="baseSalary" id="baseSalary" class="form-control"
               placeholder="Enter Salary" value="{{$user->base_salary}}" />
      </div>
      
      <div class="mb-4">
        <label class="form-label" for="availableLeaveCount">Available Leave Count</label>
        <input type="number" name="availableLeaveCount" id="availableLeaveCount" class="form-control"
               placeholder="Enter available leave count" value="{{$user->available_leave_count}}" />
      </div>


      <button type="submit" class="btn btn-primary me-3">@lang('Save Changes')</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
    </form>
  </div>
</div>

<!-- /Edit Compensation Information Modal -->
