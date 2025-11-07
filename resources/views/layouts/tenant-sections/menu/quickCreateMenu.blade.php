@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
@endphp

  <!-- Quick Create DropDown -->
<li class="nav-item dropdown dropdown-quick-create me-2 me-xl-0">
  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
    <i
      data-bs-toggle="tooltip"
      data-bs-placement="top"
      title="@lang('Quick Create')"
      class="bx bx-plus-circle text-heading"></i>
  </a>
  <div class="dropdown-menu dropdown-menu-end">
    @foreach ($menuData[2]->menu as $menu)
      <a href="{{url($menu->url)}}" class="dropdown-item">
        @if(isset($menu->icon))
          <i class='{{$menu->icon}}'></i>
        @endif
        <span>@lang($menu->name)</span>
      </a>
  @endforeach
</li>
<!-- /Quick Create DropDown -->

{{--
<a href="javascript:void(0);" class="dropdown-item">
  <i class=' bx bx-group'></i>
  <span>@lang('User')</span>
</a>
<a href="javascript:void(0);" class="dropdown-item">
  <i class='bx bx-bell'></i>
  <span>@lang('Notification')</span>
</a>--}}
