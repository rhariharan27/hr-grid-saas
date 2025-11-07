<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SOSController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserSettingsController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\AttendanceRequestController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

require __DIR__ . '/chat.php';

Route::middleware([
  'api'
])->group(function () {
  Route::middleware('api')->group(function () {
    Route::group(['prefix' => 'V1'], function () {
      Route::get('getTenantDomains', [BaseApiController::class, 'getTenantDomains'])->name('api.base.getTenantDomains');
      Route::get('checkDemoMode', [BaseApiController::class, 'checkDemoMode'])->name('api.base.checkDemoMode');
    });
  });
});

Route::middleware([
  'api',
  InitializeTenancyByDomain::class,
  PreventAccessFromCentralDomains::class,
])->group(function () {

  // Publicly accessible routes
  Route::middleware('api')->group(function () {

    Route::group(['prefix' => 'V1'], function () {

      Route::get('hello', function () {
        return response()->json(['message' => 'Hello World!']);
      });

      Route::get('/tenant', function () {
        return response()->json([
          'message' => 'This is your multi-tenant application. The id of the current tenant is ',
          'tenant_id' => tenant('id'),
        ]);
      });


      //Settings
      Route::group(['prefix' => 'settings/'], function () {
        Route::get('getAppSettings ', [SettingsController::class, 'getAppSettings'])->name('getAppSettings');
        Route::get('getModuleSettings', [SettingsController::class, 'getModuleSettings'])->name('getModuleSettings');
      });


      Route::post('checkUsername', [AuthController::class, 'checkEmail'])->name('api.auth.checkUserName');

      Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
      Route::post('loginWithUid', [AuthController::class, 'loginWithUid'])->name('loginWithUid');
      Route::post('createDemoUser', [AuthController::class, 'createDemoUser'])->name('createDemoUser');

      //Open Auth Routes
      Route::group(['prefix' => 'auth/'], function () {

        Route::get('refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
      });
    });


  });

  // Protected routes

  Route::middleware('auth:api')->group(function () {
    Route::group([
      'middleware' => 'api',
      'as' => 'api.',
    ], function ($router) {
      Route::group(['prefix' => 'V1/'], function () {

        //Authentication
        Route::group(['prefix' => 'auth/'], function () {
          Route::post('logout', [AuthController::class, 'logout'])->name('logout');
          Route::post('changePassword', [AuthController::class, 'changePassword'])->name('changePassword');
        });

        Route::prefix('userSettings/')->name('userSettings.')->group(function () {
          Route::get('getAll', [UserSettingsController::class, 'getAll'])->name('getAll');
          Route::post('getByKey', [UserSettingsController::class, 'getByKey'])->name('getByKey');
          Route::post('addOrUpdate', [UserSettingsController::class, 'addOrUpdate'])->name('addOrUpdate');
          Route::delete('delete', [UserSettingsController::class, 'delete'])->name('delete');
        });

        //Account
        Route::group(['prefix' => 'account/'], function () {
          Route::get('me', [AccountController::class, 'me'])->name('me');
          Route::get('getAccountStatus', [AccountController::class, 'getAccountStatus'])->name('getAccountStatus');
          Route::get('getProfilePicture', [AccountController::class, 'getProfilePicture'])->name('getProfilePicture');
          Route::post('updateProfilePicture', [AccountController::class, 'updateProfilePicture'])->name('updateProfilePicture');
          Route::get('getLanguage', [AccountController::class, 'getLanguage'])->name('getLanguage');
          Route::post('updateLanguage', [AccountController::class, 'updateLanguage'])->name('updateLanguage');
          Route::post('updateProfile', [AccountController::class, 'updateProfile'])->name('updateProfile');
          Route::post('deleteAccountRequest', [AccountController::class, 'deleteAccountRequest'])->name('deleteAccountRequest');
        });


        //Device Controller
        Route::get('checkDevice', [DeviceController::class, 'checkDevice'])->name('checkDevice');
        Route::get('validateDevice', [DeviceController::class, 'validateDevice'])->name('validateDevice');
        Route::post('registerDevice', [DeviceController::class, 'registerDevice'])->name('registerDevice');
        Route::post('messagingToken', [DeviceController::class, 'messagingToken'])->name('messagingToken');
        Route::post('updateDeviceStatus', [DeviceController::class, 'updateDeviceStatus'])->name('updateDeviceStatus');

        //Attendance
        Route::group(['prefix' => 'attendance/'], function () {
          Route::get('checkStatus', [AttendanceController::class, 'checkStatus'])->name('checkStatus');
          Route::post('checkInOut', [AttendanceController::class, 'checkInOut'])->name('checkInOut');
          Route::post('statusUpdate', [AttendanceController::class, 'statusUpdate'])->name('statusUpdate');
          Route::get('canCheckOut', [AttendanceController::class, 'canCheckOut'])->name('canCheckOut');
          Route::post('setEarlyCheckoutReason', [AttendanceController::class, 'setEarlyCheckoutReason'])->name('setEarlyCheckoutReason');
          Route::get('getHistory', [AttendanceController::class, 'getHistory'])->name('getHistory');
        });

        //Leave
        Route::get('getLeaveTypes', [LeaveController::class, 'getLeaveTypes'])->name('getLeaveTypes');
        Route::post('createLeaveRequest', [LeaveController::class, 'createLeaveRequest'])->name('createLeaveRequest');
        Route::get('getLeaveRequests', [LeaveController::class, 'getLeaveRequests'])->name('getLeaveRequests');
        Route::post('uploadLeaveDocument', [LeaveController::class, 'uploadLeaveDocument'])->name('uploadLeaveDocument');
        Route::post('cancelLeaveRequest', [LeaveController::class, 'cancelLeaveRequest'])->name('cancelLeaveRequest');

        //Expense
        Route::group(['prefix' => 'expense'], function () {
          Route::get('getExpenseTypes ', [ExpenseController::class, 'getExpenseTypes'])->name('getExpenseTypes');
          Route::post('createExpenseRequest', [ExpenseController::class, 'createExpenseRequest'])->name('createExpenseRequest');
          Route::get('getExpenseRequests', [ExpenseController::class, 'getExpenseRequests'])->name('getExpenseRequests');
          Route::post('uploadExpenseDocument', [ExpenseController::class, 'uploadExpenseDocument'])->name('uploadExpenseDocument');
          Route::post('cancel', [ExpenseController::class, 'cancel'])->name('cancel');
        });

        //Client
        Route::group(['prefix' => 'client'], function () {
          Route::get('getAllClients ', [ClientController::class, 'getAllClients'])->name('getAllClients');
          Route::post('create', [ClientController::class, 'create'])->name('create');
          Route::get('search', [ClientController::class, 'search'])->name('search');
        });

        //Visit
        Route::group(['prefix' => 'visit'], function () {
          Route::post('create ', [VisitController::class, 'create'])->name('create');
          Route::get('getVisitsCount', [VisitController::class, 'getVisitsCount'])->name('getVisitsCount');
          Route::get('getHistory', [VisitController::class, 'getHistory'])->name('getHistory');
        });

        //SOS
        Route::group(['prefix', 'sos'], function () {
          Route::post('sendSOS', [SOSController::class, 'create'])->name('sendSOS');
          Route::get('getAllSOS', [SOSController::class, 'getAll'])->name('getAllSOS');
        });

        //User
        Route::group(['prefix', 'user'], function () {
          Route::get('user/search/{query}', [UserController::class, 'searchUsers'])->name('searchUsers');
          Route::get('user/getAll', [UserController::class, 'getUsersList'])->name('getAllUsers');
          Route::get('userStatus', [UserController::class, 'getUserStatus'])->name('getUserStatus');
          Route::get('user/{id}', [UserController::class, 'getUserInfo'])->name('getUserInfo');
          Route::post('user/updateStatus', [UserController::class, 'updateUserStatus'])->name('updateUserStatus');
        });

        //Holidays
        Route::group(['prefix' => 'holidays'], function () {
          Route::get('getAll', [HolidayController::class, 'getAll'])->name('getAll');
        });

        //Notification
        Route::group(['prefix' => 'notification'], function () {
          Route::get('getAll', [NotificationController::class, 'getAll'])->name('getAll');
          Route::post('markAsRead/{id}', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        });

        // Attendance Requests
        Route::middleware('auth:api')->group(function () {
          Route::get('/attendance-requests', [AttendanceRequestController::class, 'getAll']);
          Route::post('/attendance-requests', [AttendanceRequestController::class, 'store']);
        });
      });
    });
  });
});
