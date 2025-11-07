<?php

namespace App\Http\Controllers;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\UserAccountStatus;
use App\Models\SuperAdmin\DomainRequest;
use App\Models\SuperAdmin\Order;
use App\Models\User;
use Constants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{

  public function customerIndex()
  {
    $users = User::where('is_customer', true)->get();

    $userCount = $users->count();

    $verified = User::where('is_customer', true)
      ->whereNotNull('email_verified_at')
      ->get()
      ->count();

    $notVerified = User::where('is_customer', true)
      ->whereNull('email_verified_at')->get()->count();

    $usersUnique = $users->unique(['email']);
    $userDuplicates = $users->diff($usersUnique)->count();

    return view('account.customerIndex', [
      'totalUser' => $userCount,
      'verified' => $verified,
      'notVerified' => $notVerified,
      'userDuplicates' => $userDuplicates,
    ]);
  }

  public function customerIndexAjax(Request $request)
  {
    // Build an Eloquent query for customers with their subscription relations
    $query = User::query()
      ->where('is_customer', true);

    // Use DataTables::eloquent to work with the Eloquent builder
    return DataTables::eloquent($query)
      ->addIndexColumn() // This adds DT_RowIndex (used as fake_id on front end)
      // Change 'name' to the full name (first + last)
      ->editColumn('name', function ($user) {
        return $user->getFullName();
      })
      ->filterColumn('user', function ($query, $keyword) {
        $query->where('first_name', 'like', "%{$keyword}%")
          ->orWhere('last_name', 'like', "%{$keyword}%");
      })
      ->orderColumn('name', function ($query, $order) {
        $query->orderBy('first_name', $order)
          ->orderBy('last_name', $order);
      })
      // Prepare a "subscription" field
      ->editColumn('subscription', function ($user) {
        $subscription = $user->activeSubscription();
        if ($subscription) {
          return [
            'plan' => $subscription->plan->name,
            'start_date' => $subscription->start_date,
            'end_date' => $subscription->end_date,
            'included_users' => $subscription->plan->included_users,
            'additional_users' => $subscription->additional_users,
          ];
        }
        return null;
      })
      // Also add a "plan_info" field (you can format it the same as subscription)
      ->addColumn('plan_info', function ($user) {
        $subscription = $user->activeSubscription();
        if ($subscription) {
          return [
            'plan' => $subscription->plan->name,
            'end_date' => $subscription->end_date,
            'included_users' => $subscription->plan->included_users,
            'additional_users' => $subscription->additional_users,
          ];
        }
        return null;
      })
      // Add an "action" column that returns HTML for the action buttons
      ->addColumn('action', function ($user) {
        $viewUrl = route('account.viewUser', $user->id);
        return '<div class="d-flex align-items-center gap-50">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-icon edit-record" data-id="' . $user->id . '">
                          <i class="bx bx-show"></i>
                        </a>
                    </div>';
      })
      // Apply global search on first_name, last_name and email
      ->filter(function ($query) use ($request) {
        if ($search = $request->input('search.value')) {
          $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
          });
        }
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function index()
  {
    $users = User::where('is_customer', false)->get();

    $userCount = $users->count();

    $verified = User::where('is_customer', false)
      ->whereNotNull('email_verified_at')
      ->get()
      ->count();

    $notVerified = User::where('is_customer', false)
      ->whereNull('email_verified_at')->get()->count();

    $usersUnique = $users->unique(['email']);
    $userDuplicates = $users->diff($usersUnique)->count();

    return view('account.index', [
      'totalUser' => $userCount,
      'verified' => $verified,
      'notVerified' => $notVerified,
      'userDuplicates' => $userDuplicates,
    ]);
  }

  public function viewUser($id)
  {
    $user = User::findOrFail($id);

    $orders = Order::with('plan')
      ->where('user_id', $id)
      ->get();

    $domains = DomainRequest::where('user_id', $id)->get();

    return view('account.user-details', [
      'user' => $user,
      'orders' => $orders,
      'domains' => $domains,
    ]);
  }

  public function activeInactiveUserAjax($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    if ($user->status == UserAccountStatus::ACTIVE) {
      $user->status = UserAccountStatus::INACTIVE;
    } else {
      $user->status = UserAccountStatus::ACTIVE;
    }

    $user->save();

    return Success::response('User status changed successfully');
  }

  public function suspendUserAjax($id)
  {
    $user = User::find($id);

    $user->status = UserAccountStatus::RETIRED;

    $user->save();

    return Success::response('User suspended successfully');
  }

  public function deleteUserAjax($id)
  {

    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    $user->delete();

    return Success::response('User deleted successfully');
  }

  public function myProfile()
  {
    $user = User::find(auth()->user()->id);

    $auditLogs = Audit::where('user_id', auth()->user()->id)
      ->where('auditable_type', 'App\Models\User')
      ->orderBy('created_at', 'desc')
      ->get();

    $role = $user->roles()->first();

    return view('account.my-profile', [
      'user' => $user,
      'auditLogs' => $auditLogs,
      'role' => $role,
    ]);
  }

  public function changeProfilePicture(Request $request)
  {
    $rules = [
      'userId' => 'required|exists:users,id',
      'file' => 'required|image|mimes:jpeg,png,jpg|max:5096',
    ];

    $validatedData = $request->validate($rules);

    try {
      $user = User::find($request->input('userId'));

      if (!$user) {
        return Error::response('User not found');
      }

      if ($request->hasFile('file')) {
        $file = $request->file('file');
        $fileName = $user->code . '_' . time() . '.' . $file->getClientOriginalExtension();

        //Delete Old File
        $oldProfilePicture = $user->profile_picture;
        if (!is_null($oldProfilePicture)) {
          $oldProfilePicturePath = Storage::disk('public')->path(Constants::BaseFolderEmployeeProfileWithSlash . $oldProfilePicture);
          if (file_exists($oldProfilePicturePath)) {
            Storage::delete($oldProfilePicturePath);
          }
        }

        //Create Directory if not exists
        if (!Storage::disk('public')->exists(Constants::BaseFolderEmployeeProfile)) {
          Storage::disk('public')->makeDirectory(Constants::BaseFolderEmployeeProfile);
        }

        Storage::disk('public')->putFileAs(Constants::BaseFolderEmployeeProfileWithSlash, $file, $fileName);

        $user->profile_picture = $fileName;
        $user->save();
      }

      return redirect()->back()->with('success', 'Profile picture updated successfully');
    } catch (Exception $e) {
      Log::error('EmployeeController@changeEmployeeProfilePicture: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to update profile picture');
    }
  }

  public function userListAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'first_name',
        3 => 'email',
        4 => 'email_verified_at',
        5 => 'status',
      ];

      $search = [];

      $totalData = User::where('is_customer', false)->count();

      $totalFiltered = $totalData;

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');


      if (empty($request->input('search.value'))) {
        $users = User::where('is_customer', false)
          ->offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();
      } else {
        $search = $request->input('search.value');

        $users = User::where('is_customer', false)
          ->where('id', 'LIKE', "%{$search}%")
          ->orWhere('first_name', 'LIKE', "%{$search}%")
          ->orWhere('last_name', 'LIKE', "%{$search}%")
          ->orWhere('phone', 'LIKE', "%{$search}%")
          ->orWhere('email', 'LIKE', "%{$search}%")
          ->offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();

        $totalFiltered = User::where('is_customer', false)
          ->where('id', 'LIKE', "%{$search}%")
          ->orWhere('first_name', 'LIKE', "%{$search}%")
          ->orWhere('last_name', 'LIKE', "%{$search}%")
          ->orWhere('phone', 'LIKE', "%{$search}%")
          ->orWhere('email', 'LIKE', "%{$search}%")
          ->count();
      }

      $data = [];

      if (!empty($users)) {
        // providing a dummy id instead of database ids
        $ids = $start;

        foreach ($users as $user) {
          $nestedData['id'] = $user->id;
          $nestedData['fake_id'] = ++$ids;
          $nestedData['name'] = $user->getFullName();
          $nestedData['email'] = $user->email;
          $nestedData['email_verified_at'] = $user->email_verified_at;
          $nestedData['status'] = $user->status;

          $data[] = $nestedData;
        }
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'code' => 200,
        'data' => $data,
      ]);
    } catch (Exception $e) {
      return Error::response($e->getMessage());
    }
  }


  public function addOrUpdateUserAjax(Request $request)
  {
    $request->validate([
      'firstName' => 'required',
      'lastName' => 'required',
      'email' => 'required|email|unique:users,email,' . $request->userId,
      'phone' => 'required'
    ]);

    try {
      $userId = $request->userId;

      if ($userId) {
        $user = User::find($userId);

        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->is_customer = false;

        $user->save();

        $user->assignRole('super_admin');

        return response()->json([
          'message' => 'updated',
          'code' => 200,
        ]);

      } else {
        $user = new User();

        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = bcrypt('123456');
        $user->is_customer = false;
        $user->email_verified_at = now();
        $user->phone_verified_at = now();

        $user->save();

        $user->assignRole($request->role);

        return response()->json([
          'message' => 'added',
          'code' => 200,
        ]);
      }
    } catch (Exception $e) {
      return response()->json([
        'message' => $e->getMessage(),
        'code' => 500,
      ]);
    }
  }

  public function editUserAjax($id)
  {

    try {
      $user = User::find($id);

      $response = [
        'id' => $user->id,
        'firstName' => $user->first_name,
        'lastName' => $user->last_name,
        'email' => $user->email,
        'phone' => $user->phone,
        'role' => $user->roles()->exists() ? $user->roles()->first()->name : '',
        'status' => $user->status,
        'gender' => $user->gender
      ];

      return response()->json($response);
    } catch (Exception $e) {
      return response()->json([
        'message' => $e->getMessage(),
        'code' => 500,
      ]);
    }
  }

  public function updateUserAjax(Request $request, $id)
  {

    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $request->validate([
      'first_name' => 'required',
      'last_name' => 'required',
      'email' => 'required|email',
      'role' => 'required',
    ]);

    $user = User::find($id);
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->status = $request->status;
    $user->save();

    $user->syncRoles([$request->role]);

    return response()->json([
      'message' => 'User updated successfully',
      'code' => 200,
    ]);
  }

  public function changeUserStatusAjax($id)
  {
    $user = User::find($id);

    if ($user->status == 'active') {
      $user->status = 'inactive';
    } else {
      $user->status = 'active';
    }

    $user->save();

    return response()->json([
      'message' => 'User status changed successfully',
      'code' => 200,
    ]);
  }

  public function getRolesAjax()
  {
    $roles = Role::get();

    return Success::response($roles);
  }

  public function getUsersByRoleAjax($role)
  {
    if (!$role) {
      return Error::response('Role is required');
    }

    if ($role == 'all') {
      $users = User::get();
      return Success::response($users);
    }

    $role = Role::find($role);

    $users = $role->users;

    return Success::response($users);
  }

  public function getUsersAjax()
  {
    $users = User::where('id', '!=', auth()->user()->id)->get();

    return Success::response($users);
  }

  public function changePassword(Request $request)
  {

    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $request->validate([
      'oldPassword' => 'required|min:6',
      'newPassword' => 'required|min:6',
    ]);

    if (!(Hash::check($request->oldPassword, auth()->user()->password))) {
      return redirect()->back()->with('error', 'Your current password does not matches with the password you provided. Please try again.');
    }

    $user = User::find(auth()->user()->id);
    $user->password = bcrypt($request->newPassword);
    $user->save();

    return redirect()->back()->with('success', 'Password changed successfully');
  }
}
