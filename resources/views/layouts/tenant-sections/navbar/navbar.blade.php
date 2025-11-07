@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
  $containerNav = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
  $navbarDetached = ($navbarDetached ?? '');
@endphp

  <!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
  <nav
    class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme"
    id="layout-navbar">
    @endif
    @if(isset($navbarDetached) && $navbarDetached == '')
      <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{$containerNav}}">
          @endif

          <!--  Brand demo (display only for navbar-full and hide on below xl) -->
          @if(isset($navbarFull))
            <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
              <a href="{{url('/')}}" class="app-brand-link gap-2">
                <span
                  class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
                <span
                  class="app-brand-text demo menu-text fw-bold text-heading">{{config('variables.templateName')}}</span>
              </a>

              @if(isset($menuHorizontal))
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                  <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
                </a>
              @endif
            </div>
          @endif

          <!-- ! Not required for layout-without-menu -->
          @if(!isset($navbarHideToggle))
            <div
              class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="bx bx-menu bx-md"></i>
              </a>
            </div>
          @endif

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

            @if(!isset($menuHorizontal))
              <!-- Search -->
              @if($configData['displaySearch'] == true)
                <div class="navbar-nav align-items-center">
                  <div class="nav-item navbar-search-wrapper mb-0">
                    <a class="nav-item nav-link search-toggler px-0" href="javascript:void(0);">
                      <i class="bx bx-search bx-md"></i>
                      <span class="d-none d-md-inline-block text-muted fw-normal ms-4">@lang('Search') (Ctrl+/)</span>
                    </a>
                  </div>
                </div>
              @endif
              <!-- /Search -->
            @endif

            <ul class="navbar-nav flex-row align-items-center ms-auto">
              @if(isset($menuHorizontal))
                <!-- Search -->
                @if($configData['displaySearch'] == true)
                  <li class="nav-item navbar-search-wrapper me-2 me-xl-0">
                    <a class="nav-link search-toggler" href="javascript:void(0);">
                      <i class="bx bx-search bx-md"></i>
                    </a>
                  </li>
                @endif
                <!-- /Search -->
              @endif
              @if($configData['displayQuickCreate'] == true)
                @include('layouts.sections.menu.quickCreateMenu')
              @endif
              @if($configData['displayAddon'] == true)
                <!--Addons -->
                <li class="nav-item dropdown dropdown-addons me-2 me-xl-0">
                  <a class="nav-link dropdown-toggle hide-arrow" href="{{route('addons.index')}}">
                    <i data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="@lang('Addons')" class="bx bx-category"></i>
                  </a>
                </li>
              @endif
              <!-- Language -->
              @if($configData['displayLanguage'] == true)
                <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class=' bx bx-globe bx-md'></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                         href="{{url('lang/en')}}"
                         data-language="en" data-text-direction="ltr">
                        <span>English</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'fr' ? 'active' : '' }}"
                         href="{{url('lang/fr')}}"
                         data-language="fr" data-text-direction="ltr">
                        <span>French</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}"
                         href="{{url('lang/ar')}}"
                         data-language="ar" data-text-direction="rtl">
                        <span>Arabic</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'de' ? 'active' : '' }}"
                         href="{{url('lang/de')}}"
                         data-language="de" data-text-direction="ltr">
                        <span>German</span>
                      </a>
                    </li>
                  </ul>
                </li>
              @endif
              <!-- /Language -->

              @if($configData['hasCustomizer'] == true)
                <!-- Style Switcher -->
                <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                     data-bs-toggle="dropdown">
                    <i class='bx bx-md'></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                        <span><i class='bx bx-sun bx-md me-3'></i>Light</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                        <span><i class="bx bx-moon bx-md me-3"></i>Dark</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                        <span><i class="bx bx-desktop bx-md me-3"></i>System</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ Style Switcher -->
              @endif

              <!-- Quick links  -->
              @if($configData['displayShortcut'] == true)
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                     data-bs-auto-close="outside" aria-expanded="false">
                    <i class='bx bx-grid-alt bx-md'></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end p-0">
                    <div class="dropdown-menu-header border-bottom">
                      <div class="dropdown-header d-flex align-items-center py-3">
                        <h6 class="mb-0 me-auto">Shortcuts</h6>
                        <a href="javascript:void(0)" class="dropdown-shortcuts-add py-2" data-bs-toggle="tooltip"
                           data-bs-placement="top" title="Add shortcuts"><i
                            class="bx bx-plus-circle text-heading"></i></a>
                      </div>
                    </div>
                    <div class="dropdown-shortcuts-list scrollable-container">
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-calendar bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('app/calendar')}}" class="stretched-link">Calendar</a>
                          <small>Appointments</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-food-menu bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('app/invoice/list')}}" class="stretched-link">Invoice App</a>
                          <small>Manage Accounts</small>
                        </div>
                      </div>
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-user bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('app/user/list')}}" class="stretched-link">User App</a>
                          <small>Manage Users</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-check-shield bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('app/access-roles')}}" class="stretched-link">Role Management</a>
                          <small>Permission</small>
                        </div>
                      </div>
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-pie-chart-alt-2 bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('/')}}" class="stretched-link">Dashboard</a>
                          <small>User Dashboard</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-cog bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('pages/account-settings-account')}}" class="stretched-link">Setting</a>
                          <small>Account Settings</small>
                        </div>
                      </div>
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-help-circle bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('pages/faq')}}" class="stretched-link">FAQs</a>
                          <small>FAQs & Articles</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                    <i class="bx bx-window-open bx-26px text-heading"></i>
                  </span>
                          <a href="{{url('modal-examples')}}" class="stretched-link">Modals</a>
                          <small>Useful Popups</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              @endif
              <!-- Quick links -->

              <!-- Notification -->
              @if($configData['displayNotification'] == true)
                @include('layouts.sections.navbar.notifications')
              @endif
              <!--/ Notification -->

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                   data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img
                      src="{{ Auth::user() && !is_null(Auth::user()->profile_picture) ? Auth::user()->profile_picture : 'https://avatar.iran.liara.run/username?username='.Auth::user()->first_name.'+'.Auth::user()->last_name}}"
                      alt class="w-px-40 h-auto rounded-circle">
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item"
                       href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img
                              src="{{ Auth::user() && !is_null(Auth::user()->profile_picture) ? Auth::user()->profile_picture : 'https://avatar.iran.liara.run/username?username='.Auth::user()->first_name.'+'.Auth::user()->last_name}}"
                              alt class="w-px-40 h-auto rounded-circle">
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <h6 class="mb-0">
                            {{Auth::user()->getFullName()}}
                          </h6>
                          <small class="text-muted">{{Auth::user()->roles()->first()->name}}</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider my-1"></div>
                  </li>
                  <li>
                    <a class="dropdown-item"
                       href="{{ route('account.myProfile') }}">
                      <i class="bx bx-user bx-md me-3"></i><span>@lang('My Profile')</span>
                    </a>
                  </li>
                  <li>
                    <a role="button" class="dropdown-item" data-bs-target="#changePasswordModal"
                       data-bs-toggle="modal">
                <span class="d-flex align-items-center align-middle">
                  <i class="flex-shrink-0 bx bx-lock bx-md me-3"></i><span
                    class="flex-grow-1 align-middle">@lang('Change Password')</span>
                </span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider my-1"></div>
                  </li>
                  @if (Auth::check())
                    <li>
                      <a class="dropdown-item" href="{{ route('auth.logout') }}"
                         onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class='bx bx-power-off bx-md me-3'></i><span>@lang('Logout')</span>
                      </a>
                    </li>
                    <form method="POST" id="logout-form" action="{{ route('auth.logout') }}">
                      @csrf
                    </form>
                  @else
                    <li>
                      <a class="dropdown-item"
                         href="{{ Route::has('login') ? route('auth.login') : url('auth/login-basic') }}">
                        <i class='bx bx-log-in bx-md me-3'></i><span>@lang('Login')</span>
                      </a>
                    </li>
                  @endif
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>

          <!-- Search Small Screens -->
          <div
            class="navbar-search-wrapper search-input-wrapper {{ isset($menuHorizontal) ? $containerNav : '' }} d-none">
            <input type="text"
                   class="form-control search-input {{ isset($menuHorizontal) ? '' : $containerNav }} border-0"
                   placeholder="@lang('Search...')" aria-label="Search...">
            <i class="bx bx-x bx-md search-toggler cursor-pointer"></i>
          </div>
          @if(isset($navbarDetached) && $navbarDetached == '')
        </div>
        @endif
      </nav>
      <!-- / Navbar -->
