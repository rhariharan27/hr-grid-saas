@php
  use App\Enums\Gender;
  use App\Helpers\StaticDataHelpers;
  use App\Services\AddonService\IAddonService;use Nwidart\Modules\Facades\Module;
  $banks = StaticDataHelpers::getIndianBanksList();
  $addonService = app(IAddonService::class);
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Create Employee')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
    'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',

  ])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite([
    'resources/assets/js/app/employee-create-validation.js',
    'resources/assets/js/app/employee-create.js',
  ])
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
          integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script>
    $(function () {
      $('#attendanceType').on('change', function () {
        var value = this.value;
        console.log(value);

        $('#ipGroupDiv').hide();
        $('#ipGroupId').val('');
        $('#qrGroupDiv').hide();
        $('#qrGroupId').val('');
        $('#dynamicQrDiv').hide();
        $('#dynamicQrId').val('');
        $('#siteId').val('');
        $('#siteDiv').hide();
        $('#geofenceGroupId').val('');
        $('#geofenceGroupDiv').hide();

        if (value === 'geofence') {
          $('#geofenceGroupDiv').show();
          getGeofenceGroups();
        } else if (value === 'ipAddress') {
          $('#ipGroupDiv').show();
          getIpGroups();
        } else if (value === 'staticqr') {
          $('#qrGroupDiv').show();
          getQrGroups();
        } else if (value == 'site') {
          $('#siteDiv').show();
          getSites();
        } else if (value == 'dynamicqr') {
          $('#dynamicQrDiv').show();
          getDynamicQrDevices();
        }else {
          $('#geofenceGroupDiv').hide();
          $('#ipGroupDiv').hide();
          $('#qrGroupDiv').hide();
          $('#dynamicQrDiv').hide();
          $('#siteDiv').hide();
        }
      });
    });

    function getDynamicQrDevices() {
      $.ajax({
        url: '{{route('employee.getDynamicQrDevices')}}',
        type: 'GET',
        success: function (response) {
          if (response.length === 0) {
            showErrorToast('Please create a dynamic qr device first');
            return;
          }
          var options = '<option value="">Please select a dynamic qr device</option>';
          response.forEach(function (item) {
            options += '<option value="' + item.id + '">' + item.name + '</option>';
          });
          $('#dynamicQrId').html(options);
        },
        error: function (error) {
          console.log(error);
        }
      });
    }

    function getGeofenceGroups() {
      $.ajax({
        url: '{{route('employee.getGeofenceGroups')}}',
        type: 'GET',
        success: function (response) {
          if (response.length === 0) {
            showErrorToast('Please create a geofence group first');
            return;
          }
          var options = '<option value="">Please select a geofence group</option>';
          response.forEach(function (item) {
            options += '<option value="' + item.id + '">' + item.name + '</option>';
          });
          $('#geofenceGroupId').html(options);
        },
        error: function (error) {
          console.log(error);
        }
      });
    }

    function getIpGroups() {
      $.ajax({
        url: '{{route('employee.getIpGroups')}}',
        type: 'GET',
        success: function (response) {
          if (response.length === 0) {
            showErrorToast('Please create a ip group first');
            return;
          }
          var options = '<option value="">Please select a ip group</option>';
          response.forEach(function (item) {
            options += '<option value="' + item.id + '">' + item.name + '</option>';
          });
          $('#ipGroupId').html(options);
        },
        error: function (error) {
          console.log(error);
        }
      });
    }

    function getQrGroups() {
      $.ajax({
        url: '{{route('employee.getQrGroups')}}',
        type: 'GET',
        success: function (response) {
          if (response.length === 0) {
            showErrorToast('Please create a qr group first');
            return;
          }
          var options = '<option value="">Please select a qr group</option>';
          response.forEach(function (item) {
            options += '<option value="' + item.id + '">' + item.name + '</option>';
          });
          $('#qrGroupId').html(options);
        },
        error: function (error) {
          console.log(error);
        }
      });
    }

    function getSites() {
      $.ajax({
        url: '{{route('employee.getSites')}}',
        type: 'GET',
        success: function (response) {
          if (response.length === 0) {
            showErrorToast('Please create a site first');
            return;
          }
          var options = '<option value="">Please select a site</option>';
          response.forEach(function (item) {
            options += '<option value="' + item.id + '">' + item.name + '</option>';
          });
          $('#siteId').html(options);
        },
        error: function (error) {
          console.log(error);
        }
      });
    }
  </script>
