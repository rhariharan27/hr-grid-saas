<?php

namespace App\Http\Controllers;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use Constants;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
  public function index()
  {
    $roles = Role::with('users')->get();

    return view('roles.index', compact('roles'));
  }

  public function addOrUpdateAjax(Request $request)
  {

    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in demo mode');
    }

    // Validate the request
    $validator = validator($request->all(), [
      'name' => 'required|string|unique:roles,name' . ($request->id ? ',' . $request->id : ''),
    ]);

    if ($validator->fails()) {
      return Error::response($validator->errors()->first());
    }

    // Prepare role data
    $roleData = [
      'name' => $request->name,
      'is_multiple_check_in_enabled' => $request->has('isMultiCheckInEnabled') && $request->isMultiCheckInEnabled == 'on',
      'is_mobile_app_access_enabled' => $request->has('mobileAppAccess') && $request->mobileAppAccess == 'on',
      'is_web_access_enabled' => $request->has('webAppAccess') && $request->webAppAccess == 'on',
      'is_location_activity_tracking_enabled' => $request->has('locationActivityTracking') && $request->locationActivityTracking == 'on',
    ];

    // Check if updating or creating
    if ($request->id) {
      // Update Existing Role
      $role = Role::find($request->id);
      if (!$role) {
        return Error::response('Role not found');
      }
      $role->update($roleData);
      return Success::response('Role updated successfully');
    } else {
      // Create New Role
      Role::create($roleData);
      return Success::response('Role created successfully');
    }
  }

  public function deleteAjax($id)
  {

    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in demo mode');
    }

    $role = Role::find($id);

    if (!$role) {
      return Error::response('Role not found');
    }

    if ($role->users->count() > 0) {
      return Error::response('Role has users assigned to it');
    }

    if (in_array($role->name, Constants::BuiltInRoles)) {
      return Error::response('Built-in roles cannot be deleted');
    }

    $role->delete();

    return Success::response('Role deleted successfully');
  }
}
