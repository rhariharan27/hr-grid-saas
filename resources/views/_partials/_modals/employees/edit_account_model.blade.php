@php use App\Helpers\StaticDataHelpers; @endphp
  <!-- Edit Account Model -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditBankAccount"
     aria-labelledby="offcanvasEditBankAccountLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasEditBankAccountLabel" class="offcanvas-title">@lang('Edit Bank Account')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="pt-0" id="bankAccountForm" action="{{route('employees.addOrUpdateBankAccount')}}" method="POST">
      @csrf
      <input type="hidden" name="userId" id="userId" value="{{$user->id}}">
      <input type="hidden" name="id" id="id"
             value="{{$user->bankAccount != null ? $user->bankAccount->id : ''}}">

      <div class="mb-6">
        <label class="form-label" for="accountNumber">@lang('Account Number')<span
            class="text-danger">*</span></label>
        <input type="text" class="form-control" id="accountNumber" placeholder="@lang('Enter account number')"
               value="{{$user->bankAccount != null ?$user->bankAccount->account_number: ''}}"
               name="accountNumber" />
      </div>

      <div class="mb-6">
        <label class="form-label" for="ifscCode">@lang('IFSC Code')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="ifscCode" placeholder="@lang('Enter IFSC code')"
               value="{{$user->bankAccount != null ?$user->bankAccount->ifsc_code : ''}}"
               name="ifscCode" />
      </div>

      <div class="mb-6">
        <label class="form-label" for="bankName">@lang('Bank Name')<span class="text-danger">*</span></label>
        <select class="form-control select2" id="bankName" data-style="btn-transparent"
                data-icon-base="bx" data-tick-icon="bx-check text-white" name="bankName">
          <option value="">Select Bank</option>
          @foreach (StaticDataHelpers::getIndianBanksList() as $bank)
            <option
              value="{{$bank}}" {{($user->bankAccount != null && $user->bankAccount->bank_name == $bank) ? 'selected' : ''}}>{{$bank}}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-6">
        <label class="form-label" for="branch">@lang('Branch')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="branch" placeholder="@lang('Enter branch name')"
               name="branch"
               value="{{$user->bankAccount != null ?$user->bankAccount->branch:''}}" />
      </div>

      <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Save')</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
    </form>
  </div>
</div>
<!-- /Edit Account Model -->
