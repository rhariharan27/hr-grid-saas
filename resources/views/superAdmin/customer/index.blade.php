@php
  use App\Enums\OfflineRequestStatus;
  use App\Enums\OrderStatus;
  use App\Enums\DomainRequestStatus;use Carbon\Carbon;
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Customer Dashboard')


<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite('resources/assets/vendor/libs/jquery/jquery.js')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection
@section('content')
  <section class="section-py bg-body first-section-pt">
    <div class="container">

      <!-- Tabs Navigation -->
      <ul class="nav nav-tabs mb-4" id="customerDashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                  type="button" role="tab" aria-controls="dashboard" aria-selected="true">
            Dashboard
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="order-history-tab" data-bs-toggle="tab" data-bs-target="#orderHistory"
                  type="button" role="tab" aria-controls="orderHistory" aria-selected="false">
            Order History
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="offline-request-tab" data-bs-toggle="tab"
                  data-bs-target="#offlineRequest"
                  type="button" role="tab" aria-controls="offlineRequest" aria-selected="false">
            Offline Requests
            @if($offlineRequests->where('status', OfflineRequestStatus::PENDING)->count() > 0)
              <span
                class="ms-2 badge badge-pill bg-primary">{{ $offlineRequests->where('status',OfflineRequestStatus::PENDING)->count() }}</span>
            @endif
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="domain-requests-tab" data-bs-toggle="tab"
                  data-bs-target="#domainRequests"
                  type="button" role="tab" aria-controls="domainRequests" aria-selected="false">
            Domain Requests
            @if($domainRequests->where('status', DomainRequestStatus::PENDING)->count() > 0)
              <span
                class="ms-2 badge badge-pill bg-primary">{{ $domainRequests->where('status',DomainRequestStatus::PENDING)->count() }}</span>
            @endif
          </button>
        </li>
        {{--    <li class="nav-item" role="presentation">
              <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button"
                      role="tab" aria-controls="settings" aria-selected="false">
                Settings
              </button>
            </li>--}}
      </ul>

      <!-- Tabs Content -->
      <div class="tab-content" id="customerDashboardTabContent">
        <!-- Dashboard Tab -->
        <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
          <h4 class="mb-1">@lang('Welcome to the') {{ config('variables.templateFullName') }} 👋</h4>
          @if($domainRequest && $domainRequest->status == DomainRequestStatus::APPROVED)
            <div class="mb-3"> @lang(' You can access your application using the domain')
              <a target="_blank" href="{{ 'https://'.$domainRequest->name.'.'.env('PRIMARY_DOMAIN') }}"
                 class="text-primary">{{ 'https://'.$domainRequest->name.'.'.env('PRIMARY_DOMAIN') }}</a>.
            </div>
          @endif
          <!-- Active Plan Section -->
          @if ($activePlan)
            <div class="card mb-4 shadow-sm">
              <div class="card-body">
                <h5 class="card-title d-flex justify-content-between align-items-center">
                  <span>Your Active Plan</span>
                  <button class="btn btn-primary" data-bs-toggle="modal"
                          data-bs-target="#addUserModal"
                          data-per-user-price="{{ $activePlan->per_user_price }}">
                    @lang('Add More Users')
                  </button>
                </h5>
                <hr>
                <div class="row">
                  <!-- Plan Details -->
                  <div class="col-md-6 mt-3">
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>@lang('Plan Name'):</strong></span>
                        <span>{{ $activePlan->name }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>@lang('Base Price'):</strong></span>
                        <span>{{$settings->currency_symbol}}{{ $activePlan->base_price }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>@lang('Per User Price'):</strong></span>
                        <span>{{$settings->currency_symbol}}{{ $activePlan->per_user_price }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>@lang('Included Users'):</strong></span>
                        <span>{{ $activePlan->included_users }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>@lang('Add-On Users'):</strong></span>
                        <span>{{ $subscription->additional_users }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>@lang('Duration'):</strong></span>
                        <span>{{ $activePlan->duration }} {{ ucfirst($activePlan->duration_type->value) }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>@lang('Expiry Date'):</strong></span>
                        <span>{{ Carbon::parse(auth()->user()->plan_expired_date)->format('D, d M Y h:i a') }}</span>
                      </li>
                    </ul>
                  </div>

                  <!-- Actions -->
                  <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                    <h1 class="text-primary">{{ round(now()->diffInDays(auth()->user()->plan_expired_date)) }}
                      days</h1>
                    <h3 class="text-muted">@lang('Remaining')</h3>
                    <a href="#" class="btn btn-primary mb-2 w-100" data-bs-toggle="modal"
                       data-bs-target="#renewModal">@lang('Renew Plan')</a>
                    {{--  <a href="#" class="btn btn-secondary w-100">@lang('Upgrade Plan')</a>--}}
                  </div>
                </div>
              </div>
            </div>
          @else
            <div class="card mb-4 shadow-sm">
              <div class="card-body text-center">
                <h5 class="card-title">@lang('You Don\'t Have an Active Plan')</h5>
                <p>Check out the available plans below and subscribe to get started!</p>
              </div>
            </div>
          @endif

          <!--Offline Request Status Section -->
          @if($offlineRequests->where('status', OfflineRequestStatus::PENDING)->count() > 0)
            <div class="alert alert-info shadow-sm" role="alert">
              <h4 class="alert-heading">Offline Request Pending!</h4>
              <p>Your offline request is pending approval. You will be notified once it is approved.</p>
            </div>
          @endif

          <!-- Domain Request Section -->
          @if($activePlan)
            @if(!$domainRequest)
              <div class="alert alert-info shadow-sm" role="alert">
                <h4 class="alert-heading">Domain Request Pending!</h4>
                <p>Kindly make a domain request to proceed further. This will be your primary domain to
                  access the
                  application.</p>
                <span class="text-danger">Note only subdomains are allowed. Do not include http:// or https://</span>
                <!-- Domain Request Form -->
                <form action="{{ route('customer.requestDomain') }}" method="POST">
                  @csrf
                  <div class="mb-3">
                    <label for="domain" class="form-label">Sub Domain Name</label>
                    <input type="text" class="form-control" id="domain" name="domain" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Request Domain</button>
                </form>
              </div>
            @elseif($domainRequest->status == DomainRequestStatus::PENDING)
              <div class="alert alert-info shadow-sm" role="alert">
                <h4 class="alert-heading">Domain Request Pending Approval!</h4>
                <p>Your domain request is pending approval. You will be notified once it is
                  approved.</p>
                <p class="text-danger"><strong>Note:</strong>Domain request approval may
                  take up to 24 hours.</p>
              </div>
            @elseif(!$domainRequest->updated_at->diffInDays(now()) > 5 && $domainRequest->status == DomainRequestStatus::APPROVED)
              <div class="alert alert-success shadow-sm" role="alert">
                <h4 class="alert-heading">Domain Request Approved!</h4>
                <p>Your domain request has been approved. You can now access your application.
                </p>
              </div>
            @elseif($domainRequest->status == DomainRequestStatus::REJECTED)
              <div class="alert alert-danger shadow-sm" role="alert">
                <h4 class="alert-heading">Domain Request Rejected!</h4>
                <p>Your domain request has been rejected. Please make a new request with a different
                  domain.</p>
              </div>
            @elseif($domainRequest->status == DomainRequestStatus::PENDING)
              <div class="alert alert-info shadow-sm" role="alert">
                <h4 class="alert-heading">Domain Request Pending!</h4>
                <p>Your domain request is pending approval. You will be notified once it is
                  approved.</p>
              </div>
            @elseif($domainRequest->status == DomainRequestStatus::CANCELLED)
              <div class="alert alert-danger shadow-sm" role="alert">
                <h4 class="alert-heading">Domain Request Cancelled!</h4>
                <p>Your domain request has been cancelled. Please make a new request.</p>
                <p>This will be your primary domain to access the application.</p>
                <span class="text-danger">Note only subdomains are allowed. Do not include http:// or https://</span>
                <!-- Domain Request Form -->
                <form action="{{ route('customer.requestDomain') }}" method="POST">
                  @csrf
                  <div class="mb-3">
                    <label for="domain" class="form-label">Sub Domain Name</label>
                    <input type="text" class="form-control" id="domain" name="domain" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Request Domain</button>
                </form>
              </div>
            @endif
          @endif
          <!-- /Domain Request Section -->

          <!-- Available Plans Section -->
          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title">Available Plans</h5>
              <div class="row">
                @foreach ($availablePlans as $plan)
                  <div class="col-md-4">
                    <div class="card mb-3 position-relative border
            @if($activePlan && $activePlan->id == $plan->id) border-primary
            @elseif($activePlan && $plan->base_price > $activePlan->base_price) border-success
            @endif">
                      <!-- Badge Section -->
                      @if($activePlan && $activePlan->id == $plan->id)
                        <!-- Current Plan Badge -->
                        <span class="badge bg-primary position-absolute top-0 end-0 m-2">Current Plan</span>
                      @elseif($activePlan && $plan->base_price > $activePlan->base_price)
                        <!-- Upgrade Available Badge -->
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Upgrade Available</span>
                      @endif

                      <div class="card-body">
                        <h5 class="card-title text-center">{{ $plan->name }}</h5>
                        <hr>
                        <div class="text-center">
                          <h1 class="text-primary">
                            {{$settings->currency_symbol}}{{ $plan->base_price }}
                          </h1>
                          <span class="text-muted fs-6">
                 base price for {{ $plan->duration }} {{ ucfirst($plan->duration_type->value) }}
                  </span>
                        </div>
                        <hr class="mt-3">

                        <!-- Plan Details Section -->
                        <ul class="list-group list-group-flush">
                          <li class="d-flex justify-content-between align-items-center mt-2">
                            <span><strong>Included Users:</strong></span>
                            <span>{{ $plan->included_users }}</span>
                          </li>
                          <li class="d-flex justify-content-between align-items-center mt-2">
                            <span><strong>Additional User:</strong></span>
                            <span>{{$settings->currency_symbol}}{{ $plan->per_user_price }} / user</span>
                          </li>
                          <!-- Available Modules Section -->
                          @foreach(ModuleConstants::All_MODULES as $module)
                            <li class="d-flex justify-content-between align-items-center mt-2">
                              <span><strong>{{ $module }}</strong></span>
                              @if(collect($plan->modules)->contains($module))
                                <i class="bx bx-check text-success fs-5"></i>
                              @else
                                <i class="bx bx-block text-danger fs-5"></i>
                              @endif
                            </li>
                          @endforeach
                        </ul>


                        <p class="text-center text-muted mt-3">{{ $plan->description }}</p>

                        <!-- Buttons -->
                        @if($activePlan && $activePlan->id == $plan->id)
                          <!-- Current Active Plan -->
                          <button class="btn btn-secondary w-100" disabled>Subscribed</button>
                        @elseif($activePlan && $plan->base_price > $activePlan->base_price)
                          <!-- Upgrade Option -->
                          <button class="btn btn-warning w-100"
                                  data-bs-toggle="modal"
                                  data-bs-target="#upgradeModal"
                                  data-plan-id="{{ $plan->id }}"
                                  data-plan-name="{{ $plan->name }}"
                                  data-plan-price="{{ $plan->base_price }}"
                                  data-per-user-price="{{ $plan->per_user_price }}"
                                  data-difference="{{ $plan->base_price - $activePlan->base_price }}">
                            Upgrade Now
                          </button>
                        @elseif($activePlan && $plan->base_price <= $activePlan->base_price)
                          <!-- Downgrade or Equal Plan (disabled) -->
                          <button class="btn btn-light w-100" disabled>Plan Unavailable
                          </button>
                        @else
                          <!-- Subscribe Option -->
                          <button class="btn btn-success w-100"
                                  data-bs-toggle="modal"
                                  data-bs-target="#paymentModal"
                                  data-plan-id="{{ $plan->id }}"
                                  data-plan-name="{{ $plan->name }}"
                                  data-plan-price="{{ $plan->base_price }}"
                                  data-per-user-price="{{ $plan->per_user_price }}"
                                  data-plan-users="{{ $plan->included_users }}">
                            Subscribe
                          </button>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
          <!-- /Available Plans Section -->

        </div>

        <!-- Order History Tab -->
        <div class="tab-pane fade" id="orderHistory" role="tabpanel" aria-labelledby="order-history-tab">
          <h5>Order History</h5>
          @if ($orders->isNotEmpty())
            <table class="table order-history-table">
              <thead>
              <tr>
                <th>Order ID</th>
                <th>Type</th>
                <th>Plan</th>
                <th>Additional Users</th>
                <th>Amount</th>
                <th>Gateway</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($orders as $order)
                <tr>
                  <td>{{ $order->id }}</td>
                  <td>{{ $order->type }}</td>
                  <td>
                    <div class="d-flex">
                      <div>
                        <p> {{ $order->plan->name }}</p>
                        <p>
                          <strong>Duration:</strong> {{ $order->plan->duration }} {{ $order->plan->duration_type->value }}
                        </p>
                        <p>
                          <strong>Included Users:</strong> {{ $order->plan->included_users }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <th>{{$order->additional_users}}</th>
                  <td>{{$settings->currency_symbol}}{{ $order->amount }}</td>
                  <td>{{$order->payment_gateway}}</td>
                  <td>
                    @if($order->status == OrderStatus::PENDING)
                      <span class="badge bg-warning">Pending</span>
                    @elseif($order->status == OrderStatus::COMPLETED)
                      <span class="badge bg-success">Completed</span>
                    @elseif($order->status == OrderStatus::CANCELLED)
                      <span class="badge bg-danger">Cancelled</span>
                    @else
                      <span class="badge bg-danger">{{$order->status}}</span>
                    @endif
                  </td>
                  <td>{{ $order->created_at->format('Y-m-d') }}</td>
                  <td>
                    @if($order->status == OrderStatus::COMPLETED)
                      <button class="btn btn-link text-primary view-order"
                              data-order-id="{{ $order->id }}"
                              data-bs-toggle="modal" data-bs-target="#viewOrderModal">
                        <i class="bx bx-show"></i>
                      </button>
                    @else
                      <p>N/A</p>
                    @endif
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <p>You have no order history.</p>
          @endif
        </div>

        <!-- Offline Requests Tab -->
        <div class="tab-pane fade" id="offlineRequest" role="tabpanel" aria-labelledby="offline-request-tab">
          <h5>Offline Requests</h5>
          @if ($offlineRequests->isNotEmpty())
            <table class="table offline-request-table">
              <thead>
              <tr>
                <th>Request ID</th>
                <th>Type</th>
                <th>Plan</th>
                <th>Amount</th>
                <th>Additional Users</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($offlineRequests as $request)
                <tr>
                  <td>{{ $request->id }}</td>
                  <td>{{ ucfirst($request->type->value) }}</td>
                  <td>
                    <div class="d-flex">
                      <div>
                        <p> {{ $request->plan->name }}</p>
                        <p>
                          <strong>Duration:</strong> {{ $request->plan->duration }} {{ $request->plan->duration_type->value }}
                        </p>
                        <p>
                          <strong>Included
                            Users:</strong> {{ $request->plan->included_users }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td> {{$settings->currency_symbol. $request->plan->base_price }}</td>
                  <td>{{ $request->additional_users }}</td>
                  <td> {{$settings->currency_symbol. $request->total_amount }}</td>
                  <td>
                    @if($request->status == OfflineRequestStatus::PENDING)
                      <span class="badge bg-warning">Pending</span>
                    @elseif($request->status == OfflineRequestStatus::APPROVED)
                      <span class="badge bg-success">Approved</span>
                    @elseif($request->status == OfflineRequestStatus::REJECTED)
                      <span class="badge bg-danger">Rejected</span>
                    @elseif($request->status == OfflineRequestStatus::CANCELLED)
                      <span class="badge bg-danger">Cancelled</span>
                    @endif

                  </td>
                  <td>{{ $request->created_at->format('Y-m-d') }}</td>
                  <td>
                    @if ($request->status == OfflineRequestStatus::PENDING)
                      <a class="btn btn-danger btn-sm cancel-request"
                         onclick="return confirm('Are you sure?')"
                         href="{{ route('offlinePayment.cancelOfflineRequest', $request->id) }}">Cancel
                      </a>
                    @endif
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <p>You have no offline requests.</p>
          @endif
        </div>

        <!-- Domain Requests Tab -->
        <div class="tab-pane fade" id="domainRequests" role="tabpanel" aria-labelledby="domain-requests-tab">
          <h5>Domain Requests</h5>
          @if ($domainRequests->isNotEmpty())
            <table class="table domain-request-table">
              <thead>
              <tr>
                <th>Request ID</th>
                <th>Domain</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($domainRequests as $request)
                <tr>
                  <td>{{ $request->id }}</td>
                  <td>{{ $request->name }}</td>
                  <td>
                    @if($request->status == DomainRequestStatus::PENDING)
                      <span class="badge bg-warning">Pending</span>
                    @elseif($request->status == DomainRequestStatus::APPROVED)
                      <span class="badge bg-success">Approved</span>
                    @elseif($request->status == DomainRequestStatus::REJECTED)
                      <span class="badge bg-danger">Rejected</span>
                    @elseif($request->status == DomainRequestStatus::CANCELLED)
                      <span class="badge bg-danger">Cancelled</span>
                    @endif
                  </td>
                  <td>{{ $request->created_at->format('Y-m-d') }}</td>
                  <td>
                    @if ($request->status == DomainRequestStatus::PENDING)
                      <a class="btn btn-danger btn-sm cancel-request"
                         onclick="return confirm('Are you sure?')"
                         href="{{ route('customer.cancelDomainRequest', $request->id) }}">Cancel
                      </a>
                    @endif
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <p>You have no domain requests.</p>
          @endif
        </div>

        {{--      <!-- Settings Tab -->
              <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                <div class="row">
                  <!-- Left Side Vertical Tabs -->
                  <div class="col-md-3">
                    <div class="nav flex-column nav-pills me-3" id="settingsTabs" role="tablist" aria-orientation="vertical">
                      <button class="nav-link active" id="basic-info-tab" data-bs-toggle="pill" data-bs-target="#basicInfo"
                              type="button" role="tab" aria-controls="basicInfo" aria-selected="false">
                        Basic Information
                      </button>
                      <button class="nav-link" id="notification-tab" data-bs-toggle="pill"
                              data-bs-target="#notificationSettings" type="button" role="tab"
                              aria-controls="notificationSettings" aria-selected="true">
                        Notification Settings
                      </button>
                      <button class="nav-link" id="business-info-tab" data-bs-toggle="pill" data-bs-target="#businessInfo"
                              type="button" role="tab" aria-controls="businessInfo" aria-selected="false">
                        Business Information
                      </button>
                    </div>
                  </div>

                  <!-- Right Side Tab Content -->
                  <div class="col-md-9">
                    <div class="tab-content" id="settingsTabsContent">
                      <!-- Basic Information -->
                      <div class="tab-pane fade show active" id="basicInfo" role="tabpanel" aria-labelledby="basic-info-tab">
                        <h5>Basic Information</h5>
                        <form action="{{ route('customer.updateBasicInfo') }}" method="POST">
                          @csrf
                          <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="{{ auth()->user()->first_name }}" required>
                          </div>
                          <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="{{ auth()->user()->email }}" required>
                          </div>
                          <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="{{ auth()->user()->phone }}" required>
                          </div>
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                      </div>

                      <!-- Notification Settings -->
                      <div class="tab-pane fade" id="notificationSettings" role="tabpanel" aria-labelledby="notification-tab">
                        <h5>Notification Settings</h5>
                        <form action="{{ route('customer.updateNotificationSettings') }}" method="POST">
                          @csrf
                          <div class="mb-3">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="emailNotifications"
                                     name="email_notifications"
                                {{ $notificationSettings['email_notifications'] ? 'checked' : '' }}>
                              <label class="form-check-label" for="emailNotifications">
                                Email Notifications
                              </label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="smsNotifications" name="sms_notifications"
                                {{ $notificationSettings['sms_notifications'] ? 'checked' : '' }}>
                              <label class="form-check-label" for="smsNotifications">
                                SMS Notifications
                              </label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="pushNotifications" name="push_notifications"
                                {{ $notificationSettings['push_notifications'] ? 'checked' : '' }}>
                              <label class="form-check-label" for="pushNotifications">
                                Push Notifications
                              </label>
                            </div>
                          </div>
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                      </div>

                      <!-- Business Information -->
                      <div class="tab-pane fade" id="businessInfo" role="tabpanel" aria-labelledby="business-info-tab">
                        <h5>Business Information</h5>
                        <form action="{{ route('customer.updateBusinessInfo') }}" method="POST">
                          @csrf
                          <div class="mb-3">
                            <label for="businessName" class="form-label">Business Name</label>
                            <input type="text" class="form-control" id="businessName" name="business_name" value="" required>
                          </div>
                          <div class="mb-3">
                            <label for="taxNumber" class="form-label">Tax Number</label>
                            <input type="text" class="form-control" id="taxNumber" name="tax_number" value="">
                          </div>
                          <div class="mb-3">
                            <label for="businessEmail" class="form-label">Business Email</label>
                            <input type="email" class="form-control" id="businessEmail" name="business_email" value=""
                                   required>
                          </div>
                          <div class="mb-3">
                            <label for="businessPhone" class="form-label">Business Phone</label>
                            <input type="text" class="form-control" id="businessPhone" name="business_phone" value=""
                                   required>
                          </div>
                          <div class="mb-3">
                            <label for="businessWebsite" class="form-label">Business Website</label>
                            <input type="text" class="form-control" id="businessWebsite" name="business_website" value="">
                          </div>
                          <div class="mb-3">
                            <label for="businessType" class="form-label">Business Type</label>
                            <select class="form-select" id="businessType" name="business_type" required>
                              <option value="">Select Business Type</option>
                              <option value="agriculture">Agriculture</option>
                              <option value="automotive">Automotive</option>
                              <option value="construction">Construction</option>
                              <option value="consumer_goods">Consumer Goods</option>
                              <option value="education">Education</option>
                              <option value="energy">Energy</option>
                              <option value="financial_services">Financial Services</option>
                              <option value="food_and_beverage">Food & Beverage</option>
                              <option value="healthcare">Healthcare</option>
                              <option value="hospitality">Hospitality</option>
                              <option value="information_technology">Information Technology</option>
                              <option value="legal">Legal</option>
                              <option value="logistics_and_transportation">Logistics & Transportation</option>
                              <option value="manufacturing">Manufacturing</option>
                              <option value="media_and_entertainment">Media & Entertainment</option>
                              <option value="nonprofit">Nonprofit</option>
                              <option value="pharmaceuticals">Pharmaceuticals</option>
                              <option value="real_estate">Real Estate</option>
                              <option value="retail">Retail</option>
                              <option value="telecommunications">Telecommunications</option>
                              <option value="travel_and_tourism">Travel & Tourism</option>
                              <option value="wholesale_and_distribution">Wholesale & Distribution</option>
                              <option value="other">Other</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label for="businessAddress" class="form-label">Business Address</label>
                            <textarea class="form-control" id="businessAddress" name="business_address" rows="3"
                                      required></textarea>
                          </div>
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- /Settings Tab -->--}}

      </div>
    </div>
  </section>

  @if($activePlan)
    @include('_partials._customerModals.add_more_users_model')
    @include('_partials._customerModals.plan_upgrade_model')
    @include('_partials._customerModals.renew_plan_model')
  @else
    @include('_partials._customerModals.initial_payment_model')
  @endif



  <!-- View Order Modal -->
  <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewOrderModalLabel">Order Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="orderDetailsContent">
            <!-- Order details will be dynamically populated here -->
            <div class="text-center">
              <p>Loading order details...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /View Order Modal -->

@endsection

@section('page-script')
  @if($settings->razorpay_enabled)
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  @endif
  @if($settings->paypal_enabled)
    <script src="https://www.paypal.com/sdk/js?client-id={{ $settings->paypal_client_id }}"></script>
  @endif

  <!-- Initial payment model script -->
  @if(!$activePlan)
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const usersInput = document.getElementById("paymentModal-users")
        const decreaseButton = document.getElementById("paymentModal-decreaseUsers")
        const increaseButton = document.getElementById("paymentModal-increaseUsers")
        const totalPriceElement = document.getElementById("paymentModal-totalPrice")
        const planPriceElement = document.getElementById("paymentModal-modalPlanPrice")
        const perUserPriceElement = document.getElementById("paymentModal-modalPlanPerUserPrice")
        const additionalUsersInput = document.getElementById("paymentModal-additionalUsers")
        const totalCostDisplay = document.getElementById("paymentModal-totalCost")

        if (additionalUsersInput) {
          additionalUsersInput.addEventListener("input", () => {
            const additionalUsers = parseInt(additionalUsersInput.value) || 0
            const perUserPrice = {{ $activePlan ? $activePlan->per_user_price : 0 }};
            const totalCost = additionalUsers * perUserPrice

            totalCostDisplay.textContent = totalCost.toFixed(2)
          })
        }

        const calculateTotalPrice = () => {
          const planPrice = parseFloat(planPriceElement.textContent)
          const perUserPrice = parseFloat(perUserPriceElement.textContent)
          const users = parseInt(usersInput.value)
          const totalPrice = planPrice + (perUserPrice * users)
          totalPriceElement.textContent = totalPrice.toFixed(2)
        }

        decreaseButton.addEventListener("click", () => {
          let currentValue = parseInt(usersInput.value)
          if (currentValue > 0) {
            usersInput.value = currentValue - 1
            calculateTotalPrice()
          }
        })

        increaseButton.addEventListener("click", () => {
          let currentValue = parseInt(usersInput.value)
          usersInput.value = currentValue + 1
          calculateTotalPrice()
        })

        if (usersInput) {
          usersInput.addEventListener("input", () => {
            calculateTotalPrice()
          })
        }

        // Populate Payment Modal with Plan Details
        const paymentModal = document.getElementById("paymentModal")
        if (paymentModal) {
          paymentModal.addEventListener("show.bs.modal", function(event) {
            const button = event.relatedTarget
            const planId = button.getAttribute("data-plan-id")
            const planName = button.getAttribute("data-plan-name")
            const planPrice = button.getAttribute("data-plan-price")
            const planUsers = button.getAttribute("data-plan-users")
            const perUserPrice = button.getAttribute("data-per-user-price")

            document.getElementById("paymentModal-planId").value = planId
            document.getElementById("paymentModal-modalPlanName").textContent = planName
            document.getElementById("paymentModal-modalPlanPrice").textContent = planPrice
            document.getElementById("paymentModal-modalPlanPerUserPrice").textContent = perUserPrice
            document.getElementById("paymentModal-modalPlanUsers").textContent = planUsers

            calculateTotalPrice()

            paymentModal.addEventListener("hidden.bs.modal", function() {
              usersInput.value = 0
              totalCostDisplay.textContent = "0.00"
            })
          })
        }

        // Razorpay Payment Integration
        window.startRazorpayPayment = function(paymentUrl) {
          const planId = document.getElementById("paymentModal-planId").value
          console.log("Payment URL:", paymentUrl)
          console.log("Plan ID:", planId)
          fetch(paymentUrl, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              currency: '{{ $settings->currency }}',
              plan_id: planId,
              users: document.getElementById("paymentModal-users").value
            })
          })
            .then(response => response.json())
            .then(data => {
              console.log("Data:", data)
              if (data.success) {
                const options = {
                  key: data.key,
                  amount: data.amount,
                  currency: data.currency,
                  name: data.name,
                  description: data.description,
                  order_id: data.order_id,
                  handler: function(response) {
                    window.location.href = `{{ url('razorpay/transaction/callback') }}/${response.razorpay_payment_id}/${data.local_order_id}`
                  },
                  prefill: {
                    name: data.prefill.name,
                    email: data.prefill.email,
                    contact: data.prefill.contact
                  },
                  theme: {
                    color: "#3399cc"
                  }
                }
                const razorpay = new Razorpay(options)
                razorpay.open()
              } else {
                showErrorToast("Error creating Razorpay order.")
              }
            })
            .catch(error => {
              console.log("Error:", error)
              showErrorToast("An error occurred while processing payment.")
            })
        }

        //Paypal Payment Integration
        window.startPaypalPayment = function(paymentUrl) {
          console.log("Payment URL:", paymentUrl)
          const form = document.getElementById("paymentForm")
          form.action = paymentUrl
          form.submit()
        }
      })
    </script>
  @else
    <!-- Add More Users Model Script -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const decreaseButton = document.getElementById("decreaseUsers")
        const increaseButton = document.getElementById("increaseUsers")
        const usersInput = document.getElementById("addUserModal-users")
        const totalCostDisplay = document.getElementById("addUserModal-totalCost")
        var perUserPrice = 0

        function calculateForAdditionalUsers() {
          const additionalUsers = parseInt(usersInput.value) || 0
          getPricePerUser(additionalUsers)
          const totalCost = additionalUsers * perUserPrice

          totalCostDisplay.textContent = totalCost.toFixed(2)
        }

        decreaseButton.addEventListener("click", () => {
          let currentValue = parseInt(usersInput.value)
          if (currentValue > 0) {
            usersInput.value = currentValue - 1
            calculateForAdditionalUsers()
          }
        })

        increaseButton.addEventListener("click", () => {
          let currentValue = parseInt(usersInput.value)
          usersInput.value = currentValue + 1
          calculateForAdditionalUsers()
        })

        //Add more users model
        const addUserModal = document.getElementById("addUserModal")

        if (addUserModal) {
          addUserModal.addEventListener("show.bs.modal", function(event) {
            const button = event.relatedTarget
            perUserPrice = button.getAttribute("data-per-user-price")
            const additionalUsers = $("#addUserModal-users").val()
            console.log("Per User Price:", perUserPrice)
            console.log("Additional Users:", additionalUsers)
            getPricePerUser(additionalUsers)

            document.getElementById("addUserModal-totalCost").textContent = additionalUsers * perUserPrice
          })
        }

        function getPricePerUser(usersCount) {
          fetch("{{ url('customer/getAddUserTotalAmountAjax') }}", {
            method: "post",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": '{{csrf_token()}}'
            },
            body: JSON.stringify({
              users: usersCount
            })
          })
            .then(response => response.json())
            .then(data => {
              console.log("Data:", data)
              if (data.status === "success") {
                $("#addUserModal-amountToBePaid").text(data.data)
              } else {
                showErrorToast("Error fetching price per user.")
              }
            })
            .catch(error => {
              console.error("Error:", error)
              showErrorToast("An error occurred while fetching price per user.")
            })
        }

        // Razorpay Payment Integration
        window.startRazorpayPaymentForUserAdd = function(paymentUrl) {
          var users = document.getElementById("addUserModal-users").value
          console.log("Payment URL:", paymentUrl)
          console.log("Users:", users)
          fetch(paymentUrl, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              currency: '{{ $settings->currency }}',
              users: users
            })
          }).then(response => {
            console.log("Response:", response)
            return response.json()
          })
            .then(data => {
              console.log("Data:", data)
              if (data.success) {
                const options = {
                  key: data.key,
                  amount: data.amount,
                  currency: data.currency,
                  name: data.name,
                  description: data.description,
                  order_id: data.order_id,
                  handler: function(response) {
                    window.location.href = `{{ url('razorpay/transaction/callback') }}/${response.razorpay_payment_id}/${data.local_order_id}`
                  },
                  prefill: {
                    name: data.prefill.name,
                    email: data.prefill.email,
                    contact: data.prefill.contact
                  },
                  theme: {
                    color: "#3399cc"
                  }
                }
                const razorpay = new Razorpay(options)
                razorpay.open()
              } else {
                showErrorToast("Error creating Razorpay order.")
              }
            })
            .catch(error => {
              console.log("Error here:", error)
              showErrorToast("An error occurred while processing payment.")
            })
        }
      })
    </script>
    <!-- /Add More Users Model Script -->

    <!-- Renewal Model Script -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {

        const renewModal = document.getElementById("renewModal")
        renewModal.addEventListener("show.bs.modal", function(event) {
        })

        window.startRazorpayPaymentForRenewal = function(paymentUrl) {
          console.log("Payment URL:", paymentUrl)
          fetch(paymentUrl, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              currency: '{{ $settings->currency }}'
            })
          }).then(response => {
            console.log("Response:", response)
            return response.json()
          })
            .then(data => {
              console.log("Data:", data)
              if (data.success) {
                const options = {
                  key: data.key,
                  amount: data.amount,
                  currency: data.currency,
                  name: data.name,
                  description: data.description,
                  order_id: data.order_id,
                  handler: function(response) {
                    window.location.href = `{{ url('razorpay/transaction/callback') }}/${response.razorpay_payment_id}/${data.local_order_id}`
                  },
                  prefill: {
                    name: data.prefill.name,
                    email: data.prefill.email,
                    contact: data.prefill.contact
                  },
                  theme: {
                    color: "#3399cc"
                  }
                }
                const razorpay = new Razorpay(options)
                razorpay.open()
              } else {
                showErrorToast("Error creating Razorpay order.")
              }
            })
            .catch(error => {
              console.log("Error here:", error)
              showErrorToast("An error occurred while processing payment.")
            })
        }
      })
    </script>

    <!-- Upgrade Model Script -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        // Populate Upgrade Modal with Plan Details
        const upgradeModal = document.getElementById("upgradeModal")
        upgradeModal.addEventListener("show.bs.modal", function(event) {
          const button = event.relatedTarget
          const planId = button.getAttribute("data-plan-id")
          const planName = button.getAttribute("data-plan-name")
          const upgradePlanId = document.getElementById("upgradeModal-planId")

          // Clear previous values in the modal
          document.getElementById("upgradeModalPlanName").textContent = planName
          document.getElementById("upgradeModalDifference").textContent = "Loading..."

          // Fetch price difference from the server
          $.get("{{ url('customer/getBalancePriceForUpgradeAjax/') }}", { planId })
            .done(function(data) {
              if (data.status === "success") {
                document.getElementById("upgradeModalDifference").textContent = `${data.data}`
                upgradePlanId.value = planId
              } else {
                document.getElementById("upgradeModalDifference").textContent = "Error fetching price difference."
              }
            })
            .fail(function() {
              document.getElementById("upgradeModalDifference").textContent = "Unable to fetch data."
            })
        })

        // Razorpay Payment Integration
        window.startRazorpayPaymentForUpgrade = function(paymentUrl) {
          const planId = document.getElementById("upgradeModal-planId").value
          console.log("Payment URL:", paymentUrl)
          console.log("Plan ID:", planId)
          fetch(paymentUrl, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              currency: '{{ $settings->currency }}',
              plan_id: planId
            })
          })
            .then(response => response.json())
            .then(data => {
              console.log("Data:", data)
              if (data.success) {
                const options = {
                  key: data.key,
                  amount: data.amount,
                  currency: data.currency,
                  name: data.name,
                  description: data.description,
                  order_id: data.order_id,
                  handler: function(response) {
                    window.location.href = `{{ url('razorpay/transaction/callback') }}/${response.razorpay_payment_id}/${data.local_order_id}`
                  },
                  prefill: {
                    name: data.prefill.name,
                    email: data.prefill.email,
                    contact: data.prefill.contact
                  },
                  theme: {
                    color: "#3399cc"
                  }
                }
                const razorpay = new Razorpay(options)
                razorpay.open()
              } else {
                showErrorToast("Error creating Razorpay order.")
              }
            })
            .catch(error => {
              console.log("Error:", error)
              showErrorToast("An error occurred while processing payment.")
            })
        }
      })
    </script>
    <!-- /Upgrade Model Script -->
  @endif

  <!-- Common Script -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {

      // DataTables Initialization
      $(".offline-request-table").dataTable({
        responsive: true,
        autoWidth: false,
        order: [[0, "desc"]],
        columnDefs: [
          { orderable: false, targets: [5] }
        ]
      })

      $(".order-history-table").dataTable({
        responsive: true,
        autoWidth: false,
        order: [[0, "desc"]],
        columnDefs: [
          { orderable: false, targets: [4] }
        ]
      })

      $(".domain-request-table").dataTable({
        responsive: true,
        autoWidth: false,
        order: [[0, "desc"]],
        columnDefs: [
          { orderable: false, targets: [4] }
        ]
      })

      //Select2 Initialization
      $("#businessType").select2({
        placeholder: "Select Business Type",
        allowClear: true
      })

      // Fetch and display order details in the modal
      document.querySelectorAll(".view-order").forEach(button => {
        button.addEventListener("click", function() {
          const orderId = this.getAttribute("data-order-id")
          const orderDetailsContent = document.getElementById("orderDetailsContent")
          const currencySymbol = '{{$settings->currency_symbol}}'

          // Clear existing content
          orderDetailsContent.innerHTML = "<div class=\"text-center\"><p>Loading order details...</p></div>"

          // Fetch order details
          fetch(`{{ url('customer/getOrderDetailsAjax') }}/${orderId}`)
            .then(response => response.json())
            .then(data => {
              console.log("Order Details:", data)
              if (data.status === "success") {
                const order = data.data
                orderDetailsContent.innerHTML = `
              <div class="p-4">
                <!-- Header Section -->
                <div class="text-center border-bottom pb-3 mb-4">
                  <h4 class="mb-0 text-primary">Invoice</h4>
                  <p class="text-muted mb-0">Order ID: ${order.id}</p>
                  <small class="text-muted">${order.created_at}</small>
                </div>

                <!-- Order Details Section -->
                <div class="mb-4">
                  <h5 class="text-secondary">Order Details</h5>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Plan Name:</strong></span>
                      <span>${order.plan.name}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Order Type:</strong></span>
                      <span>${order.type}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Base Price:</strong></span>
                      <span>${order.type}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Additional Users:</strong></span>
                      <span>${order.additional_users}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Total Amount:</strong></span>
                      <span>${currencySymbol}${order.total_amount}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Payment Gateway:</strong></span>
                      <span>${order.payment_gateway}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Status:</strong></span>
                      <span>${order.status}</span>
                    </li>
                  </ul>
                </div>

                <!-- Plan Details Section -->
                <div class="mb-4">
                  <h5 class="text-secondary">Plan Details</h5>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Included Users:</strong></span>
                      <span>${order.plan.included_users}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                      <span><strong>Duration:</strong></span>
                      <span>${order.plan.duration} ${order.plan.duration_type}</span>
                    </li>
                  </ul>
                </div>

                <!-- Footer Section -->
                <div class="text-center pt-3 border-top">
                  <button class="btn btn-primary btn-sm px-4" onclick="window.print()">Print</button>
                  <button class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            `
              } else {
                orderDetailsContent.innerHTML = `
              <div class="text-center text-danger">
                <i class="bx bx-error-circle fs-1"></i>
                <p class="mt-2">Failed to load order details.</p>
              </div>`
              }
            })
            .catch(error => {
              console.error("Error fetching order details:", error)
              orderDetailsContent.innerHTML = "<p class=\"text-danger\">An error occurred while fetching order details.</p>"
            })
        })
      })
    })
  </script>

  @if($gateways['stripe'])
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    @if(!$activePlan)
      <!--Initial Payment -->
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          // Stripe Payment Integration
          window.startStripePayment = function(paymentUrl) {
            const usersInput = document.getElementById("paymentModal-users")
            const planId = document.getElementById("paymentModal-planId").value
            const additionalUsers = parseInt(usersInput.value) || 0

            fetch(paymentUrl, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                currency: '{{ $settings->currency }}',
                plan_id: planId,
                users: additionalUsers
              })
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // data.sessionId, data.stripePublishableKey for Stripe Checkout
                  const stripe = Stripe(data.stripePublishableKey)
                  stripe.redirectToCheckout({ sessionId: data.sessionId }).then((result) => {
                    if (result.error) {
                      console.log("Stripe Error: " + result.error.message)
                    }
                  })
                } else {
                  console.log("Error creating Stripe checkout session.")
                }
              })
              .catch(error => {
                console.log("Stripe Payment Error:", error)
                console.log("An error occurred while processing Stripe payment.")
              })
          }
        })
      </script>
    @else

      <!-- Add More Users -->
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          // Stripe Payment Integration
          window.startStripePaymentForUserAdd = function(paymentUrl) {
            var users = document.getElementById("addUserModal-users").value
            console.log("Payment URL:", paymentUrl)
            console.log("Users:", users)
            fetch(paymentUrl, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                currency: '{{ $settings->currency }}',
                users: users
              })
            }).then(response => {
              console.log("Response:", response)
              return response.json()
            })
              .then(data => {
                console.log("Data:", data)
                if (data.success) {
                  var stripe = Stripe(data.stripePublishableKey)
                  stripe.redirectToCheckout({
                    sessionId: data.sessionId
                  }).then(function(result) {
                    console.log("Result:", result)
                    if (result.error) {
                      showErrorToast("An error occurred while processing payment.")
                    }
                  })
                } else {
                  showErrorToast("Error creating Stripe session.")
                }
              })
              .catch(error => {
                console.log("Error here:", error)
                showErrorToast("An error occurred while processing payment.")
              })
          }
        })
      </script>

      <!-- Upgrade Plan -->
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          // Stripe Payment Integration
          window.startStripePaymentForUpgrade = function(paymentUrl) {
            const planId = document.getElementById("upgradeModal-planId").value
            console.log("Payment URL:", paymentUrl)
            console.log("Plan ID:", planId)
            fetch(paymentUrl, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                currency: '{{ $settings->currency }}',
                plan_id: planId
              })
            })
              .then(response => response.json())
              .then(data => {
                console.log("Data:", data)
                if (data.success) {
                  const stripe = Stripe(data.stripePublishableKey)
                  stripe.redirectToCheckout({ sessionId: data.sessionId }).then((result) => {
                    if (result.error) {
                      console.log("Stripe Error: " + result.error.message)
                    }
                  })
                } else {
                  console.log("Error creating Stripe checkout session.")
                }
              })
              .catch(error => {
                console.log("Stripe Payment Error:", error)
                console.log("An error occurred while processing Stripe payment.")
              })
          }
        })
      </script>

      <!-- Renew Plan -->
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          // Stripe Payment Integration
          window.startStripePaymentForRenewal = function(paymentUrl) {
            console.log("Payment URL:", paymentUrl)
            fetch(paymentUrl, {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                currency: '{{ $settings->currency }}'
              })
            })
              .then(response => response.json())
              .then(data => {
                console.log("Data:", data)
                if (data.success) {
                  const stripe = Stripe(data.stripePublishableKey)
                  stripe.redirectToCheckout({ sessionId: data.sessionId }).then((result) => {
                    if (result.error) {
                      console.log("Stripe Error: " + result.error.message)
                    }
                  })
                } else {
                  console.log("Error creating Stripe checkout session.")
                }
              })
              .catch(error => {
                console.log("Stripe Payment Error:", error)
                console.log("An error occurred while processing Stripe payment.")
              })
          }
        })
      </script>
    @endif
  @endif

@endsection

