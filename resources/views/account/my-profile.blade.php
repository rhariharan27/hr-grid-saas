@php use Carbon\Carbon; @endphp
@extends('layouts/layoutMaster')

@section('title', 'User Profile')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss'
  ])
@endsection

<!-- Page Styles -->
@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-profile.scss'])
  <style>
    .profile-picture-container {
      position: relative;
      width: 120px;
      height: 120px;
    }

    .profile-picture-container:hover .profile-overlay {
      display: flex;
      background: rgba(0, 0, 0, 0.5);
      border-radius: 50%;
      color: #fff;
      cursor: pointer;
    }

    .profile-overlay {
      display: none;
    }
  </style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite(['resources/assets/js/app-user-view-account.js'])
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const profilePictureInput = document.getElementById('file');
      const changeProfilePictureButton = document.getElementById('changeProfilePictureButton');

      changeProfilePictureButton.addEventListener('click', function() {
        profilePictureInput.click();
      });

      profilePictureInput.addEventListener('change', function() {
        console.log('Profile Picture Changed');
        if (profilePictureInput.files.length > 0) {
          document.getElementById('profilePictureForm').submit();
        }
      });
    });
  </script>
@endsection

@section('content')
  <!-- Header -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="user-profile-header-banner">
          <img src="{{ asset('assets/img/pages/profile-banner.png') }}" alt="Banner image" class="rounded-top">
        </div>
        <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-8">
          <div class="flex-shrink-0 mt-1 mx-sm-0 mx-auto">
            <div class="user-avatar-section text-center position-relative">
              <!-- Profile Picture with Rounded Background -->
              <div class="profile-picture-container position-relative d-inline-block"
                   style="width: 150px; height: 150px;">
                <!-- Rounded Background -->
                <div class="rounded-circle bg-label-primary position-absolute top-50 start-50 translate-middle"
                     style="width: 120px; height: 120px;">
                </div>

                <!-- Profile Image -->
                @if($user->profile_picture)
                  <img class="img-fluid rounded-circle position-absolute top-50 start-50 translate-middle"
                       src="{{$user->getProfilePicture()}}"
                       height="120" width="120" alt="User avatar" id="userProfilePicture" />
                @else
                  <h2
                    class="text-white position-absolute top-50 start-50 translate-middle">{{$user->getInitials()}}</h2>
                @endif
                <!-- Overlay on Hover -->
                <div
                  class="profile-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-end justify-content-center"
                  style="display: none;">
                  <button class="btn btn-outline-light" id="changeProfilePictureButton">
                    <i class="bx bx-camera"></i> Change
                  </button>
                </div>
              </div>
            </div>
            <!-- Hidden File Input for Profile Picture Upload -->
            <form id="profilePictureForm" action="{{route('account.changeProfilePicture')}}" method="POST"
                  enctype="multipart/form-data" style="display: none;">
              @csrf
              <input type="hidden" name="userId" id="userId" value="{{ auth()->user()->id }}">
              <input type="file" id="file" name="file" accept="image/*">
            </form>
          </div>
          <div class="flex-grow-1 mt-3 mt-lg-5">
            <div
              class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
              <div class="user-profile-info">
                <h4 class="mb-2 mt-lg-7">{{ $user['first_name'] }} {{ $user['last_name'] }}</h4>
                <ul
                  class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 mt-4">
                  <li class="list-inline-item">
                    <i class='bx bx-envelope me-2 align-top'></i><span class="fw-medium">{{ $user['email'] }}</span>
                  </li>
                  <li class="list-inline-item">
                    <i class='bx bx-calendar me-2 align-top'></i><span
                      class="fw-medium">Joined: {{ Carbon::parse($user['created_at'])->format('M d, Y') }}</span>
                  </li>
                  <li class="list-inline-item">
                    <i class='bx bx-flag me-2 align-top'></i><span
                      class="fw-medium">Status: {{ $user['status'] }}</span>
                  </li>
                </ul>
              </div>
              <a href="javascript:void(0)" class="btn btn-primary mb-1">
                <i class='bx bx-user-check bx-sm me-2'></i>Connected
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Header -->

  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-sm-row mb-6">
          <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i
                class='bx bx-user bx-sm me-1_5'></i> Profile</a></li>
          {{--  <li class="nav-item"><a class="nav-link" href="{{ url('pages/profile-teams') }}"><i
                  class='bx bx-group bx-sm me-1_5'></i> Teams</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('pages/profile-projects') }}"><i
                  class='bx bx-grid-alt bx-sm me-1_5'></i> Projects</a></li>--}}
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <!-- User Profile Content -->
  <div class="row">
    <div class="col-xl-4 col-lg-5 col-md-5">
      <!-- About User -->
      <div class="card mb-6">
        <div class="card-body">
          <small class="card-text text-uppercase text-muted small">About</small>
          <ul class="list-unstyled my-3 py-1">
            <li class="d-flex align-items-center mb-4"><i class="bx bx-user"></i><span
                class="fw-medium mx-2">Full Name:</span> <span>{{ $user['first_name'] }} {{ $user['last_name'] }}</span>
            </li>
            <!-- Role -->
            <li>
              <div class="d-flex align-items-center mb-4">
                <i class="bx bx-user-pin"></i>
                <span class="fw-medium mx-2">Role:</span>
                <span>{{ $role->name }}</span>
              </div>
            </li>
            <li class="d-flex align-items-center mb-4"><i class="bx bx-check"></i><span
                class="fw-medium mx-2">Status:</span> <span>{{ $user['status'] }}</span></li>
            <li class="d-flex align-items-center mb-2"><i class="bx bx-detail"></i><span class="fw-medium mx-2">Languages:</span>
              <span>{{ strtoupper($user['language']) }}</span></li>
          </ul>
          <small class="card-text text-uppercase text-muted small">Contacts</small>
          <ul class="list-unstyled my-3 py-1">
            <li class="d-flex align-items-center mb-4"><i class="bx bx-phone"></i><span
                class="fw-medium mx-2">Contact:</span> <span>{{ $user['phone'] }}</span></li>
            <li class="d-flex align-items-center mb-4"><i class="bx bx-envelope"></i><span
                class="fw-medium mx-2">Email:</span> <span>{{ $user['email'] }}</span></li>
          </ul>
        </div>
      </div>
      <!--/ About User -->
    </div>
    <div class="col-xl-8 col-lg-7 col-md-7">
      <!-- Activity Timeline -->
      <div class="card card-action mb-6">
        <div class="card-header align-items-center">
          <h5 class="card-action-title mb-0"><i class='bx bx-bar-chart-alt-2 bx-lg text-body me-4'></i>Activity Timeline
          </h5>
        </div>
        <div class="card-body pt-3">
          <ul class="timeline mb-0">
            @foreach ($auditLogs as $log)
              <li class="timeline-item timeline-item-transparent">
                <span class="timeline-point timeline-point-primary"></span>
                <div class="timeline-event">
                  <div class="timeline-header mb-3">
                    <h6 class="mb-0">Event: {{ ucfirst($log['event']) }}</h6>
                    <small class="text-muted">{{ Carbon::parse($log['created_at'])->diffForHumans() }}</small>
                  </div>
                  <p class="mb-2">
                    URL: {{ $log['url'] }}<br>
                    IP Address: {{ $log['ip_address'] }}<br>
                    User Agent: {{ $log['user_agent'] }}
                  </p>
                  @if($log['event'] === 'updated')
                    <p><strong>Old Values:</strong> {{ json_encode($log['old_values']) }}</p>
                    <p><strong>New Values:</strong> {{ json_encode($log['new_values']) }}</p>
                  @endif
                </div>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
      <!--/ Activity Timeline -->
    </div>
  </div>
  <!--/ User Profile Content -->
@endsection
