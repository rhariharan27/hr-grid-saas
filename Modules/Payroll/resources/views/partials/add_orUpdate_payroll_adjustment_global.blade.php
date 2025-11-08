<!-- Payroll Adjustment Modal -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasPayrollAdjustment"
     aria-labelledby="offcanvasPayrollAdjustmentLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasPayrollAdjustmentLabel" class="offcanvas-title">@lang('Add Payroll Adjustment')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form action="{{ route('settings.addOrUpdatePayrollAdjustment') }}" method="POST">
      @csrf
      <input type="hidden" name="id" id="adjustmentId" value="">
      <!-- Adjustment Name -->
      <div class="mb-4">
        <label class="form-label" for="adjustmentName">@lang('Name')</label>
        <input type="text" name="adjustmentName" id="adjustmentName" class="form-control"
               placeholder="e.g., Overtime Pay, Late Deduction"/>
      </div>

      <!-- Adjustment Code -->
      <div class="mb-4">
        <label class="form-label" for="adjustmentCode">@lang('Code')</label>
        <input type="text" name="adjustmentCode" id="adjustmentCode" class="form-control"
               placeholder="e.g., OT, LD"/>
      </div>

      <!-- Adjustment Type -->
      <div class="mb-4">
        <label class="form-label" for="adjustmentType">@lang('Adjustment Type')</label>
        <select name="adjustmentType" id="adjustmentType" class="form-select">
          <option value="benefit">@lang('Benefit')</option>
          <option value="deduction">@lang('Deduction')</option>
        </select>
      </div>

      <!-- Adjustment Category -->
      <div class="mb-4">
        <label class="form-label" for="adjustmentCategory">@lang('Adjustment Category')</label>
        <select name="adjustmentCategory" id="adjustmentCategory" class="form-select">
          <option value="fixed">@lang('Fixed')</option>
          <option value="percentage">@lang('Percentage')</option>
        </select>
      </div>

      <!-- Adjustment Amount -->
      <div class="mb-4">
        <label class="form-label" for="adjustmentAmount">@lang('Adjustment Amount')</label>
        <input type="number" step="0.01" name="adjustmentAmount" id="adjustmentAmount" class="form-control"
               placeholder="Enter adjustment amount"/>
      </div>

      <!-- Adjustment Percentage -->
      <div class="mb-4 d-none">
        <label class="form-label" for="adjustmentPercentage">@lang('Adjustment Percentage (%)')</label>
        <input type="number" step="0.01" name="adjustmentPercentage" id="adjustmentPercentage"
               class="form-control" placeholder="Enter adjustment percentage"/>
      </div>

      <!-- Notes -->
      <div class="mb-4">
        <label class="form-label" for="adjustmentNotes">@lang('Notes')</label>
        <textarea name="adjustmentNotes" id="adjustmentNotes" class="form-control"
                  rows="3" placeholder="Add notes (optional)"></textarea>
      </div>

      <button type="submit" class="btn btn-primary me-3" id="adjustmentSubmitBtn">@lang('Save Adjustment')</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
    </form>
  </div>
</div>
<!-- /Payroll Adjustment Modal -->
