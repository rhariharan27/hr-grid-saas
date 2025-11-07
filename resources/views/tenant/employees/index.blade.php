@extends('layouts/layoutMaster')

@section('title', 'Employees')


<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite([
    'resources/js/main-helper.js',
    'resources/assets/js/app/employee-index.js',
    'resources/js/main-select2.js'
])
@endsection


@section('content')

  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Total</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{$totalUser}}</h4>
                {{--  <p class="text-success mb-0">(100%)</p>--}}
              </div>
              <small class="mb-0">Total Users</small>
            </div>
            <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-user bx-lg"></i>
            </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Active</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{$active}}</h4>
                {{--  <p class="text-success mb-0">(+95%)</p>--}}
              </div>
              <small class="mb-0">Total Active Users </small>
            </div>
            <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-user-check bx-lg"></i>
            </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">InActive</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{$inactive}}</h4>
                <p class="text-success mb-0">(0%)</p>
              </div>
              <small class="mb-0">Tota</small>
            </div>
            <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-group bx-lg"></i>
            </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Relieved</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{$relieved}}</h4>
                <p class="text-danger mb-0">(+6%)</p>
              </div>
              <small class="mb-0">Recent analytics</small>
            </div>
            <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-user-voice bx-lg"></i>
            </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Filter Row -->
  <div class="row mb-4">
    <div class="col-md-3">
      <label for="roleFilter" class="form-label">Filter by role</label>
      <select class="form-select select2 filter-input" id="roleFilter">
        <option value="">All Roles</option>
        @foreach($roles as $role)
          <option value="{{ $role->name }}">{{ $role->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <label for="teamFilter" class="form-label">Filter by team</label>
      <select class="form-select select2 filter-input" id="teamFilter">
        <option value="">All Teams</option>
        @foreach($teams as $team)
          <option value="{{ $team->id }}">{{ $team->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <label for="designationFilter" class="form-label">Filter by designation</label>
      <select class="form-select select2 filter-input" id="designationFilter">
        <option value="">All Designations</option>
        @foreach($designations as $designation)
          <option value="{{ $designation->id }}">{{ $designation->name }}</option>
        @endforeach
      </select>
    </div>
    {{--<!-- Total Users Limit -->
    <div class="col-md-3 col-lg-6 text-md-end align-self-center">
    <span class="d-inline-block mt-2 mt-md-0">
      <span class="fw-bold">Total Users Limit:</span>
      <span class="badge bg-primary fs-6">{{$settings->employees_limit}}</span>
    </span>
    </div>--}}
  </div>
  <!-- DataTable -->
  <div class="card">
    <div class="card-datatable table-responsive">
      <table class="datatables-employees table border-top">
        <thead>
        <tr>
          <th>Id</th>
          <th>User</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Attendance Type</th>
          <th>Team</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Available Employees limit -->
  <div class="row mt-3">
    <div class="col-md-12">
      <div class="alert alert-primary" role="alert">
        <h5 class="alert-heading">Available Employees Limit</h5>
        <p class="mb-0">You have <strong>{{$settings->employees_limit}}</strong> employees limit available. You can add more employees by upgrading your plan.</p>
      </div>
    </div>
  </div>
@endsection
