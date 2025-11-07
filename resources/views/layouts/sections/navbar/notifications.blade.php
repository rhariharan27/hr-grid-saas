@php
  use App\Models\Notification;$notifications = Notification::where('notifiable_id', auth()->id())->orderBy('created_at', 'desc')->take(15)->get();
  $isUnread = $notifications->where('is_read', 0)->count();
@endphp
<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
     data-bs-auto-close="outside" aria-expanded="false">

      <span class="position-relative">
              <i class="bx bx-bell bx-md"></i>
         @if($isUnread > 0)
          <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
        @endif
            </span>

  </a>
  <ul class="dropdown-menu dropdown-menu-end p-0">
    <li class="dropdown-menu-header border-bottom">
      <div class="dropdown-header d-flex align-items-center py-3">
        <h6 class="mb-0 me-auto">@lang('Notifications')</h6>
        <div class="d-flex align-items-center h6 mb-0">
          <span class="badge bg-label-primary me-2">{{$isUnread}} @lang('New')</span>
          {{-- <a href="javascript:void(0)" class="dropdown-notifications-all p-2" data-bs-toggle="tooltip"
              data-bs-placement="top" title="Mark all as read"><i
               class="bx bx-envelope-open text-heading"></i></a>--}}
        </div>
      </div>
    </li>
    <li class="dropdown-notifications-list scrollable-container">
      <ul class="list-group list-group-flush">
        @foreach($notifications as $notification)
          @php
            $data = json_decode($notification->data);
            $title = $data->title ?? $notification->type;
            $message = $data->message ?? $data->body ?? 'N/A';
          @endphp
          <li
            class="list-group-item list-group-item-action dropdown-notifications-item {{$notification->is_read == 1 ? 'marked-as-read' : ''}}">
            <div class="d-flex">
              <div class="flex-shrink-0 me-3">
                <div class="avatar">
                  {{-- @if($notification->notification-> == 'all')
                     <span class="avatar-initial rounded-circle bg-label-primary"><i
                         class="bx bx-bell"></i></span>
                   @elseif($notification->notification->type == 'role')
                     <span class="avatar-initial rounded-circle bg-label-warning"><i
                         class="bx bx-bell"></i></span>
                   @elseif($notification->notification->type == 'user')
                     <span class="avatar-initial rounded-circle bg-label-success"><i
                         class="bx bx-bell"></i></span>
                   @endif--}}
                  <span class="avatar-initial rounded-circle bg-label-success"><i
                      class="bx bx-bell"></i></span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6
                  class="small mb-0">{{$title}}</h6>
                <small
                  class="mb-1 d-block text-body">{{$message}}</small>
                <small class="text-muted">{{$notification->created_at->diffForHumans()}}</small>
              </div>
              <div class="flex-shrink-0 dropdown-notifications-actions">
                <a href="javascript:void(0)" class="dropdown-notifications-read"><span
                    class="badge badge-dot"></span></a>
                <a href="javascript:void(0)" class="dropdown-notifications-archive"><span
                    class="bx bx-x"></span></a>
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    </li>
    <li class="border-top">
      <div class="d-grid p-4">
        <a class="btn btn-primary btn-sm d-flex" href="{{route('notifications.myNotifications')}}">
          <small class="align-middle">@lang('View all notifications')</small>
        </a>
      </div>
    </li>
  </ul>
</li>
