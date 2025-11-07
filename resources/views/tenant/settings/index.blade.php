@php
    use App\Models\SuperAdmin\SaSettings;
    use App\Services\AddonService\IAddonService;
    use Modules\Payroll\app\Models\PayrollAdjustment;
    $addonService = app(IAddonService::class);
@endphp
@extends('layouts/layoutMaster')

@section('title', __('Settings'))


<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
    <div class="row g-6">

        <!-- Navigation -->
        <div class="col-12 col-lg-4">
            <div class="d-flex justify-content-between flex-column mb-4 mb-md-0">
                <h5 class="mb-4">@lang('Settings')</h5>
                <ul class="nav nav-align-left nav-pills flex-column" id="settingsMenu">
                    <li class="nav-item mb-1">
                        <a class="nav-link active" href="?tab=generalSettings" data-bs-toggle="pill"
                            data-bs-target="#generalSettings">
                            <i class="bx bx-shape-square bx-sm me-1_5"></i>
                            <span class="align-middle">General</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="?tab=appSettings" data-bs-toggle="pill" data-bs-target="#appSettings">
                            <i class="bx bx-cog bx-sm me-1_5"></i>
                            <span class="align-middle">App Settings</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="?tab=employeeSettings" data-bs-toggle="pill"
                            data-bs-target="#employeeSettings">
                            <i class="bx bx-user bx-sm me-1_5"></i>
                            <span class="align-middle">Employee Settings</span>
                        </a>
                    </li>
                    @if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
                        <li class="nav-item mb-1">
                            <a class="nav-link" href="?tab=payrollSettings" data-bs-toggle="pill"
                                data-bs-target="#payrollSettings">
                                <i class="bx bx-money bx-sm me-1_5"></i>
                                <span class="align-middle">Payroll Settings</span>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="?tab=trackingSettings" data-bs-toggle="pill"
                            data-bs-target="#trackingSettings">
                            <i class="bx bx-location-plus bx-sm me-1_5"></i>
                            <span class="align-middle">Tracking</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="?tab=codePrefixSettings" data-bs-toggle="pill"
                            data-bs-target="#codePrefixSettings">
                            <i class="bx bx-code-block bx-sm me-1_5"></i>
                            <span class="align-middle">Code Prefix/Suffix</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="?tab=mapsSettings" data-bs-toggle="pill" data-bs-target="#mapsSettings">
                            <i class="bx bx-map bx-sm me-1_5"></i>
                            <span class="align-middle">Maps</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="?tab=companySettings" data-bs-toggle="pill"
                            data-bs-target="#companySettings">
                            <i class="bx bx-buildings bx-sm me-1_5"></i> Company Settings
                        </a>
                    </li>
                    @if ($addonService->isAddonEnabled(ModuleConstants::AI_CHATBOT))
                        <li class="nav-item mb-1">
                            <a class="nav-link" href="?tab=aiSettings" data-bs-toggle="pill" data-bs-target="#aiSettings">
                                <i class="bx bx-brain bx-sm me-1_5"></i> AI Settings <span
                                    class="badge bg-danger rounded-pill ms-auto">Beta</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <!-- /Navigation -->

        <!-- Options -->
        <div class=" col-12 col-lg-8 pt-6 pt-lg-0">
            <div class="tab-content p-0">

                <!-- General Settings Tab -->
                <div class="tab-pane fade show active" id="generalSettings" role="tabpanel">
                    <form action="{{ route('settings.updateGeneralSettings') }}" method="POST">
                        @csrf
                        <div class="card mb-6">
                            <div class="card-header">
                                <h5 class="card-title m-0">General Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6">
                                    <div class="col-12 col-md-6">
                                        <label for="appName" class="form-label">App Name</label>
                                        <input type="text" class="form-control" id="appName" name="appName"
                                            value="{{ $settings->app_name ?? '' }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="country" name="country"
                                            value="{{ $settings->country ?? '' }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="phoneCountryCode" class="form-label">Phone Country Code</label>
                                        <input type="text" class="form-control" id="phoneCountryCode"
                                            name="phoneCountryCode" value="{{ $settings->phone_country_code ?? '' }}">
                                    </div>
                                    {{--  <div class="col-12 col-md-6">
                      <label for="timezone" class="form-label">Timezone</label>
                      <select class="form-select" id="timezone" name="timezone">
                        @foreach (timezone_identifiers_list() as $timezone)
                          <option
                            value="{{ $timezone }}" {{ ($settings->timezone ?? 'UTC') === $timezone ? 'selected' : '' }}>
                            {{ $timezone }}
                          </option>
                        @endforeach
                      </select>
                    </div> --}}
                                    <div class="col-12 col-md-6">
                                        <label for="currency" class="form-label">Currency</label>
                                        <input type="text" class="form-control" id="currency" name="currency"
                                            value="{{ $settings->currency ?? '' }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="currencySymbol" class="form-label">Currency Symbol</label>
                                        <input type="text" class="form-control" id="currencySymbol"
                                            name="currencySymbol" value="{{ $settings->currency_symbol ?? '' }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="distanceUnit" class="form-label">Distance Unit</label>
                                        <select id="distanceUnit" class="form-select" name="distanceUnit">
                                            <option value="km"
                                                {{ ($settings->distance_unit ?? 'km') == 'km' ? 'selected' : '' }}>
                                                Kilometers
                                            </option>
                                            <option value="miles"
                                                {{ ($settings->distance_unit ?? 'km') == 'miles' ? 'selected' : '' }}>
                                                Miles
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Enable Helper Text</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="isHelperTextEnabled"
                                                name="isHelperTextEnabled"
                                                {{ $settings->is_helper_text_enabled ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isHelperTextEnabled">
                                                @if ($settings->is_helper_text_enabled)
                                                    Enabled
                                                @else
                                                    Disabled
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /General Settings -->

                <!-- App Settings -->
                <div class="tab-pane fade" id="appSettings" role="tabpanel">
                    <form action="{{ route('settings.updateAppSettings') }}" method="POST">
                        @csrf
                        <div class="card mb-6">
                            <div class="card-header">
                                <h5 class="card-title m-0">Mobile App Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6">
                                    <div class="col-12 col-md-6">
                                        <label for="mAppVersion" class="form-label">Mobile App Version</label>
                                        <input type="text" class="form-control" id="mAppVersion" name="mAppVersion"
                                            value="{{ $settings->m_app_version ?? '' }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="locationDistanceFilter" class="form-label">Location Distance Filter(in
                                            meters)</label>
                                        <input type="number" class="form-control" id="locationDistanceFilter"
                                            name="locationDistanceFilter"
                                            value="{{ $settings->m_location_distance_filter ?? '' }}">
                                    </div>
                                    @if ($settings->is_helper_text_enabled)
                                        <div class="alert alert-primary alert-dismissible" role="alert">
                                            <h6 class="alert-heading">Important Note: </h6>
                                            <p class="mb-0">Please note that the location distance filter is used to
                                                filter out
                                                location
                                                updates
                                                that are less than the specified distance. This is useful to reduce the
                                                number of
                                                location
                                                updates
                                                sent to the server.</p>
                                            <p> We recommend using a <strong>10 meters distance filter</strong> for most use
                                                cases.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /App Settings -->

                <!-- Employee Settings -->
                <div class="tab-pane fade" id="employeeSettings" role="tabpanel">
                    <form action="{{ route('settings.updateEmployeeSettings') }}" method="POST">
                        @csrf
                        <div class="card mb-6">
                            <div class="card-header">
                                <h5 class="card-title m-0">Employee Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6">

                                    <!-- Biometric Verification -->
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Enable Biometric Verification</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="isBioMetricVerificationEnabled" name="isBioMetricVerificationEnabled"
                                                {{ $settings->is_biometric_verification_enabled ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isBioMetricVerificationEnabled">
                                                {{ $settings->is_biometric_verification_enabled ? 'Enabled' : 'Disabled' }}</label>
                                        </div>
                                    </div>

                                    <!-- Device Verification -->
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Enable Device Verification</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="isDeviceVerificationEnabled" name="isDeviceVerificationEnabled"
                                                {{ $settings->is_device_verification_enabled ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isDeviceVerificationEnabled">
                                                {{ $settings->is_device_verification_enabled ? 'Enabled' : 'Disabled' }}</label>
                                        </div>
                                    </div>

                                    <!-- Default Password -->
                                    <div class="col-12">
                                        <label for="defaultPassword" class="form-label">Default Password</label>
                                        <input type="password" class="form-control" id="defaultPassword"
                                            name="defaultPassword" value="{{ $settings->default_password }}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /Employee Settings -->

                <!-- Tracking Settings -->
                <div class="tab-pane fade" id="trackingSettings" role="tabpanel">
                    <form action="{{ route('settings.updateTrackingSettings') }}" method="POST">
                        @csrf
                        <div class="card mb-6">
                            <div class="card-header">
                                <h5 class="card-title m-0">Tracking Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6">
                                    <!-- Offline Check Time -->
                                    <div class="col-12 col-md-6">
                                        <label for="offlineCheckTime" class="form-label">Offline Check Time (In
                                            Seconds)</label>
                                        <input type="number" class="form-control" id="offlineCheckTime"
                                            name="offlineCheckTime" value="{{ $settings->offline_check_time ?? 900 }}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /Tracking Settings -->

                <!-- Code Prefix & Suffix Settings -->
                <div class="tab-pane fade" id="codePrefixSettings" role="tabpanel">
                    <form action="" method="POST">
                        @csrf
                        <div class="card mb-6">
                            <div class="card-header">
                                <h5 class="card-title m-0">Code Prefix & Suffix</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6">
                                    <!-- Employee Code Prefix -->
                                    <div class="col-12 col-md-6">
                                        <label for="employeeCodePrefix" class="form-label">Employee Code Prefix</label>
                                        <input type="text" class="form-control" id="employeeCodePrefix"
                                            name="employee_code_prefix"
                                            value="{{ $settings->employee_code_prefix ?? 'EMP' }}">
                                    </div>
                                    @if (Nwidart\Modules\Facades\Module::has('ProductOrder'))
                                        <!-- Order Prefix -->
                                        <div class="col-12 col-md-6">
                                            <label for="orderPrefix" class="form-label">Order Prefix</label>
                                            <input type="text" class="form-control" id="orderPrefix"
                                                name="order_prefix" value="{{ $settings->order_prefix ?? 'FM_ORD' }}">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Maps Settings -->
                <div class="tab-pane fade" id="mapsSettings" role="tabpanel">
                    <form action="{{ route('settings.updateMapSettings') }}" method="POST">
                        @csrf
                        <div class="card mb-6">
                            <div class="card-header">
                                <h5 class="card-title m-0">Maps Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6">
                                    <!-- Map Provider -->
                                    <div class="col-12 col-md-6">
                                        <label for="mapProvider" class="form-label">Map Provider</label>
                                        <select id="mapProvider" class="form-select" name="mapProvider">
                                            <option value="google"
                                                {{ ($settings->map_provider ?? 'google') == 'google' ? 'selected' : '' }}>
                                                Google
                                            </option>
                                            {{-- <option value="mapbox" {{ ($settings->map_provider ?? 'google') == 'mapbox' ? 'selected' : '' }}>
                         Mapbox
                       </option> --}}
                                        </select>
                                    </div>
                                    <!-- Map Zoom Level -->
                                    <div class="col-12 col-md-6">
                                        <label for="mapZoomLevel" class="form-label">Map Zoom Level</label>
                                        <input type="number" class="form-control" id="mapZoomLevel" name="mapZoomLevel"
                                            value="{{ $settings->map_zoom_level ?? 3 }}">
                                    </div>
                                    <!-- Center Latitude -->
                                    <div class="col-12 col-md-6">
                                        <label for="centerLatitude" class="form-label">Center Latitude</label>
                                        <input type="text" class="form-control" id="centerLatitude"
                                            name="centerLatitude"
                                            value="{{ $settings->center_latitude ?? '18.418983770139405' }}">
                                    </div>
                                    <!-- Center Longitude -->
                                    <div class="col-12 col-md-6">
                                        <label for="centerLongitude" class="form-label">Center Longitude</label>
                                        <input type="text" class="form-control" id="centerLongitude"
                                            name="centerLongitude"
                                            value="{{ $settings->center_longitude ?? '49.67194361588897' }}">
                                    </div>
                                    @php
                                        $usePerTenantMapKey = tenancy()->central(function () {
                                            return SaSettings::first()->use_per_tenant_map_key;
                                        });
                                    @endphp

                                    @if ($usePerTenantMapKey)
                                        <!-- Map API Key -->
                                        <div class="col-12">
                                            <label for="mapApiKey" class="form-label">Map API Key</label>
                                            <input type="text" class="form-control" id="mapApiKey" name="mapApiKey"
                                                value="{{ $settings->map_api_key ?? '' }}">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /Maps Settings -->

                <!-- Company Settings -->
                <div class="tab-pane fade" id="companySettings">
                    <form action="{{ route('settings.updateCompanySettings') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-6">
                            <div class="card-header">
                                <h5 class="card-title m-0">Company Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6">
                                    <!-- Company Logo Upload with Preview -->
                                    <div class="col-12 col-md-12">
                                        <label for="companyLogo" class="form-label">Company Logo</label>
                                        <div class="position-relative d-flex justify-content-start align-items-center">
                                            <!-- Logo Preview -->
                                            <div class="border rounded p-1 d-flex justify-content-center align-items-center"
                                                style="width: 150px; height: 150px; overflow: hidden; cursor: pointer; background: #f8f9fa;"
                                                onclick="document.getElementById('companyLogo').click();">
                                                <img id="companyLogoPreview"
                                                    src="{{ $settings->company_logo ? tenant_asset('images/' . $settings->company_logo) : 'https://placehold.co/150x150' }}"
                                                    alt="Company Logo" class="img-fluid"
                                                    style="max-width: 100%; max-height: 100%;">
                                            </div>

                                            <!-- Hidden File Input -->
                                            <input type="file" class="form-control d-none" id="companyLogo"
                                                name="company_logo" accept="image/*">

                                            <!-- Delete Logo Button -->
                                            @if ($settings->company_logo)
                                                <button type="button" class="btn btn-danger btn-sm ms-2"
                                                    id="removeLogoButton">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            @endif
                                        </div>
                                        <small class="text-muted mt-2 d-block">Click on the logo to change it. Allowed
                                            formats: JPG,
                                            PNG,
                                            Max size: 2MB</small>
                                    </div>
                                    <!-- Company Name -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyName" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="companyName" name="company_name"
                                            value="{{ $settings->company_name ?? '' }}">
                                    </div>
                                    <!-- Company Phone -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyPhone" class="form-label">Company Phone</label>
                                        <input type="text" class="form-control" id="companyPhone"
                                            name="company_phone" value="{{ $settings->company_phone ?? '' }}">
                                    </div>
                                    <!-- Company Email -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyEmail" class="form-label">Company Email</label>
                                        <input type="email" class="form-control" id="companyEmail"
                                            name="company_email" value="{{ $settings->company_email ?? '' }}">
                                    </div>
                                    <!-- Company Website -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyWebsite" class="form-label">Company Website</label>
                                        <input class="form-control" id="companyWebsite" name="company_website"
                                            value="{{ $settings->company_website ?? '' }}">
                                    </div>
                                    <!-- Company Address -->
                                    <div class="col-12">
                                        <label for="companyAddress" class="form-label">Company Address</label>
                                        <textarea type="text" class="form-control" id="companyAddress" name="company_address">{{ $settings->company_address ?? '' }}</textarea>
                                    </div>

                                    <!-- Company City -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyCity" class="form-label">Company City</label>
                                        <input type="text" class="form-control" id="companyCity" name="company_city"
                                            value="{{ $settings->company_city ?? '' }}">
                                    </div>

                                    <!-- Company Country -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyCountry" class="form-label">Company Country</label>
                                        <input type="text" class="form-control" id="companyCountry"
                                            name="company_country" value="{{ $settings->company_country ?? '' }}">
                                    </div>
                                    <!-- Company State -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyState" class="form-label">Company State</label>
                                        <input type="text" class="form-control" id="companyState"
                                            name="company_state" value="{{ $settings->company_state ?? '' }}">
                                    </div>
                                    <!-- Company ZIP -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyZipcode" class="form-label">Company Zipcode</label>
                                        <input type="text" class="form-control" id="companyZipcode"
                                            name="company_zipcode" value="{{ $settings->company_zipcode ?? '' }}">
                                    </div>
                                    <!-- TAX No -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyTaxId" class="form-label">Company Tax Id</label>
                                        <input type="text" class="form-control" id="companyTaxId"
                                            name="company_tax_id" value="{{ $settings->company_tax_id ?? '' }}">
                                    </div>
                                    <!-- Reg No -->
                                    <div class="col-12 col-md-6">
                                        <label for="companyRegNo" class="form-label">Company Reg No</label>
                                        <input type="text" class="form-control" id="companyRegNo"
                                            name="company_reg_no" value="{{ $settings->company_reg_no ?? '' }}">
                                    </div>

                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /Company Settings -->

                @if ($addonService->isAddonEnabled(ModuleConstants::AI_CHATBOT))
                    <!-- AI Settings -->
                    <div class="tab-pane fade" id="aiSettings" role="tabpanel">
                        <form action="{{ route('settings.updateAiSettings') }}" method="POST">
                            @csrf
                            <div class="card mb-6">
                                <div class="card-header">
                                    <h5 class="card-title m-0">AI Settings <span
                                            class="badge bg-danger rounded-pill ms-auto">Beta</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-6">
                                        <!-- Chat GPT Key (Full Width) -->
                                        <div class="col-12">
                                            <label class="form-label" for="chatGptKey">Chat GPT API Key</label>
                                            <input type="text" class="form-control" id="chatGptKey"
                                                name="chat_gpt_key"
                                                value="{{ old('chat_gpt_key', $settings->chat_gpt_key) }}"
                                                maxlength="500">
                                            <small class="form-text text-muted">Enter the API key to enable Chat GPT
                                                integrations.</small>
                                        </div>

                                        <!-- Enable AI Chat Globally -->
                                        <div class="col-12">
                                            <label class="form-label">Enable AI Chat Globally</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enableAiChatGlobal"
                                                    name="enable_ai_chat_global"
                                                    {{ $settings->enable_ai_chat_global ? 'checked' : '' }}
                                                    onchange="toggleAIOptions(this)">
                                                <label class="form-check-label" for="enableAiChatGlobal">
                                                    {{ $settings->enable_ai_chat_global ? 'Enabled' : 'Disabled' }}
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">When disabled, all AI features will be
                                                turned off
                                                globally.</small>
                                        </div>

                                        <!-- Conditionally Hidden Options -->
                                        <div id="aiOptions" class="col-12"
                                            style="{{ $settings->enable_ai_chat_global ? '' : 'display: none;' }}">
                                            <div class="row g-6">
                                                <!-- Enable AI for Admin -->
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Enable AI for Admin</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="enableAiForAdmin" name="enable_ai_for_admin"
                                                            {{ $settings->enable_ai_for_admin ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="enableAiForAdmin">
                                                            {{ $settings->enable_ai_for_admin ? 'Enabled' : 'Disabled' }}
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Allow administrators to access AI
                                                        features.</small>
                                                </div>

                                                <!-- Enable AI for Employee Self Service -->
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Enable AI for Employee Self Service</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="enableAiForEmployeeSelfService"
                                                            name="enable_ai_for_employee_self_service"
                                                            {{ $settings->enable_ai_for_employee_self_service ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="enableAiForEmployeeSelfService">
                                                            {{ $settings->enable_ai_for_employee_self_service ? 'Enabled' : 'Disabled' }}
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Enable AI features for employee
                                                        self-service
                                                        in mobile app.</small>
                                                </div>

                                                <!-- Enable AI for Business Intelligence -->
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label">Enable AI for Business Intelligence</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="enableAiForBusinessIntelligence"
                                                            name="enable_ai_for_business_intelligence"
                                                            {{ $settings->enable_ai_for_business_intelligence ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="enableAiForBusinessIntelligence">
                                                            {{ $settings->enable_ai_for_business_intelligence ? 'Enabled' : 'Disabled' }}
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Enable AI for advanced business
                                                        intelligence
                                                        insights.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>

                                    @if ($settings->is_helper_text_enabled)
                                        <div class="alert alert-primary alert-dismissible mt-3" role="alert">
                                            <h6 class="text-primary mb-4 fw-bold">What are AI Settings?</h6>
                                            <p class="mb-0">
                                                AI settings allow you to enable or disable AI-powered features
                                                across <strong>{{ config('variables.templateFullName') }}.</strong> These
                                                include Chat GPT integrations for generating intelligent responses,
                                                enhancing workflows, and
                                                providing insights. You can manage access and enable AI features for
                                                specific use cases such
                                                as employee self-service, administrative tools, and business intelligence.
                                            </p>
                                            <ul class="mt-3">
                                                <li>
                                                    <strong>Enable AI for Admin:</strong> Empower administrators with AI
                                                    features to automate
                                                    repetitive tasks, generate reports, and streamline HR operations
                                                    efficiently.
                                                </li>
                                                <li>
                                                    <strong>Enable AI for Employee Self Service:</strong> AI capabilities
                                                    for employees are
                                                    accessible through mobile app, designed to cater to both field and
                                                    office employees.
                                                    Features include:
                                                    <ul>
                                                        <li>Smart chatbots for answering HR-related queries (e.g., leave
                                                            balance, payroll
                                                            details).
                                                        </li>
                                                        {{-- <li>Intelligent recommendations for training programs and skill development.</li>
                             <li>Streamlined processes for requesting leave, reimbursements, and other tasks.</li> --}}
                                                    </ul>
                                                </li>
                                                <li>
                                                    <strong>Enable AI for Business Intelligence:</strong> Use AI to generate
                                                    actionable insights
                                                    and analytics for your organization. This feature aids in
                                                    decision-making by analyzing HR
                                                    trends, employee performance, and workforce planning.
                                                </li>
                                            </ul>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <div class="alert alert-warning alert-dismissible mt-3" role="alert">
                                        <h6 class="text-warning mb-4 fw-bold">Important Note:</h6>
                                        <p class="mb-0">
                                            AI features are currently in beta and may produce unexpected results. We
                                            recommend using AI
                                            cautiously and monitoring its output for accuracy.
                                            <br><br>
                                            Currently, the AI options are only available for <strong>Admin</strong> and
                                            <strong>Business
                                                Intelligence</strong> purposes. Features for Employee Self-Service will be
                                            introduced in
                                            future updates. Admins can use AI to automate HR processes, generate intelligent
                                            reports, and
                                            enhance decision-making through data-driven insights. Business Intelligence
                                            users can leverage
                                            AI to analyze trends, identify patterns, and forecast workforce needs.
                                        </p>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                @endif

                @if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
                    @php
                        $payrollAdjustments = PayrollAdjustment::where('user_id', null)->get();
                    @endphp
                    <!-- Payroll Settings -->
                    <div class="tab-pane fade" id="payrollSettings" role="tabpanel">
                        <form action="{{ route('settings.updatePayrollSettings') }}" method="POST">
                            @csrf
                            <div class="card mb-6">
                                <div class="card-header">
                                    <h5 class="card-title m-0">Payroll Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-6">
                                        <!-- Payroll Frequency -->
                                        <div class="col-12 col-md-6">
                                            <label for="payrollFrequency" class="form-label">Payroll Frequency</label>
                                            <select id="payrollFrequency" class="form-select" name="payrollFrequency">
                                                <option value="monthly"
                                                    {{ ($settings->payroll_frequency ?? 'monthly') == 'monthly' ? 'selected' : '' }}>
                                                    Monthly
                                                </option>
                                                <option value="bi-weekly"
                                                    {{ ($settings->payroll_frequency ?? 'monthly') == 'bi-weekly' ? 'selected' : '' }}>
                                                    Bi-Weekly
                                                </option>
                                                <option value="weekly"
                                                    {{ ($settings->payroll_frequency ?? 'monthly') == 'weekly' ? 'selected' : '' }}>
                                                    Weekly
                                                </option>
                                                <option value="daily"
                                                    {{ ($settings->payroll_frequency ?? 'monthly') == 'daily' ? 'selected' : '' }}>
                                                    Daily
                                                </option>
                                            </select>
                                        </div>
                                        <!-- Payroll Start Date -->
                                        <div class="col-12 col-md-6">
                                            <label for="payrollStartDate" class="form-label">Payroll Start Date</label>
                                            <input type="number" class="form-control" id="payrollStartDate"
                                                name="payrollStartDate" min="1" max="31"
                                                value="{{ $settings->payroll_start_date ?? '1' }}">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="payrollCutoffDate" class="form-label">Payroll Cut-Off Date</label>
                                            <input type="number" class="form-control" id="payrollCutoffDate"
                                                name="payrollCutoffDate" min="1" max="31"
                                                value="{{ $settings->payroll_cutoff_date ?? '25' }}">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="autoPayrollProcessing" class="form-label">Enable Automatic Payroll
                                                Processing</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    id="autoPayrollProcessing" name="autoPayrollProcessing"
                                                    {{ $settings->auto_payroll_processing ?? false ? 'checked' : '' }}>
                                                <label class="form-check-label" for="autoPayrollProcessing">Enable</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>


                        <!-- Adjustments Info Card -->
                        <div class="card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bx bx-adjust text-muted"></i> @lang('Payroll Adjustments')</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="offcanvas"
                                    data-bs-target="#offcanvasPayrollAdjustment" id="addPayrollAdjustment">
                                    <i class="bx bx-plus"></i> @lang('Add Adjustment')
                                </button>
                            </div>
                            <div class="card-body">
                                @if ($payrollAdjustments->count() > 0)
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
                                                @foreach ($payrollAdjustments as $adjustment)
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
                                                                <!-- Edit Button -->
                                                                <a href="#"
                                                                    class="btn btn-sm btn-icon btn-warning me-2 editPayrollAdjustment"
                                                                    data-bs-toggle="offcanvas"
                                                                    data-bs-target="#offcanvasPayrollAdjustment"
                                                                    onclick="editAdjustment({{ $adjustment }})">
                                                                    <i class="bx bx-edit"></i>
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
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </form>
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
                            <div class="alert alert-primary alert-dismissible mt-5" role="alert">
                                <h6 class="text-primary mb-4 fw-bold">What is Payroll Adjustments?</h6>
                                <p class="mb-0">Payroll adjustments are additional benefits or deductions that are added
                                    to the
                                    employee's
                                    salary. You can add, edit, or delete adjustments as needed. This settings will be
                                    applied to
                                    all
                                    employees.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                    </button>
                            </div>
                        @endif
                        <!-- /Adjustments Info Card -->
                    </div>
                    <!-- /Payroll Settings -->
                @endif

            </div>
        </div>
        <!-- /Options -->
    </div>
@endsection

@if ($addonService->isAddonEnabled(ModuleConstants::PAYROLL))
    @include('payroll::partials.add_orUpdate_payroll_adjustment_global')
@endif
@section('page-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>
        $(function() {

            window.toggleAIOptions = function(checkbox) {
                const aiOptions = document.getElementById("aiOptions")
                if (checkbox.checked) {
                    aiOptions.style.display = "block"
                } else {
                    aiOptions.style.display = "none"
                }
            }

            document.getElementById("companyLogo").addEventListener("change", function(e) {
                const file = e.target.files[0]
                if (file) {
                    const reader = new FileReader()
                    reader.onload = function(e) {
                        document.getElementById("companyLogoPreview").src = e.target.result
                    }
                    reader.readAsDataURL(file)
                }
            })

            // Remove Logo Functionality
            const removeLogoButton = document.getElementById("removeLogoButton")
            if (removeLogoButton) {
                removeLogoButton.addEventListener("click", function() {
                    document.getElementById("companyLogoPreview").src = "https://placehold.co/150x150"
                    document.getElementById("companyLogo").value = ""
                })
            }

            $("#timezone").select2()
            // Get the tab parameter from the URL
            const urlParams = new URLSearchParams(window.location.search)
            const activeTab = urlParams.get("tab")

            if (activeTab) {
                // Activate the tab
                $(".nav-link").removeClass("active")
                $(".tab-pane").removeClass("show active")

                // Add active classes
                $(`[data-bs-target="#${activeTab}"]`).addClass("active")
                $(`#${activeTab}`).addClass("show active")
            } else {
                // Default to the first tab if no tab param is provided
                $(".nav-link").first().addClass("active")
                $(".tab-pane").first().addClass("show active")
            }

            $("#adjustmentCategory").on("change", function() {
                if ($(this).val() === "percentage") {
                    $("#adjustmentPercentage").parent().removeClass("d-none")
                    $("#adjustmentAmount").parent().addClass("d-none")
                } else {
                    $("#adjustmentAmount").parent().removeClass("d-none")
                    $("#adjustmentPercentage").parent().addClass("d-none")
                }
            })

            window.editAdjustment = function(adjustment) {
                $("#offcanvasPayrollAdjustmentLabel").text("Edit Payroll Adjustment")
                $("#adjustmentId").val(adjustment.id)
                $("#adjustmentName").val(adjustment.name)
                $("#adjustmentCode").val(adjustment.code)
                $("#adjustmentType").val(adjustment.type)
                $("#adjustmentAmount").val(adjustment.amount)
                $("#adjustmentPercentage").val(adjustment.percentage)



                if (adjustment.amount) {
                    $("#adjustmentCategory").val("fixed")
                    $("#adjustmentAmount").parent().removeClass("d-none")
                    $("#adjustmentPercentage").parent().addClass("d-none")
                } else {
                    $("#adjustmentCategory").val("percentage")
                    $("#adjustmentPercentage").parent().removeClass("d-none")
                    $("#adjustmentAmount").parent().addClass("d-none")
                }

                $("#adjustmentNotes").val(adjustment.notes)
                $("#adjustmentSubmitBtn").text("Update Adjustment")

            }

            $("#addPayrollAdjustment").on("click", function() {
                $("#offcanvasPayrollAdjustmentLabel").text("Add Payroll Adjustment")
                $("#adjustmentId").val("")
                $("#adjustmentName").val("")
                $("#adjustmentType").val("benefit")
                $("#adjustmentAmount").val("")
                $("#adjustmentPercentage").val("")
                $("#adjustmentCategory").val("fixed")
                $("#adjustmentAmount").parent().removeClass("d-none")
                $("#adjustmentPercentage").parent().addClass("d-none")
                $("#adjustmentNotes").val("")
                $("#adjustmentSubmitBtn").text("Add Adjustment")
            })
        })
    </script>
@endsection
