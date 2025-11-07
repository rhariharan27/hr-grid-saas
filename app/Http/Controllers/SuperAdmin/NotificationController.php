<?php

namespace App\Http\Controllers\SuperAdmin;

use App\ApiClasses\Success;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

  public function index()
  {
    $notifications = Auth::user()->notifications;

    //$users = User::all();

    //Notification::send($users, new Announcement('New Test Announcement'));

    return view('notifications.index', compact('notifications'));
  }

  public function markAsRead()
  {
    Auth::user()->unreadNotifications->markAsRead();
    return redirect()->back()->with('success', 'All notifications marked as read.');
  }

  public function getNotificationsAjax()
  {
    $notifications = Auth::user()->notifications;
    return Success::response($notifications);
  }

  public function myNotifications()
  {
    $notifications = Auth::user()->notifications;
    return view('notifications.myNotifications', compact('notifications'));
  }
}
