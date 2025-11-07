@php
    use App\Enums\IncentiveType;
    use App\Enums\UserAccountStatus;
    use App\Services\AddonService\IAddonService;
    use Carbon\Carbon;
    use App\Helpers\StaticDataHelpers;
    $role = $user->roles()->first()->name ?? '';
    $addonService = app(IAddonService::class);
@endphp
@extends('layouts.layoutMaster')

@section('title', 'Employee Details')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-user-view.scss', 'resources/assets/css/employee-view.css'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection
@section('content')

    <div class="row">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 order-1 order-md-0">
            <!-- User Card -->
            <div class="card mb-6">
                <div class="card-body pt-12">
                    <div class="user-avatar-section text-center position-relative">
                        <!-- Profile Picture with Rounded Background -->
                        <div class="profile-picture-container position-relative d-inline-block"
                            style="width: 150px; height: 150px;">
                            <!-- Rounded Background -->
                            <div class="rounded-circle bg-label-primary position-absolute top-50 start-50 translate-middle"
                                style="width: 120px; height: 120px;">
                            </div>

                            <!-- Profile Image -->
                            @if ($user->profile_picture)
                                <img class="img-fluid rounded-circle position-absolute top-50 start-50 translate-middle"
                                    src="{{ $user->getProfilePicture() }}" height="120" width="120" alt="User avatar"
                                    id="userProfilePicture" />
                            @else
                                <h2 class="text-white position-absolute top-50 start-50 translate-middle">
                                    {{ $user->getInitials() }}</h2>
                            @endif
                            <!-- Overlay on Hover -->
                            <div class="profile-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-end justify-content-center"
                                style="display: none;">
                                <button class="btn btn-outline-light" id="changeProfilePictureButton">
                                    <i class="bx bx-camera"></i> Change
                                </button>
                            </div>
                        </div>
                        <h5 class="mt-4">{{ $user->first_name }} {{ $user->last_name }}</h5>
                        <p class="text-muted">{{ $user->code }}</p>
                        <span
                            class="badge bg-label-secondary">{{ $user->designation ? $user->designation->name : 'N/A' }}</span>
                    </div>

                    <!-- Hidden File Input for Profile Picture Upload -->
                    <form id="profilePictureForm" action="{{ route('employees.changeEmployeeProfilePicture') }}"
                        method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="hidden" name="userId" id="userId" value="{{ $user->id }}">
                        <input type="file" id="file" name="file" accept="image/*">
                    </form>
                </div>
            </div>
            <!-- /User Card -->
            {{-- Check if Terminated First --}}
            @if ($user->status == UserAccountStatus::TERMINATED)
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Terminated</h4>
                    <p>
                        This employee's employment was terminated on
                        <strong>{{ $user->exit_date ? Carbon::parse($user->exit_date)->format('d M Y') : 'N/A' }}</strong>.
                        <br>Reason: {{ $user->exit_reason ?? 'Not specified' }}
                        <br>Type: {{ $user->termination_type ?? 'Not specified' }}
                        <br>Last Working Day:
                        {{ $user->last_working_day ? Carbon::parse($user->last_working_day)->format('d M Y') : 'N/A' }}
                    </p>
                </div>
                {{-- Check other inactive statuses --}}
            @elseif($user->status == UserAccountStatus::RELIEVED)
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Relieved</h4>
                    <p>This employee has been relieved from the organization
                        on {{ Carbon::parse($user->relieved_at)->format('d M Y') }}.</p>
                </div>
            @elseif($user->status == UserAccountStatus::RETIRED)
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">Retired</h4>
                    <p>This employee has been retired from the organization on
                        {{ Carbon::parse($user->retired_at)->format('d M Y') }}.</p>
                </div>
            @else
                <!-- Employee Status & Actions Section -->
                <div class="card mb-6">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            @lang('Employee Status & Actions')
                        </h5>
                        @if ($user->status == UserAccountStatus::TERMINATED)
                            <div class="alert alert-danger" role="alert"> </div>
                        @elseif($user->status == UserAccountStatus::RELIEVED)
                            <div class="alert alert-danger" role="alert"> </div>
                        @elseif($user->status == UserAccountStatus::RETIRED)
                            <div class="alert alert-warning" role="alert"> </div>
                        @else
                            <!-- Status Toggle -->
                            <div class="d-flex align-items-center mb-4">
                                <label class="form-label me-3">@lang('Employee Status')</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="employeeStatusToggle"
                                        @if ($user->status == UserAccountStatus::ACTIVE) checked @endif
                                        onchange="toggleEmployeeStatus({{ $user->id }}, this.checked)">
                                    <label class="form-check-label" for="employeeStatusToggle">
                                        @lang($user->status == UserAccountStatus::ACTIVE ? 'Active' : 'Inactive')
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-start gap-3 flex-wrap">
                                {{-- <button class="btn btn-outline-warning" onclick="confirmEmployeeAction('relieve', {{ $user->id }})">
                <i class="bx bx-log-out me-2"></i> @lang('Relieve')
              </button>
              <button class="btn btn-outline-danger" onclick="confirmEmployeeAction('retire', {{ $user->id }})">
                <i class="bx bx-user-x me-2"></i> @lang('Retire')
              </button> --}}
                                {{-- NEW: Initiate Termination Button --}}
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#terminateEmployeeModal">
                                    <i class="bx bx-block me-1"></i> Initiate Termination
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <!-- Work Card -->
            <div class="card mb-6">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bx bx-briefcase text-muted"></i> Work Information
                    </h5>

                    <ul class="list-unstyled mb-4">
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bx bx-user-check text-muted me-2"></i>
                            <strong>Designation:</strong> <span
                                class="ms-2">{{ $user->designation ? $user->designation->name : 'N/A' }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bx bx-id-card text-muted me-2"></i>
                            <strong>Role:</strong> <span class="ms-2">{{ $user->roles()->first()->name ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bx bx-group text-muted me-2"></i>
                            <strong>Team:</strong> <span class="ms-2">{{ $user->team->name ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bx bx-time text-muted me-2"></i>
                            <strong>Shift:</strong> <span class="ms-2">{{ $user->shift->name ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bx bx-user text-muted me-2"></i>
                            <strong>Reporting To:</strong> <span
                                class="ms-2">{{ $user->reporting_to_id != null ? $user->getReportingToUserName() : 'N/A' }}</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bx bx-calendar text-muted me-2"></i>
                            <strong>Joining Date:</strong> <span
                                class="ms-2">{{ Carbon::parse($user->date_of_joining)->format('d M Y') }}</span>
                        </li>
                        <!-- Attendance Type -->
                        <li class="mb-2 d-flex align-items-center">
                            <i class="bx bx-calendar text-muted me-2"></i>
                            <strong>Attendance Type:</strong> <span class="ms-2">{{ $user->attendance_type }}</span>
                        </li>
                    </ul>

                    @if (
                        $user->status != UserAccountStatus::RELIEVED &&
                            $user->status != UserAccountStatus::RETIRED &&
                            $user->status != UserAccountStatus::TERMINATED)
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasEditWorkInfo" onclick="loadSelectList()">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </button>
                    @endif
                </div>
            </div>
            <p> Account created on <strong>{{ Carbon::parse($user->created_at)->format('d M Y') }}</strong>
                by <strong>{{ $user->createdBy != null ? $user->createdBy->getFullName() : 'Admin' }}.</strong></p>
            <!-- /Work Card -->

            <!-- Probation Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bx bx-hourglass text-muted me-2"></i>Probation Details</h5>
                    {{-- Show action buttons only if currently under probation --}}
                    @if ($user->isUnderProbation())
                        <div class="btn-group btn-group-sm" role="group" aria-label="Probation Actions">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#confirmProbationModal">Confirm</button>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#extendProbationModal">Extend</button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#failProbationModal">Fail</button> {{-- Fail might trigger termination modal directly later --}}
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Status:</strong>
                            <span class="ms-2">{{ $user->probation_status_display }}</span>
                        </li>
                        @if ($user->probation_end_date)
                            <li class="mb-2">
                                <strong>End Date:</strong>
                                <span
                                    class="ms-2">{{ Carbon::parse($user->probation_end_date)->format('d M Y') }}</span>
                                @if ($user->is_probation_extended)
                                    <span class="badge bg-label-warning ms-1">Extended</span>
                                @endif
                            </li>
                        @endif
                        @if ($user->probation_period_months)
                            <li class="mb-2">
                                <strong>Initial Period:</strong>
                                <span class="ms-2">{{ $user->probation_period_months }} Month(s)</span>
                            </li>
                        @endif
                        @if ($user->probation_remarks)
                            <li class="mt-2 pt-2 border-top">
                                <strong>Remarks:</strong>
                                <p class="text-muted mb-0" style="white-space: pre-wrap;">{{ $user->probation_remarks }}
                                </p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- /Probation Details Card -->


        </div>
        <!--/ User Sidebar -->

        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 order-0 order-md-1">
            <!-- Tabs Navigation -->
            <div class="nav-align-top mb-6">
                <ul class="nav nav-pills flex-column flex-md-row">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#basic-info"><i
                                class="bx bx-info-circle me-1"></i> Basic</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#device"><i
                                class="bx bx-devices me-1"></i>
                            Device</a></li>
                    @if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bankAccount"><i
                                    class="bx bx-building me-1"></i>
                                Bank Account</a></li>
                    @endif
                    @if ($addonService->isAddonEnabled(ModuleConstants::SALES_TARGET))
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#salesTargets"><i
                                    class="bx bx-target-lock me-1"></i>
                                Sales Targets</a></li>
                    @endif
                    @if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payroll"><i
                                    class="bx bx-money me-1"></i> Payroll</a></li>
                    @endif
                </ul>
            </div>
            <!-- /Tabs Navigation -->

            <!-- Tab Content -->
            <div class="tab-content">

                <!-- Basic Info Tab -->
                <div class="tab-pane fade show active" id="basic-info">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bx bx-info-circle text-muted"></i> Basic Information
                            </h5>

                            <!-- Information List -->
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-envelope text-muted me-2"></i>
                                        <span>Email</span>
                                    </div>
                                    <span class="text-muted">{{ $user->email }}</span>
                                </li>

                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-phone text-muted me-2"></i>
                                        <span>Phone</span>
                                    </div>
                                    <span
                                        class="text-muted">{{ $settings->phone_country_code . '-' . $user->phone }}</span>
                                </li>

                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-phone-call text-muted me-2"></i>
                                        <span>Alternate Contact</span>
                                    </div>
                                    <span
                                        class="text-muted">{{ $user->alternate_number != null ? $settings->phone_country_code . '-' . $user->alternate_number : 'N/A' }}</span>
                                </li>

                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-calendar text-muted me-2"></i>
                                        <span>Date of Birth</span>
                                    </div>
                                    <span class="text-muted">{{ Carbon::parse($user->dob)->format('d M Y') }}</span>
                                </li>

                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-male-female text-muted me-2"></i>
                                        <span>Gender</span>
                                    </div>
                                    <span class="text-muted">{{ ucfirst($user->gender) }}</span>
                                </li>

                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-building text-muted me-2"></i>
                                        <span>Address</span>
                                    </div>
                                    <span class="text-muted">{{ $user->address ?? 'N/A' }}</span>
                                </li>

                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-file-blank text-muted me-2"></i>
                                        <span>Passport Number</span>
                                    </div>
                                    <span class="text-muted">{{ $user->passport_number ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-calendar-event text-muted me-2"></i>
                                        <span>Passport Expiry</span>
                                    </div>
                                    <span
                                        class="text-muted">{{ $user->passport_expiry_date ? \Carbon\Carbon::parse($user->passport_expiry_date)->format('d M Y') : 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bxs-plane-alt text-muted me-2"></i>
                                        <span>Visa Number</span>
                                    </div>
                                    <span class="text-muted">{{ $user->visa_number ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-calendar-event text-muted me-2"></i>
                                        <span>Visa Expiry</span>
                                    </div>
                                    <span
                                        class="text-muted">{{ $user->visa_expiry_date ? \Carbon\Carbon::parse($user->visa_expiry_date)->format('d M Y') : 'N/A' }}</span>
                                </li>
                            </ul>

                            @if (
                                $user->status != UserAccountStatus::RELIEVED &&
                                    $user->status != UserAccountStatus::RETIRED &&
                                    $user->status != UserAccountStatus::TERMINATED)
                                <!-- Edit Button -->
                                <div class="text-start mt-4">
                                    <button class="btn btn-outline-primary" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasEditBasicInfo" onclick="loadEditBasicInfo()">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>


                    @if ($addonService->isAddonEnabled(ModuleConstants::FACE_ATTENDANCE) && $user->attendance_type == 'face_recognition')
                        @include('faceattendance::partials.face_data_view_component')
                    @endif

                    @if ($addonService->isAddonEnabled(ModuleConstants::ASSETS))
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title mb-4"> Assets
                                </h5>
                                @include('assets::_employee_assets_card', ['userId' => $user->id])
                            </div>
                        </div>
                    @endif

                </div>



                <!-- Device Section -->
                <div class="tab-pane fade" id="device">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="bx bx-devices text-muted"></i> Device Information
                            </h5>
                            @if ($user->userDevice)
                                <!-- Display Device Details -->
                                <div class="mb-3">
                                    <div class="">
                                        <h6 class="card-title mb-3 d-flex align-items-center">
                                            <i class="bx bx-mobile-alt text-muted me-2"></i>
                                            {{ ucfirst($user->userDevice->type) }} Device
                                        </h6>
                                        <ul class="list-unstyled mb-4">
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-barcode text-muted me-2"></i>
                                                <strong>Device ID:</strong> <span
                                                    class="ms-2">{{ $user->userDevice->device_id }}</span>
                                            </li>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-mobile text-muted me-2"></i>
                                                <strong>Brand:</strong> <span
                                                    class="ms-2">{{ $user->userDevice->brand ?? 'N/A' }}</span>
                                            </li>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-cog text-muted me-2"></i>
                                                <strong>SDK Version:</strong> <span
                                                    class="ms-2">{{ $user->userDevice->sdk_version ?? 'N/A' }}</span>
                                            </li>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-battery text-muted me-2"></i>
                                                <strong>Battery:</strong> <span
                                                    class="ms-2">{{ $user->userDevice->battery_percentage }}%</span>
                                            </li>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-wifi text-muted me-2"></i>
                                                <strong>WiFi:</strong> <span class="ms-2">
                                                    @if ($user->userDevice->is_wifi_on)
                                                        <span class="badge bg-success">On</span>
                                                    @else
                                                        <span class="badge bg-danger">Off</span>
                                                    @endif
                                                </span>
                                            </li>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-location-plus text-muted me-2"></i>
                                                <strong>GPS:</strong> <span class="ms-2">
                                                    @if ($user->userDevice->is_gps_on)
                                                        <span class="badge bg-success">On</span>
                                                    @else
                                                        <span class="badge bg-danger">Off</span>
                                                    @endif
                                                </span>
                                            </li>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-flag text-muted me-2"></i>
                                                <strong>Location:</strong> <span
                                                    class="ms-2">{{ $user->userDevice->latitude }},
                                                    {{ $user->userDevice->longitude }}</span>
                                            </li>
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="bx bx-cog text-muted me-2"></i>
                                                <strong>App Version:</strong> <span
                                                    class="ms-2">{{ $user->userDevice->app_version ?? 'N/A' }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <form action="{{ route('employees.removeDevice') }}" method="post"
                                    onsubmit="return false;" id="deleteDeviceForm">
                                    <input type="hidden" id="userId" name="userId" value="{{ $user->id }}">
                                    @csrf
                                    <button type="submit" onclick="showDeleteDeviceConfirmation()"
                                        class="btn btn-outline-danger">
                                        <i class="bx bx-trash me-1"></i> Remove Device
                                    </button>
                                </form>
                                @if ($settings->is_helper_text_enabled)
                                    <div class="alert alert-primary alert-dismissible mt-5" role="alert">
                                        <h6 class="text-primary"><strong>Note</strong></h6>
                                        <p class="mb-0">If you remove the device, the user will not be able to use the
                                            application until
                                            they register a new device.</p>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close">
                                        </button>
                                    </div>
                                @endif
                            @else
                                <!-- No Device Registered Message -->
                                <div class="text-center py-5">
                                    <i class="bx bx-mobile-alt text-muted" style="font-size: 4em;"></i>
                                    <p class="text-muted mt-3">No device registered</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /Device Section -->

                @if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
                    <!-- Bank Account Section -->
                    <div class="tab-pane fade" id="bankAccount">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="bx bx-target-lock text-muted"></i> @lang('Bank Account Information')
                                </h5>

                                @if (!$user->bankAccount)
                                    <!-- Add Target Button -->
                                    <div class="mb-3 text-end">
                                        @if ($user->status != UserAccountStatus::RELIEVED && $user->status != UserAccountStatus::RETIRED)
                                            <button class="btn btn-primary" data-bs-toggle="offcanvas"
                                                data-bs-target="#offcanvasAddAccount">
                                                <i class="bx bx-plus"></i> @lang('Add')
                                            </button>
                                        @endif
                                    </div>
                                @endif
                                @if ($user->bankAccount)
                                    <!-- Display Bank Account Details -->
                                    <div class="mb-3">
                                        <div class="">
                                            <ul class="list-unstyled mb-4">
                                                <li class="mb-2 d-flex align-items-center">
                                                    <i class="bx bx-building text-muted me-2"></i>
                                                    <strong>Bank Name:</strong> <span
                                                        class="ms-2">{{ $user->bankAccount->bank_name }}</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-center">
                                                    <i class="bx bx-building text-muted me-2"></i>
                                                    <strong>Bank Code:</strong> <span
                                                        class="ms-2">{{ $user->bankAccount->bank_code }}</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-center">
                                                    <i class="bx bx-user text-muted me-2"></i>
                                                    <strong>Account Holder name:</strong> <span
                                                        class="ms-2">{{ $user->bankAccount->account_name }}</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-center">
                                                    <i class="bx bx-credit-card text-muted me-2"></i>
                                                    <strong>Account Number:</strong> <span
                                                        class="ms-2">{{ $user->bankAccount->account_number }}</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-center">
                                                    <i class="bx bx-building text-muted me-2"></i>
                                                    <strong>Branch:</strong> <span
                                                        class="ms-2">{{ $user->bankAccount->branch_name }}</span>
                                                </li>
                                                <li class="mb-2 d-flex align-items-center">
                                                    <i class="bx bx-building text-muted me-2"></i>
                                                    <strong>Branch Code:</strong> <span
                                                        class="ms-2">{{ $user->bankAccount->branch_code }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    @if (
                                        $user->status != UserAccountStatus::RELIEVED &&
                                            $user->status != UserAccountStatus::RETIRED &&
                                            $user->status != UserAccountStatus::TERMINATED)
                                        <!-- Edit Button -->
                                        <div class="text-start mt-4">
                                            <button class="btn btn-outline-primary" data-bs-toggle="offcanvas"
                                                data-bs-target="#offcanvasAddAccount" onclick="loadBankDetails()">
                                                <i class="bx bx-pencil        -alt me-1"></i> Edit
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <!-- No Bank Account Registered Message -->
                                    <div class="text-center py-5">
                                        <i class="bx bx-bank text-muted" style="font-size: 4em;"></i>
                                        <p class="text-muted mt-3">No bank account registered</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if ($addonService->isAddonEnabled(ModuleConstants::SALES_TARGET))
                    <!-- Sales Targets Section -->
                    <div class="tab-pane fade" id="salesTargets">
                        @include('salestarget::partials.employee_view_content')
                    </div>
                    <!-- /Sales Targets Section -->
                @endif

                @if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
                    <!-- Payroll Section -->
                    <div class="tab-pane fade" id="payroll">
                        <!-- Compensation Info Card -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bx bx-money text-muted"></i> @lang('Compensation Info')</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-money text-muted me-2"></i>
                                            <span>@lang('Salary')</span>
                                        </div>
                                        <span class="text-muted">
                                            {{ $user->base_salary != null ? $settings->currency_symbol . $user->base_salary : 'N/A' }}
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-building text-muted me-2"></i>
                                            <span>@lang('Available Leave Count')</span>
                                        </div>
                                        <span class="text-muted">
                                            {{ $user->available_leave_count != null ? $user->available_leave_count : 'N/A' }}
                                        </span>
                                    </li>
                                </ul>

                                @if (
                                    $user->status != UserAccountStatus::RELIEVED &&
                                        $user->status != UserAccountStatus::RETIRED &&
                                        $user->status != UserAccountStatus::TERMINATED)
                                    <!-- Edit Button -->
                                    <div class="text-start mt-4">
                                        <button class="btn btn-outline-primary" data-bs-toggle="offcanvas"
                                            data-bs-target="#offcanvasEditCompInfo">
                                            <i class="bx bx-edit-alt me-1"></i> @lang('Edit')
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- /Compensation Info Card -->

                        <!-- Adjustments Info Card -->
                        <div class="card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bx bx-adjust text-muted"></i> @lang('Payroll Adjustments')</h5>
                                @if ($user->status != UserAccountStatus::RELIEVED && $user->status != UserAccountStatus::RETIRED)
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasPayrollAdjustment" id="addPayrollAdjustment">
                                        <i class="bx bx-plus"></i> @lang('Add Adjustment')
                                    </button>
                                @endif
                            </div>
                            <div class="card-body">
                                @if ($user->payrollAdjustments->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>@lang('Name')</th>
                                                    <th>@lang('Code')</th>
                                                    <th>@lang('Type')</th>
                                                    <th>@lang('Applicability')</th>
                                                    <th>@lang('Amount')</th>
                                                    <th>@lang('Percentage')</th>
                                                    <th>@lang('Actions')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($user->payrollAdjustments as $adjustment)
                                                    <tr>
                                                        <td>{{ $adjustment->name }}</td>
                                                        <td>{{ $adjustment->code }}</td>
                                                        <td>
                                                            @if ($adjustment->type === 'benefit')
                                                                <span class="badge bg-success">@lang('Benefit')</span>
                                                            @else
                                                                <span class="badge bg-danger">@lang('Deduction')</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($adjustment->applicability === 'global')
                                                                <span class="badge bg-info">@lang('Global')</span>
                                                            @else
                                                                <span class="badge bg-secondary">@lang('Employee-Specific')</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $settings->currency_symbol . number_format($adjustment->amount, 2) }}
                                                        </td>
                                                        <td>{{ $adjustment->percentage ?? '-' }}%</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if (
                                                                    $user->status != UserAccountStatus::RELIEVED &&
                                                                        $user->status != UserAccountStatus::RETIRED &&
                                                                        $user->status != UserAccountStatus::TERMINATED)
                                                                    <!-- Edit Button -->
                                                                    <a href="#"
                                                                        class="btn btn-sm btn-icon btn-warning me-2 editPayrollAdjustment"
                                                                        data-bs-toggle="offcanvas"
                                                                        data-bs-target="#offcanvasPayrollAdjustment"
                                                                        onclick="editAdjustment({{ $adjustment }})">
                                                                        <i class="bx bx-pencil"></i>
                                                                    </a>
                                                                    <!-- Delete Button -->
                                                                    <form
                                                                        action="{{ route('employees.deletePayrollAdjustment', $adjustment->id) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="btn btn-sm btn-icon btn-danger"
                                                                            onclick="return confirm('@lang('Are you sure you want to delete this adjustment?')');">
                                                                            <i class="bx bx-trash text-danger"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted text-center">@lang('No payroll adjustments found for this employee.')</p>
                                @endif
                            </div>
                        </div>

                        @if ($settings->is_helper_text_enabled)
                            <div class="alert alert-warning alert-dismissible mt-5" role="alert">
                                <h6 class="text-warning"><strong>Note</strong></h6>
                                <p>If you add adjustments here they will be applied to this employee's salary only.If you
                                    want to set a
                                    <strong>global adjustment</strong> that applies to all employees, you can do so in the
                                    <a href="{{ route('settings.index', ['tab' => 'payrollSettings']) }}" target="_blank"
                                        class="text-primary">Payroll Settings</a> page.
                                </p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                </button>
                            </div>

                            <div class="alert alert-primary alert-dismissible mt-5" role="alert">
                                <h6 class="text-primary mb-4 fw-bold">What is Payroll Adjustments?</h6>
                                <p class="mb-0">Payroll adjustments are additional benefits or deductions that are added
                                    to the
                                    employee's
                                    salary. You can add, edit, or delete adjustments as needed.</p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                </button>
                            </div>
                        @endif
                        <!-- /Adjustments Info Card -->
                    </div>
                    <!-- /Payroll Section -->
                @endif
            </div>
            <!-- /Tab Content -->
        </div>
        <!--/ User Content -->
    </div>


    @include('_partials._modals.employees.edit_compensation_info')

    @include('_partials._modals.employees.edit_basic_info')

    @include('_partials._modals.employees.edit_work_info')

    @if ($addonService->isAddonEnabled(ModuleConstants::SALES_TARGET))
        @include('salestarget::partials.add_or_update_sales_target_model')
    @endif

    @if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
        @include('_partials._modals.employees.add_orUpdate_bankAccount')
        @include('payroll::partials.add_orUpdate_payroll_adjustment')
    @endif

    {{-- NEW: Terminate Employee Modal --}}
    <div class="modal fade" id="terminateEmployeeModal" tabindex="-1" aria-labelledby="terminateEmployeeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="terminateEmployeeModalLabel">Initiate Termination for
                        {{ $user->getFullName() }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="terminateEmployeeForm" action="{{ route('employees.terminate', $user->id) }}" method="POST"
                    onsubmit="return false;">
                    @csrf {{-- Method will be POST --}}
                    <div class="modal-body">
                        <div class="row g-3">
                            {{-- Termination Type --}}
                            <div class="col-md-6">
                                <label for="terminationType" class="form-label">Termination Type <span
                                        class="text-danger">*</span></label>
                                <select id="terminationType" name="terminationType" class="select2 form-select" required>
                                    <option value="">Select Type</option>
                                    @foreach (\App\Enums\TerminationType::cases() as $type)
                                        <option value="{{ $type->value }}">
                                            {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $type->value)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            {{-- Eligible for Rehire --}}
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="isEligibleForRehire"
                                        name="isEligibleForRehire" value="1" checked>
                                    <label class="form-check-label" for="isEligibleForRehire">Eligible for
                                        Re-hire?</label>
                                    <input type="hidden" name="isEligibleForRehire" value="0">
                                    {{-- Hidden input for unchecked value --}}
                                </div>
                            </div>
                            {{-- Exit Date --}}
                            <div class="col-md-6">
                                <label for="exitDate" class="form-label">Exit Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="exitDate" name="exitDate"
                                    class="form-control flatpickr-input" placeholder="YYYY-MM-DD" required
                                    readonly="readonly">
                                <div class="invalid-feedback"></div>
                            </div>
                            {{-- Last Working Day --}}
                            <div class="col-md-6">
                                <label for="lastWorkingDay" class="form-label">Last Working Day <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="lastWorkingDay" name="lastWorkingDay"
                                    class="form-control flatpickr-input" placeholder="YYYY-MM-DD" required
                                    readonly="readonly">
                                <div class="invalid-feedback"></div>
                            </div>
                            {{-- Reason --}}
                            <div class="col-12">
                                <label for="exitReason" class="form-label">Reason <span
                                        class="text-danger">*</span></label>
                                <textarea id="exitReason" name="exitReason" class="form-control" rows="3"
                                    placeholder="Reason for termination/exit..." required></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="terminateSubmitBtn">Confirm
                            Termination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End Terminate Modal --}}

    {{-- 1. Confirm Probation Modal --}}
    <div class="modal fade" id="confirmProbationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Probation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="confirmProbationForm" action="{{ route('employees.confirmProbation', $user->id) }}"
                    method="POST" onsubmit="return false;">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to confirm the successful completion of probation for
                            {{ $user->getFullName() }}?</p>
                        <div class="mb-3">
                            <label for="confirmRemarks" class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" id="confirmRemarks" name="probationRemarks" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="confirmProbationSubmitBtn">Confirm
                            Completion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Extend Probation Modal --}}
    <div class="modal fade" id="extendProbationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Extend Probation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="extendProbationForm" action="{{ route('employees.extendProbation', $user->id) }}"
                    method="POST" onsubmit="return false;">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="newProbationEndDate" class="form-label">New Probation End Date <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="newProbationEndDate" name="newProbationEndDate"
                                class="form-control flatpickr-input" placeholder="YYYY-MM-DD" required
                                readonly="readonly">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="extendReason" class="form-label">Reason for Extension <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="extendReason" name="probationRemarks" rows="3" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="extendProbationSubmitBtn">Extend
                            Probation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. Fail Probation Modal --}}
    <div class="modal fade" id="failProbationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fail Probation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="failProbationForm" action="{{ route('employees.failProbation', $user->id) }}" method="POST"
                    onsubmit="return false;">
                    @csrf
                    <div class="modal-body">
                        <p>Failing probation will initiate the termination process for {{ $user->getFullName() }}. Please
                            provide a reason.</p>
                        <div class="mb-3">
                            <label for="failReason" class="form-label">Reason for Failure <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="failReason" name="probationRemarks" rows="3" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <p class="text-danger small">Note: Further termination details (exit date, etc.) might be required
                            in the next step or via the standard termination process.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="failProbationSubmitBtn">Confirm
                            Failure</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('page-script')
    <script>
        var user = @json($user);
        var role = @json($role);
        var attendanceType = @json($user->attendance_type);
        var terminateUrl = "{{ route('employees.terminate', $user->id) }}";

        function showDeleteDeviceConfirmation() {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (result.value) {
                    document.getElementById('deleteDeviceForm').submit();
                }
            });
        }

        function getDynamicQrDevices() {
            var dynamicQrId = '{{ $user->dynamic_qr_device_id }}';
            $.ajax({
                url: '{{ route('employee.getDynamicQrDevices') }}',
                type: 'GET',
                success: function(response) {
                    if (response.length === 0) {
                        showErrorToast('Please create a dynamic qr device first');
                        return;
                    }
                    var options = '<option value="">Please select a dynamic qr device</option>';
                    response.forEach(function(item) {
                        options += '<option value="' + item.id + '" ' + (dynamicQrId == item.id ?
                            'selected' : '') + '>' + item.name + '</option>';
                    });
                    $('#dynamicQrId').html(options);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function getGeofenceGroups() {
            var geofenceId = '{{ $user->geofence_group_id }}';
            $.ajax({
                url: '{{ route('employee.getGeofenceGroups') }}',
                type: 'GET',
                success: function(response) {
                    if (response.length === 0) {
                        showErrorToast('Please create a geofence group first');
                        return;
                    }
                    var options = '<option value="">Please select a geofence group</option>';
                    response.forEach(function(item) {
                        options += '<option value="' + item.id + '" ' + (geofenceId == item.id ?
                            'selected' : '') + '>' + item.name + '</option>';
                    });
                    $('#geofenceGroupId').html(options);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function getIpGroups() {
            var ipGroupId = '{{ $user->ip_address_group_id }}';
            $.ajax({
                url: '{{ route('employee.getIpGroups') }}',
                type: 'GET',
                success: function(response) {
                    if (response.length === 0) {
                        showErrorToast('Please create a ip group first');
                        return;
                    }
                    var options = '<option value="">Please select a ip group</option>';
                    response.forEach(function(item) {
                        options += '<option value="' + item.id + '" ' + (ipGroupId == item.id ?
                            'selected' : '') + '>' + item.name + '</option>';
                    });
                    $('#ipGroupId').html(options);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function getQrGroups() {
            var qrGroupId = '{{ $user->qr_group_id }}';
            $.ajax({
                url: '{{ route('employee.getQrGroups') }}',
                type: 'GET',
                success: function(response) {
                    if (response.length === 0) {
                        showErrorToast('Please create a qr group first');
                        return;
                    }
                    var options = '<option value="">Please select a qr group</option>';
                    response.forEach(function(item) {
                        options += '<option value="' + item.id + '" ' + (qrGroupId == item.id ?
                            'selected' : '') + '>' + item.name + '</option>';
                    });
                    $('#qrGroupId').html(options);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function getSites() {
            var siteId = '{{ $user->site_id }}';
            $.ajax({
                url: '{{ route('employee.getSites') }}',
                type: 'GET',
                success: function(response) {
                    if (response.length === 0) {
                        showErrorToast('Please create a site first');
                        return;
                    }
                    var options = '<option value="">Please select a site</option>';
                    response.forEach(function(item) {
                        options += '<option value="' + item.id + '" ' + (siteId == item.id ?
                            'selected' : '') + '>' + item.name + '</option>';
                    });
                    $('#siteId').html(options);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        /**
         * Toggle Employee Status
         */
        function toggleEmployeeStatus(userId, isActive) {
            const status = isActive ? 'activate' : 'deactivate';
            Swal.fire({
                title: `Are you sure you want to ${status} this employee?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `Yes, ${status}`,
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/employees/toggleStatus/${userId}`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: isActive ? 1 : 0
                        },
                        success: function(response) {
                            console.log(response);
                            Swal.fire({
                                title: response.data,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            });
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error',
                                text: 'Unable to change employee status.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        }
                    });
                }
            });
        }

        /**
         * Confirm Employee Action (Relieve or Retire)
         */
        function confirmEmployeeAction(action, userId) {
            const actionText = action === 'relieve' ? 'relieve' : 'retire';
            Swal.fire({
                title: `Are you sure you want to ${actionText} this employee?`,
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `Yes, ${actionText}`,
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/employees/${action}/${userId}/`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: response.data,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            });

                            //Reload after 2 seconds
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error',
                                text: `Unable to ${actionText} the employee.`,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                }
                            });
                        }
                    });
                }
            });
        }

        function loadBankDetails() {
            console.log('hit');
            console.log(user);
            $('#bankName').val(user.bank_account ? user.bank_account.bank_name : '');
            $('#bankCode').val(user.bank_account ? user.bank_account.bank_code : '');
            $('#accountName').val(user.bank_account ? user.bank_account.account_name : '');
            $('#accountNumber').val(user.bank_account ? user.bank_account.account_number : '');
            $('#branchName').val(user.bank_account ? user.bank_account.branch_name : '');
            $('#branchCode').val(user.bank_account ? user.bank_account.branch_code : '');
        }
    </script>
    {{-- Add Initialization and Handlers for Termination Modal --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const terminateModalElement = document.getElementById('terminateEmployeeModal');
            const terminateForm = document.getElementById('terminateEmployeeForm');
            const terminateSubmitBtn = document.getElementById('terminateSubmitBtn');

            // Helper to reset termination modal validation
            function resetTerminationFormValidation() {
                if (!terminateForm) return;
                terminateForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                terminateForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                $(terminateForm).find('.select2-hidden-accessible').siblings('.select2-container').removeClass(
                    'is-invalid');
            }


            // Initialize Plugins for Termination Modal
            if (terminateModalElement) {
                const modalSelects = terminateModalElement.querySelectorAll('.select2');
                modalSelects.forEach(select => {
                    $(select).select2({
                        dropdownParent: $(
                            terminateModalElement) // Ensure dropdown appears above modal
                    });
                });

                const exitDatePicker = terminateModalElement.querySelector('#exitDate');
                const lwdPicker = terminateModalElement.querySelector('#lastWorkingDay');
                if (exitDatePicker) flatpickr(exitDatePicker, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y'
                });
                if (lwdPicker) flatpickr(lwdPicker, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y'
                });

                // Handle hidden input for checkbox unchecked value
                const rehireCheckbox = terminateModalElement.querySelector('#isEligibleForRehire');
                if (rehireCheckbox) {
                    rehireCheckbox.addEventListener('change', function() {
                        const hiddenInput = this.nextElementSibling; // Assumes hidden input is next sibling
                        if (hiddenInput && hiddenInput.type === 'hidden') {
                            hiddenInput.disabled = this.checked;
                        }
                    });
                    // Initial state check
                    const hiddenInput = rehireCheckbox.nextElementSibling;
                    if (hiddenInput && hiddenInput.type === 'hidden') {
                        hiddenInput.disabled = rehireCheckbox.checked;
                    }
                }
            }

            // Termination Form Submission
            if (terminateForm) {
                terminateForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    resetTerminationFormValidation();

                    // Confirmation before submitting
                    Swal.fire({
                        title: 'Confirm Termination?',
                        text: "This action cannot be undone easily. Are you sure?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Confirm Termination',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'btn btn-danger me-3',
                            cancelButton: 'btn btn-label-secondary'
                        },
                        buttonsStyling: false
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            // Proceed with AJAX submission
                            const formData = new FormData(terminateForm);
                            const submitButton = terminateSubmitBtn;

                            submitButton.disabled = true;
                            submitButton.innerHTML =
                                '<span class="spinner-border spinner-border-sm"></span> Processing...';

                            $.ajax({
                                url: terminateUrl, // Use URL defined in script vars
                                method: 'POST', // As per your standard
                                data: formData,
                                processData: false,
                                contentType: false,
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        $('#terminateEmployeeModal').modal(
                                            'hide'); // Hide modal
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: response.message,
                                            timer: 2500,
                                            showConfirmButton: false
                                        });
                                        // Reload page after a delay to show updated status/UI
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2500);
                                    } else {
                                        // Should ideally not happen if using proper error codes
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: response.message ||
                                                'Termination failed.'
                                        });
                                    }
                                },
                                error: function(jqXHR) {
                                    if (jqXHR.status === 422 && jqXHR.responseJSON
                                        ?.errors) {
                                        // Display validation errors
                                        Object.keys(jqXHR.responseJSON.errors).forEach(
                                            key => {
                                                let inputName = key;
                                                const inputElement = terminateForm
                                                    .querySelector(
                                                        `[name="${inputName}"]`);
                                                if (inputElement) {
                                                    inputElement.classList.add(
                                                        'is-invalid');
                                                    const feedbackElement =
                                                        inputElement.closest(
                                                            '.col-md-6, .col-12')
                                                        .querySelector(
                                                            '.invalid-feedback');
                                                    if (feedbackElement)
                                                        feedbackElement
                                                        .textContent = jqXHR
                                                        .responseJSON.errors[key][
                                                            0
                                                        ];
                                                    if ($(inputElement).hasClass(
                                                            'select2-hidden-accessible'
                                                        )) {
                                                        $(inputElement).siblings(
                                                            '.select2-container'
                                                        ).addClass(
                                                            'is-invalid');
                                                    }
                                                }
                                            });
                                        const firstInvalid = terminateForm
                                            .querySelector('.is-invalid');
                                        if (firstInvalid) {
                                            $(firstInvalid).hasClass(
                                                    'select2-hidden-accessible') ? $(
                                                    firstInvalid).select2('open') :
                                                firstInvalid.focus();
                                        }

                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: jqXHR.responseJSON?.message ||
                                                'An unexpected error occurred.'
                                        });
                                    }
                                },
                                complete: function() {
                                    submitButton.disabled = false;
                                    submitButton.innerHTML = 'Confirm Termination';
                                }
                            });
                        } // end if confirmed
                    }); // end Swal confirmation
                }); // end form submit listener
            } // end if terminateForm

        }); // End DOMContentLoaded
    </script>

    <script>
        // Ensure this code is placed inside your existing
        document.addEventListener('DOMContentLoaded', function() {
            // or $(function() { ... }); block

            // --- Probation Modal Selectors ---
            const confirmProbationModalEl = document.getElementById('confirmProbationModal');
            const confirmProbationForm = document.getElementById('confirmProbationForm');
            const confirmProbationSubmitBtn = document.getElementById('confirmProbationSubmitBtn');
            const confirmProbationModal = confirmProbationModalEl ? new bootstrap.Modal(confirmProbationModalEl) :
                null;

            const extendProbationModalEl = document.getElementById('extendProbationModal');
            const extendProbationForm = document.getElementById('extendProbationForm');
            const extendProbationSubmitBtn = document.getElementById('extendProbationSubmitBtn');
            const extendProbationModal = extendProbationModalEl ? new bootstrap.Modal(extendProbationModalEl) :
                null;

            const failProbationModalEl = document.getElementById('failProbationModal');
            const failProbationForm = document.getElementById('failProbationForm');
            const failProbationSubmitBtn = document.getElementById('failProbationSubmitBtn');
            const failProbationModal = failProbationModalEl ? new bootstrap.Modal(failProbationModalEl) : null;

            // --- Flatpickr for Extend Modal ---
            const newProbationEndDateInput = document.getElementById('newProbationEndDate');
            if (newProbationEndDateInput) {
                // Get current end date to set minDate for extension (optional)
                // This requires passing the current end date from blade to JS if needed.
                const currentProbationEndDate = '{{ $user->probation_end_date?->toDateString() }}';
                flatpickr(newProbationEndDateInput, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'M j, Y',
                    minDate: currentProbationEndDate ? new Date(currentProbationEndDate).fp_incr(1) :
                        "today" // Set min date to day after current end date
                });
            }

            // --- Reset Validation Helpers ---
            function resetModalValidation(formElement) {
                if (!formElement) return;
                formElement.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                formElement.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                // Note: No Select2 in these modals currently
            }

            // --- Generic AJAX Submit Handler for Modals ---
            function handleModalFormSubmit(formElement, submitButton, modalInstance) {
                if (!formElement || !submitButton || !modalInstance) return;

                formElement.addEventListener('submit', function(e) {
                    e.preventDefault();
                    resetModalValidation(formElement); // Clear previous errors for this specific form

                    const formData = new FormData(formElement);
                    const url = formElement.action; // Get URL from form's action attribute
                    const method = 'POST'; // Always POST as per standard

                    submitButton.disabled = true;
                    submitButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm"></span> Processing...';

                    fetch(url, {
                            method: method,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json().then(data => ({
                            status: response.status,
                            body: data
                        }))) // Keep status code
                        .then(({
                            status,
                            body
                        }) => {
                            if (body.success) {
                                modalInstance.hide();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: body.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                // Reload the page to reflect changes (simplest approach)
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);

                            } else {
                                // Handle validation errors (422) or other errors
                                if (status === 422 && body.errors) {
                                    Object.keys(body.errors).forEach(key => {
                                        let inputName = key;
                                        const inputElement = formElement.querySelector(
                                            `[name="${inputName}"]`);
                                        if (inputElement) {
                                            inputElement.classList.add('is-invalid');
                                            // Find the specific invalid-feedback div for this input
                                            let feedbackElement = inputElement
                                                .nextElementSibling;
                                            while (feedbackElement && !feedbackElement.classList
                                                .contains('invalid-feedback')) {
                                                feedbackElement = feedbackElement
                                                    .nextElementSibling;
                                            }
                                            if (feedbackElement) {
                                                feedbackElement.textContent = body.errors[key][
                                                    0
                                                ];
                                            }
                                        }
                                    });
                                    const firstInvalid = formElement.querySelector('.is-invalid');
                                    if (firstInvalid) firstInvalid.focus();

                                } else {
                                    // General error message from backend or default
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: body.message || 'An unexpected error occurred.'
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error submitting probation form:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An unexpected network error occurred.'
                            });
                        })
                        .finally(() => {
                            // Reset button state
                            submitButton.disabled = false;
                            // Reset button text based on action (requires knowing which button)
                            // We can add a data attribute to the button or check the form ID
                            if (formElement.id === 'confirmProbationForm') submitButton.innerHTML =
                                'Confirm Completion';
                            else if (formElement.id === 'extendProbationForm') submitButton.innerHTML =
                                'Extend Probation';
                            else if (formElement.id === 'failProbationForm') submitButton.innerHTML =
                                'Confirm Failure';
                            else submitButton.innerHTML = 'Submit'; // Default fallback
                        });
                }); // End addEventListener
            } // End handleModalFormSubmit

            // --- Attach Submit Handlers ---
            handleModalFormSubmit(confirmProbationForm, confirmProbationSubmitBtn, confirmProbationModal);
            handleModalFormSubmit(extendProbationForm, extendProbationSubmitBtn, extendProbationModal);
            handleModalFormSubmit(failProbationForm, failProbationSubmitBtn, failProbationModal);

            // --- Reset forms when modals are closed ---
            if (confirmProbationModalEl) {
                confirmProbationModalEl.addEventListener('hidden.bs.modal', function() {
                    resetModalValidation(confirmProbationForm);
                    confirmProbationForm?.reset();
                });
            }
            if (extendProbationModalEl) {
                extendProbationModalEl.addEventListener('hidden.bs.modal', function() {
                    resetModalValidation(extendProbationForm);
                    extendProbationForm?.reset();
                    // Reset flatpickr
                    const fp = document.querySelector('#newProbationEndDate')._flatpickr;
                    if (fp) fp.clear();
                });
            }
            if (failProbationModalEl) {
                failProbationModalEl.addEventListener('hidden.bs.modal', function() {
                    resetModalValidation(failProbationForm);
                    failProbationForm?.reset();
                });
            }

            // Make sure this closing brace is for the main DOMContentLoaded or $(function(){...})
        });
    </script>
    @vite(['resources/js/main-helper.js', 'resources/assets/js/app/employee-view.js'])
@endsection
