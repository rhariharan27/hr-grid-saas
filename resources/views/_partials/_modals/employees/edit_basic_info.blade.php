@php use App\Enums\Gender;use Carbon\Carbon; @endphp
  <!-- Edit Basic Info Modal -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditBasicInfo"
     aria-labelledby="offcanvasEditBasicInfoLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasEditBasicInfoLabel" class="offcanvas-title">@lang('Edit Basic Information')</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="pt-0" id="basicInfoForm" action="{{route('employees.updateBasicInfo')}}" method="POST">
      @csrf

      <input type="hidden" name="id" id="id" value="{{ $user->id }}">
      <div class="mb-6">
        <label class="form-label" for="firstName">@lang('First Name')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="firstName" name="firstName"
               value="{{ $user->first_name }}"
               placeholder="@lang('Enter first name')" />
      </div>

      <div class="mb-6">
        <label class="form-label" for="lastName">@lang('Last Name')<span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="lastName" name="lastName" value="{{ $user->last_name }}"
               placeholder="@lang('Enter last name')" />
      </div>
      <div class="mb-6">
        <label class="form-label" for="dob">Date of Birth <span class="text-danger">*</span></label>
        <input type="date" name="dob" id="dob" class="form-control"
               value="{{ Carbon::parse($user->dob)->format('Y-m-d') }}" />
      </div>
      <div class="mb-3">
        <label for="passport_number_edit" class="form-label">Passport Number</label>
        <input type="text" class="form-control" id="passport_number_edit" name="passport_number" value="{{ $user->passport_number }}">
    </div>
    <div class="mb-3">
        <label for="passport_expiry_date_edit" class="form-label">Passport Expiry Date</label>
        <input type="date" class="form-control" id="passport_expiry_date_edit" name="passport_expiry_date" value="{{ $user->passport_expiry_date }}">
    </div>
    <div class="mb-3">
        <label for="visa_number_edit" class="form-label">Visa Number</label>
        <input type="text" class="form-control" id="visa_number_edit" name="visa_number" value="{{ $user->visa_number }}">
    </div>
    <div class="mb-3">
        <label for="visa_expiry_date_edit" class="form-label">Visa Expiry Date</label>
        <input type="date" class="form-control" id="visa_expiry_date_edit" name="visa_expiry_date" value="{{ $user->visa_expiry_date }}">
    </div>
      <div class="mb-6">
        <label class="form-label" for="gender">Gender <span class="text-danger">*</span></label>
        <select class="form-control select2" id="gender" data-style="btn-transparent"
                data-icon-base="bx" data-tick-icon="bx-check text-white" name="gender">
          <option value="" selected>Select Gender</option>
          @foreach(Gender::cases() as $gender)
            <option
              value="{{$gender->value}}" {{$user->gender == $gender->value ? 'selected':''}} >{{$gender->value}}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-6">
        <label class="form-label" for="email">@lang('Email')<span class="text-danger">*</span></label>
        <input type="email" class="form-control" id="email" name="email"
               value="{{ $user->email }}"
               placeholder="@lang('Enter email')" />
      </div>
      <div class="mb-6">
        <label class="form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
        <input type="number" name="phone" id="phone" class="form-control"
               value="{{$user->phone}}"
               placeholder="Enter phone number" />
      </div>
      <div class="mb-6">
        <label class="form-label" for="altPhone">Alternative Mobile No</label>
        <input type="number" name="altPhone" id="altPhone" class="form-control"
               value="{{$user->alternate_number}}"
               placeholder="Enter alternate number" />
      </div>
      <div class="mb-6">
        <label class="form-label" for="address">@lang('Address')</label>
        <textarea class="form-control" id="address" name="address"
                  placeholder="@lang('Enter address')">{{ $user->address }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary me-3 data-submit">@lang('Save Changes')</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">@lang('Cancel')</button>
    </form>
  </div>
</div>
<!-- /Edit Basic Info Modal -->
