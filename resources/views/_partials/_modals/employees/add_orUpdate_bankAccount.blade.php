<!-- Edit Bank Account Information Modal -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddAccount"
     aria-labelledby="offcanvasAddAccountLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddAccountLabel" class="offcanvas-title">@lang('Bank Account Update')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body mx-0 flex-grow-0 p-4 h-100">
    <form action="{{ route('employees.addOrUpdateBankAccount') }}" method="POST">
      @csrf
      <input type="hidden" name="userId" id="userId" value="{{ $user->id }}">

      <!-- Bank Name -->
      <div class="mb-3">
        <label class="form-label" for="bankName">@lang('Bank Name')</label>
        <input type="text" name="bankName" id="bankName" class="form-control"
               placeholder="@lang('Enter Bank Name')"
               value="{{ $user->bankAccount ? $user->bankAccount->bank_name : '' }}"
               required/>
      </div>

      <!-- Bank Code -->
      <div class="mb-3">
        <label class="form-label" for="bankCode">@lang('Bank Code')</label>
        <input type="text" name="bankCode" id="bankCode" class="form-control"
               placeholder="@lang('Enter Bank Code')" value="{{$user->bankAccount ? $user->bank_code : '' }}"/>
      </div>

      <!-- Account Name -->
      <div class="mb-3">
        <label class="form-label" for="accountName">@lang('Account Name')</label>
        <input type="text" name="accountName" id="accountName" class="form-control"
               placeholder="@lang('Enter Account Name')" value="{{$user->bankAccount ? $user->account_name : '' }}"
               required/>
      </div>

      <!-- Account Number -->
      <div class="mb-3">
        <label class="form-label" for="accountNumber">@lang('Account Number')</label>
        <input type="text" name="accountNumber" id="accountNumber" class="form-control"
               placeholder="@lang('Enter Account Number')" value="{{$user->bankAccount ? $user->account_number :'' }}"
               required/>
      </div>

      <!-- Branch Name -->
      <div class="mb-3">
        <label class="form-label" for="branchName">@lang('Branch Name')</label>
        <input type="text" name="branchName" id="branchName" class="form-control"
               placeholder="@lang('Enter Branch Name')" value="{{$user->bankAccount ? $user->branch_name :'' }}"/>
      </div>

      <!-- Branch Code -->
      <div class="mb-3">
        <label class="form-label" for="branchCode">@lang('Branch Code')</label>
        <input type="text" name="branchCode" id="branchCode" class="form-control"
               placeholder="@lang('Enter Branch Code')" value="{{$user->bankAccount ? $user->branch_code :'' }}"/>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary me-3">@lang('Save Changes')</button>
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
      </div>
    </form>
  </div>
</div>
