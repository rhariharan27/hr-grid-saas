@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Roles - Apps')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
@endsection

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
  @vite([
    'resources/assets/js/app/role-index.js',
    ])
@endsection

@section('content')
  <h4 class="mb-1">@lang('Roles')</h4>
  <!-- Role cards -->
  <div class="row g-6">
    @foreach($roles as $role)
      <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h6 class="fw-normal mb-0 text-body">@lang('Total') {{$role->users()->count()}} @lang('Users')</h6>
              <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                @foreach($role->users()->limit(3)->get() as $user)
                  @php
                    $randomStatusColor = ['primary', 'success', 'danger', 'warning', 'info', 'dark'];
                    $randomColor = $randomStatusColor[array_rand($randomStatusColor)];
                  @endphp
                  <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                      title="{{$user->getFullName()}}"
                      class="avatar pull-up">
                    @if($user->profile_picture)
                      <img class="rounded-circle"
                           src="{{$user->getProfilePicture()}}"
                           alt="Avatar">
                    @else
                      <span
                        class="avatar-initial rounded-circle bg-label-{{$randomColor}}">{{ $user->getInitials() }}</span>
                    @endif
                  </li>
                @endforeach
                @if($role->users()->count() > 3)
                  <li class="avatar">
                    <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip"
                          data-bs-placement="bottom"
                          title="{{$role->users()->count() - 3}} more">+{{$role->users()->count() - 3}}</span>
                  </li>
                @endif

              </ul>
            </div>
            <div class="d-flex justify-content-between align-items-end">
              <div class="role-heading">
                <h5 class="mb-1">{{$role->name}}</h5>
              </div>
              <div class="d-flex">
                <a href="javascript:void(0);"><i
                    class="bx bx-pencil bx-md text-muted me-2 edit" data-value="{{$role}}"></i></a>
                <a href="javascript:void(0);" onclick="deleteRole({{$role->id}})"><i
                    class="bx bx-trash  bx-md text-muted"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
    <div class="col-xl-4 col-lg-6 col-md-6">
      <div class="card h-100">
        <div class="row h-100">
          <div class="col-sm-5">
            <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-4 ps-6">
              <img src="{{asset('assets/img/illustrations/lady-with-laptop-'.$configData['style'].'.png')}}"
                   class="img-fluid" alt="Image" width="120"
                   data-app-light-img="illustrations/lady-with-laptop-light.png"
                   data-app-dark-img="illustrations/lady-with-laptop-dark.png">
            </div>
          </div>
          <div class="col-sm-7">
            <div class="card-body text-sm-end text-center ps-sm-0">
              <button data-bs-target="#addOrUpdateRoleModal" data-bs-toggle="modal"
                      class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role">@lang('Add New Role')
              </button>
              <p class="mb-0"> Add new role, <br> if it doesn't exist.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Role cards -->

  @if($settings->is_helper_text_enabled)
    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
      <p class="alert-body d-flex">
      <h6 class="alert-heading">
        <i class="bx bx-info-circle me-2"></i>Warning: </h6>
      <p class="mb-0">
        Do not delete the default system roles <strong>{{implode(', ', Constants::BuiltInRoles)}}.</strong>. Deleting
        these roles will cause the system to malfunction.
      </p>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Add Role Modal -->
  @include('_partials._modals.role.addOrUpdate-role')
  <!-- / Add Role Modal -->
@endsection