@endsection
@section('content')

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
      <div class="d-flex align-items-center">
        <div class="alert-message">
          <strong>Error!</strong>
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  <div class="col-12 mb-6">
    <h4 class="">Employee Creation</h4>
    <div id="wizard-validation" class="bs-stepper mt-2">
      <div class="bs-stepper-header">
        <div class="step" data-target="#personal-details-validation">
          <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">1</span>
            <span class="bs-stepper-label">
            <span class="bs-stepper-title">Personal Information</span>
          </span>
          </button>
        </div>
        <div class="line">
          <i class="bx bx-chevron-right"></i>
        </div>
        <div class="step" data-target="#employee-info-validation">
          <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">2</span>
            <span class="bs-stepper-label">
            <span class="bs-stepper-title">Employment Details</span>
          </span>
          </button>
        </div>
        <div class="line">
          <i class="bx bx-chevron-right"></i>
        </div>
        <div class="step" data-target="#salary-validation">
          <button type="button" class="step-trigger">
            <span class="bs-stepper-circle">3</span>
            <span class="bs-stepper-label">
            <span class="bs-stepper-title">Compensation & Benefits</span>
          </span>
          </button>
        </div>
      </div>
      <div class="bs-stepper-content">
        <form id="wizard-validation-form" method="post" action="{{route('employees.store')}}"
              enctype="multipart/form-data"
              onSubmit="return false">
          @csrf
          <!-- Personal details -->
          <div id="personal-details-validation" class="content">
            <div class="content-header mb-4">
              <h6 class="mb-0">Personal Information</h6>
              <small>Enter Your Personal Details.</small>
            </div>
            <div class="row g-6">
              <div class="col-sm-6">
                <label class="form-label" for="file">Profile Picture</label>
                <input type="file" name="file" id="file" class="form-control"
                       placeholder="Upload profile picture"/>
                <span class="text-muted">Upload a profile picture (Only .jpg, .jpeg, .png and <=
                  5MB)</span>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="firstName">First Name <span class="text-danger">*</span> </label>
                <input type="text" name="firstName" id="firstName" class="form-control"
                       placeholder="Enter first name"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="lastName">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="lastName" id="lastName" class="form-control"
                       placeholder="Enter last name"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="gender">Gender <span class="text-danger">*</span></label>
                <select class="selectpicker w-auto" id="gender" data-style="btn-transparent"
                        data-icon-base="bx" data-tick-icon="bx-check text-white" name="gender">
                  <option value="" selected>Select Gender</option>
                  @foreach(Gender::cases() as $gender)
                    <option value="{{$gender->value}}">{{ucfirst($gender->value)}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                <input type="number" name="phone" id="phone" class="form-control"
                       placeholder="Enter phone number"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="altPhone">Alternative Mobile No</label>
                <input type="number" name="altPhone" id="altPhone" class="form-control"
                       placeholder="Enter alternate number"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control"
                       placeholder="Enter email address"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                <select class="selectpicker select2 w-auto" id="role" data-style="btn-transparent"
                        data-icon-base="bx" data-tick-icon="bx-check text-white" name="role">
                  <option value="" selected>Select Role</option>
                  @foreach ($roles as $role)
                    <option value="{{$role->name}}">{{$role->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="dob">Date of Birth <span class="text-danger">*</span></label>
                <input type="date" name="dob" id="dob" class="form-control"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="passport_number">Passport Number</label>
                <input type="text" name="passport_number" id="passport_number" class="form-control" placeholder="Enter passport number" />
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="passport_expiry_date">Passport Expiry Date</label>
                <input type="date" name="passport_expiry_date" id="passport_expiry_date" class="form-control" />
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="visa_number">Visa Number</label>
                <input type="text" name="visa_number" id="visa_number" class="form-control" placeholder="Enter visa number" />
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="visa_expiry_date">Visa Expiry Date</label>
                <input type="date" name="visa_expiry_date" id="visa_expiry_date" class="form-control" />
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="address">Address</label>
                <textarea name="address" id="address" class="form-control"
                          placeholder="Enter complete address"></textarea>
              </div>
              <div class="col-12 g-6 mb-4">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="useDefaultPassword" name="useDefaultPassword"
                         checked>
                  <label class="form-check label" for="useDefaultPassword">Use Default Password</label>
                </div>
              </div>
              <div class="row mb-4" id="passwordDiv" style="display: none;">
                <div class="col-sm-6">
                  <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                  <input type="password" name="password" id="password" class="form-control"
                         placeholder="Enter password"/>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="confirmPassword">Confirm Password <span
                      class="text-danger">*</span></label>
                  <input type="password" name="confirmPassword" id="confirmPassword" class="form-control"
                         placeholder="Re-enter password"/>
                </div>
              </div>

              @if($settings->is_helper_text_enabled)
                <div class="alert alert-primary alert-dismissible" role="alert">
                  <h6 class="alert-heading">Note</h6>
                  <p class="mb-0">If you check the "Use Default Password" checkbox, the default password will be
                    <strong>{{$settings->default_password}}</strong></p>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  </button>
                </div>
              @endif
            </div>
            <div class="col-12 d-flex justify-content-between">
              <button class="btn btn-label-secondary btn-prev" disabled>
                <i class="bx bx-left-arrow-alt bx-sm ms-sm-n2 me-sm-2"></i>
                <span class="align-middle d-sm-inline-block d-none">Previous</span>
              </button>
              <button class="btn btn-primary btn-next">
                <span class="align-middle d-sm-inline-block d-none me-sm-2">Next</span>
                <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
              </button>
            </div>
          </div>

          <!-- employment details -->
          <div id="employee-info-validation" class="content">
            <div class="content-header mb-4">
              <h6 class="mb-0">Work Details</h6>
              <small>Enter the work details.</small>
            </div>
            <div class="row g-6">
              <div class="col-sm-6">
                <label class="form-label" for="code">Employee Code <span class="text-danger">*</span></label>
                <input type="text" name="code" id="code" class="form-control"
                       placeholder="Enter employee code"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="doj">Date of Joining <span class="text-danger">*</span></label>
                <input type="date" id="doj" name="doj" class="form-control"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="designationId">Designation <span class="text-danger">*</span></label>
                <select class="selectpicker select2 w-auto" id="designationId" data-style="btn-transparent"
                        data-icon-base="bx" data-tick-icon="bx-check text-white" name="designationId">
                  <option value="" selected>Select a designation</option>
                  @foreach ($designations as $designation)
                    <option value="{{$designation->id}}">{{$designation->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="teamId">Team <span class="text-danger">*</span></label>
                <select class="selectpicker select2 w-auto" id="teamId" data-style="btn-transparent"
                        data-icon-base="bx" data-tick-icon="bx-check text-white" name="teamId">
                  <option value="" selected>Select a team</option>
                  @foreach ($teams as $team)
                    <option value="{{$team->id}}">{{$team->code}} - {{$team->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="shiftId">Shifts <span class="text-danger">*</span></label>
                <select class="selectpicker select2 w-auto" id="shiftId" data-style="btn-transparent"
                        data-icon-base="bx" data-tick-icon="bx-check text-white" name="shiftId">
                  <option value="" selected>Select a shift</option>
                  @foreach ($shifts as $shift)
                    <option value="{{$shift->id}}">{{$shift->code}} - {{$shift->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="reportingToId">Reporting To <span class="text-danger">*</span></label>
                <select class="selectpicker select2 w-auto" id="reportingToId" data-style="btn-transparent"
                        data-icon-base="bx" data-tick-icon="bx-check text-white" name="reportingToId">
                  <option value="" selected>Select reporting manager</option>
                  @foreach ($users as $user)
                    <option value="{{$user->id}}">{{$user->code}}
                      : {{$user->first_name.' '.$user->last_name}}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-sm-6">
                <label class="form-label" for="attendanceType">Attendance Type <span
                    class="text-danger">*</span></label>
                <select class="selectpicker select2 w-auto" id="attendanceType" data-style="btn-transparent"
                        data-icon-base="bx" data-tick-icon="bx-check text-white" name="attendanceType">
                  <option value="open" selected>Open</option>
                  @if($addonService->isAddonEnabled(ModuleConstants::GEOFENCE))
                    <option value="geofence">Geofence</option>
                  @endif
                  @if($addonService->isAddonEnabled(ModuleConstants::IP_ADDRESS_ATTENDANCE))
                    <option value="ipAddress">IP Address</option>
                  @endif
                  @if($addonService->isAddonEnabled(ModuleConstants::QR_ATTENDANCE))
                    <option value="staticqr">Static QR</option>
                  @endif
                  @if($addonService->isAddonEnabled(ModuleConstants::DYNAMIC_QR_ATTENDANCE))
                    <option value="dynamicqr">Dynamic QR</option>
                  @endif
                  @if($addonService->isAddonEnabled(ModuleConstants::SITE_ATTENDANCE))
                    <option value="site">Site</option>
                  @endif
                  @if($addonService->isAddonEnabled(ModuleConstants::FACE_ATTENDANCE))
                    <option value="face">Face</option>
                  @endif
                </select>
              </div>
              <div class="form-group col-sm-6 mb-3" id="geofenceGroupDiv" style="display:none;">
                <label for="geofenceGroupId" class="control-label">Geofence Group</label>
                <select id="geofenceGroupId" name="geofenceGroupId" class="form-select mb-3"></select>
                <span class="text-danger">{{ $errors->first('geofenceGroupId', ':message') }}</span>
              </div>
              <div class="form-group col-sm-6 mb-3" id="ipGroupDiv" style="display:none;">
                <label for="ipGroupId" class="control-label">Ip Group</label>
                <select id="ipGroupId" name="ipGroupId" class="form-select mb-3"></select>
                <span class="text-danger">{{ $errors->first('ipGroupId', ':message') }}</span>
              </div>
              <div class="form-group col-sm-6 mb-3" id="dynamicQrDiv" style="display:none;">
                <label for="dynamicQrId" class="control-label">Qr Device</label>
                <select id="dynamicQrId" name="dynamicQrId" class="form-select mb-3"></select>
                <span class="text-danger">{{ $errors->first('dynamicQrId', ':message') }}</span>
              </div>
              <div class="form-group col-sm-6 mb-3" id="qrGroupDiv" style="display:none;">
                <label for="qrGroupId" class="control-label">Qr Group</label>
                <select id="qrGroupId" name="qrGroupId" class="form-select mb-3"></select>
                <span class="text-danger">{{ $errors->first('qrGroupId', ':message') }}</span>
              </div>
              <div class="form-group col-md-3 mb-3" id="siteDiv" style="display:none;">
                <label for="siteId" class="control-label">Site</label>
                <select id="siteId" name="siteId" class="form-select mb-3"></select>
                <span class="text-danger">{{ $errors->first('siteId', ':message') }}</span>
              </div>
              @if($settings->is_helper_text_enabled)
                <div class="alert alert-primary alert-dismissible" role="alert">
                  <h6 class="alert-heading">Note</h6>
                  <ul>
                    <li><strong>None</strong> Open attendance system without any restriction</li>
                    <li><strong>Geofence</strong> Allow attendance only from the selected geofence group</li>
                    <li><strong>IP Address</strong> Allow attendance only from the selected IP group</li>
                    <li><strong>Dynamic QR Code</strong> Allow attendance only from the selected dynamic qr device</li>
                    <li><strong>Static QR Code</strong> Allow attendance only from the selected QR group</li>
                    <li><strong>Face</strong> Allow attendance using face recognition</li>
                    <li><strong>Site</strong> Allow attendance only from the selected site (Site will have its own
                      geofence, ip, qr code)
                    </li>
                  </ul>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                  </button>
                </div>
              @endif

              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-primary btn-prev">
                  <i class="bx bx-left-arrow-alt bx-sm ms-sm-n2 me-sm-2"></i>
                  <span class="align-middle d-sm-inline-block d-none">Previous</span>
                </button>
                <button class="btn btn-primary btn-next">
                  <span class="align-middle d-sm-inline-block d-none me-sm-2">Next</span>
                  <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Compensation -->
          <div id="salary-validation" class="content">
            <div class="content-header mb-4">
              <h6 class="mb-0">Salary Information</h6>
              <small>Enter Employee Salary Details.</small>
            </div>
            <div class="row g-6">
              <div class="col-sm-6">
                <label class="form-label" for="baseSalary">Base Salary <span class="text-danger">*</span></label>
                <input type="number" name="baseSalary" id="baseSalary" class="form-control"
                       placeholder="Enter Salary"/>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="availableLeaveCount">Available Leave Count</label>
                <input type="number" name="availableLeaveCount" id="availableLeaveCount" class="form-control"
                       placeholder="Enter available leave count"/>
              </div>
              <div class="col-12 d-flex justify-content-between">
                <button class="btn btn-primary btn-prev">
                  <i class="bx bx-left-arrow-alt bx-sm ms-sm-n2 me-sm-2"></i>
                  <span class="align-middle d-sm-inline-block d-none">Previous</span>
                </button>
                <button class="btn btn-success btn-next btn-submit">Submit</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection


