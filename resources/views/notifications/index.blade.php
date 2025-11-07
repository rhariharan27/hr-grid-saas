@extends('layouts/layoutMaster')

@section('title', __('Notifications'))

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

@section('page-script')
  @vite(['resources/js/main-datatable.js'])
  @vite(['resources/js/main-helper.js'])
  @vite(['resources/assets/js/app/notification-index.js'])
@endsection


@section('content')
  <div class="row">
    <div class="col">
      <h4>@lang('Notifications')</h4>
    </div>
    <div class="col text-end">
      {{--     <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas"
                   data-bs-target="#offcanvasCreateNotification">
             <i class="bx bx-plus bx-sm me-0 me-sm-2"></i> @lang('Create Notification')
           </button>--}}
    </div>
  </div>
  <!-- Notification table card -->
  <div class="card">
    <div class="card-datatable table-responsive">
      <table id="datatable" class="table border-top">
        <thead>
        <tr>
          <th>@lang('Id')</th>
          <th>@lang('User')</th>
          <th>@lang('Type')</th>
          <th>@lang('Title')</th>
          <th>@lang('Message')</th>
          <th>@lang('Actions')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($notifications as $notification)
          <tr>
            <td>{{$notification->id}}</td>
            <td>
              <div class="d-flex justify-content-start align-items-center user-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-4">
                    {{-- <img
                       src="{{ is_null(!$notification->user) && !is_null($notification->user->profile_picture) ? $notification->user->profile_picture : 'https://avatar.iran.liara.run/username?username='.$notification->user->first_name.'+'.$notification->user->last_name}}"
                       alt class="w-px-40 h-auto rounded-circle">--}}
                  </div>
                </div>
                <div class="d-flex flex-column">
                  @if(is_null(!$notification->user))
                    <span class="fw-bold">{{$notification->user->first_name.' '.$notification->user->last_name}}</span>
                    <span class="text-muted">{{$notification->user->email}}</span>
                  @else
                    <span class="fw-bold">-</span>
                    <span class="text-muted text-muted">-</span>
                  @endif
                </div>
              </div>
            </td>
            <td>{{$notification->type}}</td>
            <td>{{$notification->title}}</td>
            <td>{{$notification->message}}</td>
            <td>
              <button type="button" class="btn btn-sm" data-bs-toggle="tooltip"
                      title="Delete Notification"
                      onclick="deleteNotification({{$notification->id}})">
                <i class="bx bx-trash text-danger"></i>
              </button>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

  </div>
@endsection
