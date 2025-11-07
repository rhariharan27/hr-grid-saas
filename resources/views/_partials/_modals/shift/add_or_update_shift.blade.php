{{-- File: resources/views/_partials/_modals/shift/add_or_update_shift.blade.php --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddOrUpdateShift" aria-labelledby="offcanvasShiftLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasShiftLabel" class="offcanvas-title">Add Shift</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="add-edit-shift-form pt-0" id="shiftForm" onsubmit="return false;">
      @csrf
      <input type="hidden" name="_method" id="shiftMethod" value="POST"> {{-- JS will change to PUT for update --}}
      <input type="hidden" name="id" id="shift_id" value="">

      {{-- Name --}}
      <div class="mb-3">
        <label class="form-label" for="shiftName">Shift Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="shiftName" placeholder="e.g., General Shift, Night Shift"
               name="name" required />
        <div class="invalid-feedback"></div>
      </div>

      {{-- Code --}}
      <div class="mb-3">
        <label class="form-label" for="shiftCode">Shift Code <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="shiftCode" placeholder="Unique Code (e.g., GS01)" name="code"
               required />
        {{-- Remote validation done by JS if checkCodeValidationAjax route is kept --}}
        <div class="invalid-feedback"></div>
      </div>

      {{-- Start & End Time --}}
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="startTime" class="form-label">Start Time <span class="text-danger">*</span></label>
          <input type="text" class="form-control flatpickr-input" placeholder="HH:MM" id="startTime" name="start_time"
                 required readonly="readonly" />
          <div class="invalid-feedback"></div>
        </div>
        <div class="col-md-6">
          <label for="endTime" class="form-label">End Time <span class="text-danger">*</span></label>
          <input type="text" class="form-control flatpickr-input" placeholder="HH:MM" id="endTime" name="end_time"
                 required readonly="readonly" />
          <div class="invalid-feedback"></div>
        </div>
      </div>

      {{-- Shift Days --}}
      <div class="mb-3">
        <label class="form-label d-block">Working Days</label>
        <div class="d-flex flex-wrap justify-content-start gap-3">
          @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="{{ $day }}Toggle" name="{{ $day }}">
              <label class="form-check-label" for="{{ $day }}Toggle"> {{ ucfirst($day) }} </label>
            </div>
          @endforeach
          {{-- Hidden inputs for days (value="0") are not strictly needed if backend handles null/missing as false --}}
        </div>
      </div>

      {{-- Notes --}}
      <div class="mb-3">
        <label class="form-label" for="shiftNotes">Notes</label>
        <textarea class="form-control" id="shiftNotes" name="notes" rows="3" placeholder="Optional notes..."></textarea>
        <div class="invalid-feedback"></div>
      </div>

      {{-- General Error Message Area --}}
      <div class="mb-3">
        <small class="text-danger general-error-message"></small>
      </div>

      <div class="mt-4 text-end">
        <button type="reset" class="btn btn-label-secondary me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary data-submit" id="submitShiftBtn">Submit</button>
      </div>
    </form>
  </div>
</div>
