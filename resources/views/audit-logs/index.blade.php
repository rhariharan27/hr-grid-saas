@extends('layouts/layoutMaster')

@section('title', __('Audit Logs'))

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
      <h4>@lang('Audit Logs')</h4>
    </div>
    <div class="col text-end">

    </div>
  </div>
  <!-- Audit Log table card -->
  <div class="card">
    <div class="card-datatable table-responsive">
      <table id="datatable" class="datatables-users table border-top">
        <thead>
        <tr>
          <th>@lang('Id')</th>
          <th>@lang('User')</th>
          <th>@lang('Event')</th>
          <th>@lang('Ip')</th>
          <th>@lang('Model')</th>
          <th>@lang('Created At')</th>
          <th>@lang('Actions')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($auditLogs as $auditLog)
          <tr>
            <td>{{$auditLog->id}}</td>
            <td>
              @if($auditLog->user == null)
                <span class="text-muted">@lang('N/A')</span>
              @else
              <div class="d-flex justify-content-start align-items-center user-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-4">
                    <img
                      src="{{ !is_null($auditLog->user->profile_picture) ? $auditLog->user->profile_picture : 'https://avatar.iran.liara.run/username?username='.$auditLog->user->first_name.'+'.$auditLog->user->last_name}}"
                      alt class="w-px-40 h-auto rounded-circle">
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span
                    class="fw-bold">{{$auditLog->user->first_name.' '.$auditLog->user->last_name}}</span>
                  <span class="text-muted">{{$auditLog->user->email}}</span>
                </div>
              </div>
              @endif
            </td>
            <td>{{$auditLog->event}}</td>
            <td>{{$auditLog->ip_address}}</td>
            <td>{{$auditLog->auditable_type}}</td>
            <td>{{$auditLog->created_at}}</td>
            <td>
              <a href="{{route('auditLogs.show', $auditLog->id)}}"
                 class="btn btn-icon">
                <i class="bx bx-show"></i>
              </a>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
