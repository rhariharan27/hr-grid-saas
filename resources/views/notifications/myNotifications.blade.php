@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', __('My Notifications'))

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  ])
@endsection

@section('page-script')
  @vite(['resources/js/main-datatable.js'])
@endsection


@section('content')

  <div class="row">
    <div class="col">
      <h4>@lang('My Notifications')</h4>
    </div>
    <div class="col text-end">
      {{-- <a class="btn btn-primary" href="{{route('notifications.marksAllAsRead')}}">
         <i class="bx bx-no-entry bx-sm me-0 me-sm-2"></i> @lang('Mark all as read')
       </a>--}}
    </div>
  </div>
  <!-- Notification table card -->
  <div class="card">
    <div class="card-datatable table-responsive">
      <table id="datatable" class="datatables-users table border-top">
        <thead>
        <tr>
          <th></th>
          <th>@lang('Id')</th>
          <th>@lang('From')</th>
          <th>@lang('Type')</th>
          <th>@lang('Title')</th>
          <th>@lang('Message')</th>
          <th>@lang('Actions')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($notifications as $notification)
          <tr>
            <td></td>
            <td>
              {{$loop->iteration}}
            </td>
            <td>
              <div class="d-flex justify-content-start align-items-center user-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-4">
                    <img
                      src=" 'https://avatar.iran.liara.run/username?username="
                      alt class="w-px-40 h-auto rounded-circle">
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span
                    class="fw-bold">Name</span>
                  <span class="text-muted">EMail</span>
                </div>
              </div>
            </td>
            <td>{{$notification->getTypeString ?? 'N/A'}}</td>
            <td>{{json_encode($notification->data)}}</td>
            <td>{{$notification->notification}}</td>
            <td>
              @if($notification->is_read == 0)
                <form action="{{route('notifications.markAsRead', $notification->id)}}" method="POST">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-sm me-2" data-bs-toggle="tooltip"
                          title="Mark as read"><i class="bx bx-check"></i>
                  </button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

  </div>

@endsection
