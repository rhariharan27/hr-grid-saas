
<?php



use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuperAdmin\NotificationController;
use App\Http\Controllers\tenant\AttendanceController;
use App\Http\Controllers\tenant\ClientController;
use App\Http\Controllers\tenant\DashboardController;
use App\Http\Controllers\tenant\DepartmentsController;
use App\Http\Controllers\tenant\DesignationController;
use App\Http\Controllers\tenant\DeviceController;
use App\Http\Controllers\tenant\EmployeeController;
use App\Http\Controllers\tenant\ExpenseController;
use App\Http\Controllers\tenant\ExpenseTypeController;
use App\Http\Controllers\tenant\HolidayController;
use App\Http\Controllers\tenant\LeaveController;
use App\Http\Controllers\tenant\LeaveTypeController;
use App\Http\Controllers\tenant\OrganisationHierarchyController;
use App\Http\Controllers\tenant\PermissionController;
use App\Http\Controllers\tenant\ReportController;
use App\Http\Controllers\tenant\SettingsController;
use App\Http\Controllers\tenant\ShiftController;
use App\Http\Controllers\tenant\SOSController;
use App\Http\Controllers\tenant\TeamController;
use App\Http\Controllers\tenant\VisitController;
use App\Services\AddonService\IAddonService;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Admin\AttendanceRequestController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
*/

require __DIR__ . '/user.php';

Route::middleware([
  'web',
  InitializeTenancyByDomain::class,
  PreventAccessFromCentralDomains::class,
])->group(function () {


  Route::get('/auth/login', [AuthController::class, 'login'])->name('auth.login');
  Route::post('/auth/login', [AuthController::class, 'loginPost'])->name('auth.loginPost');
  Route::get('/accessDenied', [BaseController::class, 'accessDenied'])->name('accessDenied');

  Route::middleware([
    'web',
    'auth',
    'role:admin|hr'
  ])->group(callback: function () {

    Route::group(['middleware' => function ($request, $next) {
      $addonService = app(IAddonService::class);
      if (!$addonService->isAddonEnabled(ModuleConstants::SOS, true)) {
        return redirect()->route('accessDenied')->with('error', 'You do not have permission to access this page');
      }
      return $next($request);
    }], function () {
      Route::get('/sos', [SOSController::class, 'index'])->name('sos.index');
      Route::get('/sos/fetch', [SOSController::class, 'fetchSOSRequests'])->name('sos.fetch');
      Route::post('/sos/resolve/{id}', [SOSController::class, 'markAsResolved'])->name('sos.resolve');
    });

    Route::post('markAsRead', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/myNotifications', [NotificationController::class, 'myNotifications'])->name('notifications.myNotifications');
    Route::get('getNotificationsAjax', [NotificationController::class, 'getNotificationsAjax'])->name('notifications.getNotificationsAjax');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
    Route::get('liveLocation', [DashboardController::class, 'liveLocationView'])->name('liveLocationView');
    Route::get('liveLocationAjax', [DashboardController::class, 'liveLocationAjax'])->name('liveLocationAjax');
    Route::get('cardView', [DashboardController::class, 'cardView'])->name('cardView');
    Route::get('cardViewAjax', [DashboardController::class, 'cardViewAjax'])->name('cardViewAjax');
    Route::get('timeline', [DashboardController::class, 'timelineView'])->name('timelineView');
    Route::post('getTimeLineAjax', [DashboardController::class, 'getTimeLineAjax'])->name('getTimeLineAjax');
    Route::get('getRecentActivities', [DashboardController::class, 'getRecentActivities'])->name('getRecentActivities');


    Route::get('getAttendanceLogAjax/{userId}/{date}', [DashboardController::class, 'getAttendanceLogAjax'])->name('getAttendanceLogAjax');
    Route::get('getStatsForTimeLineAjax/{userId}/{date}/{attendanceLogId}', [DashboardController::class, 'getStatsForTimeLineAjax'])->name('getStatsForTimeLineAjax');
    Route::get('getActivityAjax/{userId}/{date}/{attendanceLogId}', [DashboardController::class, 'getActivityAjax'])->name('getActivityAjax');
    Route::get('getDeviceLocationAjax/{userId}/{date}/{attendanceLogId}', [DashboardController::class, 'getDeviceLocationAjax'])->name('getDeviceLocationAjax');
    Route::get('getDepartmentPerformanceAjax', [DashboardController::class, 'getDepartmentPerformanceAjax'])->name('getDepartmentPerformanceAjax');

    Route::middleware(['role:admin'])->name('settings.')->group(function () {
      Route::get('', [SettingsController::class, 'index'])->name('index');
      Route::post('updateGeneralSettings', [SettingsController::class, 'updateGeneralSettings'])->name('updateGeneralSettings');
      Route::post('updateCompanySettings', [SettingsController::class, 'updateCompanySettings'])->name('updateCompanySettings');
      Route::post('updateAppSettings', [SettingsController::class, 'updateAppSettings'])->name('updateAppSettings');
      Route::post('updateTrackingSettings', [SettingsController::class, 'updateTrackingSettings'])->name('updateTrackingSettings');
      Route::post('updateMapSettings', [SettingsController::class, 'updateMapSettings'])->name('updateMapSettings');
      Route::post('updateEmployeeSettings', [SettingsController::class, 'updateEmployeeSettings'])->name('updateEmployeeSettings');
      Route::post('updatePayrollSettings', [SettingsController::class, 'updatePayrollSettings'])->name('updatePayrollSettings');
      Route::post('updateAiSettings', [SettingsController::class, 'updateAiSettings'])->name('updateAiSettings');

      //Payroll
      Route::post('addOrUpdatePayrollAdjustment', [SettingsController::class, 'addOrUpdatePayrollAdjustment'])->name('addOrUpdatePayrollAdjustment');
      Route::delete('deletePayrollAdjustment/{id}', [SettingsController::class, 'deletePayrollAdjustment'])->name('deletePayrollAdjustment');
    });

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('report/getAttendanceReport', [ReportController::class, 'getAttendanceReport'])->name('report.getAttendanceReport');
    Route::post('report/getVisitReport', [ReportController::class, 'getVisitReport'])->name('report.getVisitReport');
    Route::post('report/getLeaveReport', [ReportController::class, 'getLeaveReport'])->name('report.getLeaveReport');
    Route::post('report/getExpenseReport', [ReportController::class, 'getExpenseReport'])->name('report.getExpenseReport');
    Route::post('reports/getProductOrderReport', [ReportController::class, 'getProductOrderReport'])->name('report.getProductOrderReport');


    Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index');

    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');

    Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');

    Route::prefix('attendance/')->name('attendance.')->group(function () {
      Route::get('', [AttendanceController::class, 'index'])->name('index');
      Route::get('indexAjax', [AttendanceController::class, 'indexAjax'])->name('indexAjax');
    });

    Route::get('visits', [VisitController::class, 'index'])->name('visits.index');

    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

    Route::get('/lang/{locale}', [LanguageController::class, 'swap']);


    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles/addOrUpdateAjax', [RoleController::class, 'addOrUpdateAjax'])->name('roles.addOrUpdateAjax');
    Route::delete('roles/deleteAjax/{id}', [RoleController::class, 'deleteAjax'])->name('roles.deleteAjax');

    //Search Routes
    Route::get('/getSearchDataAjax', [BaseController::class, 'getSearchDataAjax'])->name('search.Ajax');

    Route::prefix('holidays/')->name('holidays.')->group(function () {
      Route::get('', [HolidayController::class, 'index'])->name('index');
      Route::get('indexAjax', [HolidayController::class, 'indexAjax'])->name('indexAjax');
      Route::post('addOrUpdateHolidayAjax', [HolidayController::class, 'addOrUpdateHolidayAjax'])->name('addOrUpdateHolidayAjax');
      Route::get('getByIdAjax/{id}', [HolidayController::class, 'getByIdAjax'])->name('getByIdAjax');
      Route::delete('deleteAjax/{id}', [HolidayController::class, 'deleteAjax'])->name('deleteAjax');
      Route::post('changeStatusAjax/{id}', [HolidayController::class, 'changeStatusAjax'])->name('changeStatusAjax');
    });

    Route::prefix('employees/')->name('employees.')->group(function () {
      Route::get('', [EmployeeController::class, 'index'])->name('index');
      Route::get('view/{id}', [EmployeeController::class, 'show'])->name('show');
      Route::get('indexAjax', [EmployeeController::class, 'getListAjax'])->name('indexAjax');
      Route::get('create', [EmployeeController::class, 'create'])->name('create');
      Route::get('getNewEmployeeCode/{locationId}', [EmployeeController::class, 'GetNewEmployeeCodeByLocationAjax'])->name('getNewEmployeeCode');
      Route::get('checkEmailValidationAjax', [EmployeeController::class, 'checkEmailValidationAjax'])->name('checkEmailValidationAjax');
      Route::get('checkPhoneValidationAjax', [EmployeeController::class, 'checkPhoneValidationAjax'])->name('checkPhoneValidationAjax');
      Route::get('checkEmployeeCodeValidationAjax', [EmployeeController::class, 'checkEmployeeCodeValidationAjax'])->name('checkEmployeeCodeValidationAjax');
      Route::delete('deleteEmployeeAjax/{id}', [EmployeeController::class, 'deleteEmployeeAjax'])->name('deleteEmployeeAjax');
      Route::post('store', [EmployeeController::class, 'store'])->name('store');

      Route::post('changeEmployeeProfilePicture', [EmployeeController::class, 'changeEmployeeProfilePicture'])->name('changeEmployeeProfilePicture');
      Route::post('addHrLocation', [EmployeeController::class, 'addHrLocation'])->name('addHrLocation');
      Route::delete('deleteHrLocation/{id}', [EmployeeController::class, 'deleteHrLocation'])->name('deleteHrLocation');
      Route::post('addOrUpdateBankAccount', [EmployeeController::class, 'addOrUpdateBankAccount'])->name('addOrUpdateBankAccount');
      Route::post('addOrUpdateLeaveCount', [EmployeeController::class, 'addOrUpdateLeaveCount'])->name('addOrUpdateLeaveCount');
      Route::post('addOrUpdateDocument', [EmployeeController::class, 'addOrUpdateDocument'])->name('addOrUpdateDocument');
      Route::get('getUserDocumentsAjax/{userId}', [EmployeeController::class, 'getUserDocumentsAjax'])->name('getUserDocumentsAjax');
      Route::get('downloadUserDocument/{userDocumentId}', [EmployeeController::class, 'downloadUserDocument'])->name('downloadUserDocument');
      Route::post('updateBasicInfo', [EmployeeController::class, 'updateBasicInfo'])->name('updateBasicInfo');
      Route::post('updateCompensationInfo', [EmployeeController::class, 'updateCompensationInfo'])->name('updateCompensationInfo');
      Route::post('addOrUpdateBankAccount', [EmployeeController::class, 'addOrUpdateBankAccount'])->name('addOrUpdateBankAccount');
      Route::post('updateWorkInformation', [EmployeeController::class, 'updateWorkInformation'])->name('updateWorkInformation');
      Route::post('updateEmergencyContactInfo', [EmployeeController::class, 'updateEmergencyContactInfo'])->name('updateEmergencyContactInfo');

      Route::post('addOrUpdatePayrollAdjustment', [EmployeeController::class, 'addOrUpdatePayrollAdjustment'])->name('addOrUpdatePayrollAdjustment');
      Route::delete('deletePayrollAdjustment/{id}', [EmployeeController::class, 'deletePayrollAdjustment'])->name('deletePayrollAdjustment');
      Route::get('getPayrollAdjustmentAjax/{id}', [EmployeeController::class, 'getPayrollAdjustmentAjax'])->name('getPayrollAdjustmentAjax');

      Route::get('getReportingToUsersAjax', [EmployeeController::class, 'getReportingToUsersAjax'])->name('getReportingToUsersAjax');
      Route::post('removeDevice', [EmployeeController::class, 'removeDevice'])->name('removeDevice');

      //Sales targets
      Route::post('addOrUpdateSalesTarget', [EmployeeController::class, 'addOrUpdateSalesTarget'])->name('addOrUpdateSalesTarget');
      Route::delete('destroySalesTarget/{id}', [EmployeeController::class, 'destroySalesTarget'])->name('destroySalesTarget');
      Route::get('getTargetByIdAjax/{id}', [EmployeeController::class, 'getTargetByIdAjax'])->name('getTargetByIdAjax');

      Route::post('toggleStatus/{id}', [EmployeeController::class, 'toggleStatus'])->name('employees.toggleStatus');
      Route::post('relieve/{id}', [EmployeeController::class, 'relieveEmployee'])->name('employees.relieve');
      Route::post('retire/{id}', [EmployeeController::class, 'retireEmployee'])->name('employees.retire');

      Route::post('/{user}/terminate', [EmployeeController::class, 'initiateTermination'])->name('terminate');
      Route::post('/{user}/confirmProbation', [EmployeeController::class, 'confirmProbation'])->name('confirmProbation');
      Route::post('/{user}/extendProbation', [EmployeeController::class, 'extendProbation'])->name('extendProbation');
      Route::post('/{user}/failProbation', [EmployeeController::class, 'failProbation'])->name('failProbation');
    });


    Route::prefix('account/')->name('account.')->group(function () {
      Route::get('/', [AccountController::class, 'index'])->name('index');
      Route::get('activeInactiveUserAjax/{id}', [AccountController::class, 'activeInactiveUserAjax'])->name('activeInactiveUserAjax');
      Route::get('suspendUserAjax/{id}', [AccountController::class, 'suspendUserAjax'])->name('suspendUserAjax');
      Route::get('deleteUserAjax/{id}', [AccountController::class, 'deleteUserAjax'])->name('deleteUserAjax');
      Route::get('viewUser/{id}', [AccountController::class, 'viewUser'])->name('viewUser');
      Route::get('indexAjax', [AccountController::class, 'userListAjax'])->name('userListAjax');
      Route::delete('deleteUserAjax/{id}', [AccountController::class, 'deleteUserAjax'])->name('deleteUserAjax');
      Route::get('getRolesAjax', [AccountController::class, 'getRolesAjax'])->name('getRolesAjax');
      Route::get('getUsersAjax', [AccountController::class, 'getUsersAjax'])->name('getUsersAjax');
      Route::get('getUsersByRoleAjax/{role}', [AccountController::class, 'getUsersByRoleAjax'])->name('getUsersByRoleAjax');
      Route::post('addOrUpdateUserAjax', [AccountController::class, 'addOrUpdateUserAjax'])->name('addOrUpdateUserAjax');
      Route::get('editUserAjax/{id}', [AccountController::class, 'editUserAjax'])->name('editUserAjax');
      Route::post('updateUserAjax/{id}', [AccountController::class, 'updateUserAjax'])->name('updateUserAjax');
      Route::post('updateUserStatusAjax/{id}', [AccountController::class, 'updateUserStatusAjax'])->name('updateUserStatusAjax');
      Route::post('changeUserStatusAjax/{id}', [AccountController::class, 'changeUserStatusAjax'])->name('changeUserStatusAjax');
      Route::post('changePassword', [AccountController::class, 'changePassword'])->name('changePassword');
    });

    //Audit Logs
    Route::prefix('auditLogs/')->name('auditLogs.')->group(function () {
      Route::get('/', [AuditLogController::class, 'index'])->name('index');
      Route::get('show/{id}', [AuditLogController::class, 'show'])->name('show');
    });

    //Leave Types
    Route::prefix('leaveTypes/')->name('leaveTypes.')->group(function () {
      Route::get('', [LeaveTypeController::class, 'index'])->name('index');
      Route::get('getLeaveTypesAjax', [LeaveTypeController::class, 'getLeaveTypesAjax'])->name('getLeaveTypesAjax');
      Route::post('addOrUpdateLeaveTypeAjax', [LeaveTypeController::class, 'addOrUpdateLeaveTypeAjax'])->name('addOrUpdateLeaveTypeAjax');
      Route::get('getLeaveTypeAjax/{id}', [LeaveTypeController::class, 'getLeaveTypeAjax'])->name('getLeaveTypeAjax');
      Route::get('getCodeAjax', [LeaveTypeController::class, 'getCodeAjax'])->name('getCodeAjax');
      Route::delete('deleteLeaveTypeAjax/{id}', [LeaveTypeController::class, 'deleteLeaveTypeAjax'])->name('deleteLeaveTypeAjax');
      Route::post('changeStatus/{id}', [LeaveTypeController::class, 'changeStatus'])->name('changeStatus');
      Route::get('checkCodeValidationAjax', [LeaveTypeController::class, 'checkCodeValidationAjax'])->name('checkCodeValidationAjax');
    });


    //Expense Types
    Route::prefix('expenseTypes/')->name('expenseTypes.')->group(function () {
      Route::get('/', [ExpenseTypeController::class, 'index'])->name('index');
      Route::get('getExpenseTypesListAjax', [ExpenseTypeController::class, 'getExpenseTypesListAjax'])->name('getExpenseTypesListAjax');
      Route::post('addOrUpdateExpenseTypeAjax', [ExpenseTypeController::class, 'addOrUpdateExpenseTypeAjax'])->name('addOrUpdateAjax');
      Route::get('getExpenseTypeAjax/{id}', [ExpenseTypeController::class, 'getExpenseTypeAjax'])->name('getExpenseTypeAjax');
      Route::delete('deleteExpenseTypeAjax/{id}', [ExpenseTypeController::class, 'deleteExpenseTypeAjax'])->name('deleteExpenseTypeAjax');
      Route::post('changeStatus/{id}', [ExpenseTypeController::class, 'changeStatus'])->name('changeStatus');
      Route::get('getCodeAjax', [ExpenseTypeController::class, 'getCodeAjax'])->name('getCodeAjax');
      Route::get('view/{id}', [ExpenseTypeController::class, 'view'])->name('view');
      Route::post('addOrUpdateRule', [ExpenseTypeController::class, 'addOrUpdateRule'])->name('addOrUpdateRule');
      Route::delete('deleteRule/{id}', [ExpenseTypeController::class, 'deleteRule'])->name('deleteRule');
      Route::get('checkCodeValidationAjax', [ExpenseTypeController::class, 'checkCodeValidationAjax'])->name('checkCodeValidationAjax');
    });


    //Teams
    Route::prefix('teams/')->name('teams.')->group(function () {
      Route::get('', [TeamController::class, 'index'])->name('index');
      Route::get('getTeamsListAjax', [TeamController::class, 'getTeamsListAjax'])->name('getTeamsListAjax');
      Route::post('addOrUpdateTeamAjax', [TeamController::class, 'addOrUpdateTeamAjax'])->name('addOrUpdateTeamAjax');
      Route::get('getTeamAjax/{id}', [TeamController::class, 'getTeamAjax'])->name('getTeamAjax');
      Route::delete('deleteTeamAjax/{id}', [TeamController::class, 'deleteTeamAjax'])->name('deleteTeamAjax');
      Route::post('changeStatus/{id}', [TeamController::class, 'changeStatus'])->name('changeStatus');
      Route::get('getCodeAjax', [TeamController::class, 'getCodeAjax'])->name('getCodeAjax');
      Route::get('getTeamListAjax', [TeamController::class, 'getTeamListAjax'])->name('getTeamListAjax');
      Route::get('checkCodeValidationAjax', [TeamController::class, 'checkCodeValidationAjax'])->name('checkCodeValidationAjax');
    });


    //Shifts
    Route::prefix('shifts')->name('shifts.')->group(function () {
      Route::get('/', [ShiftController::class, 'index'])->name('index');
      Route::get('/list', [ShiftController::class, 'listAjax'])->name('listAjax'); // DataTables GET source
      Route::post('/', [ShiftController::class, 'store'])->name('store'); // Create POST
      Route::get('/{shift}/edit', [ShiftController::class, 'edit'])->name('edit'); // Fetch data for edit GET
      Route::put('/{shift}', [ShiftController::class, 'update'])->name('update'); // Update PUT
      Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy'); // Delete DELETE
      Route::post('/{shift}/toggle-status', [ShiftController::class, 'toggleStatus'])->name('toggleStatus'); // Use POST for toggle action
      Route::get('/getActiveShiftsForDropdown', [ShiftController::class, 'getActiveShiftsForDropdown'])->name('getActiveShiftsForDropdown');
    });

    //Visits
    Route::group(['prefix' => 'visits'], function () {
      Route::get('/', [VisitController::class, 'index'])->name('visits.index');
      Route::get('/getListAjax', [VisitController::class, 'getListAjax'])->name('visits.getListAjax');
      Route::delete('/deleteVisitAjax/{id}', [VisitController::class, 'deleteVisitAjax'])->name('visits.deleteVisitAjax');
      Route::get('/getByIdAjax/{id}', [VisitController::class, 'getByIdAjax'])->name('visits.getByIdAjax');
    });

    //Leave Requests
    Route::group(['prefix' => 'leaveRequests'], function () {
      Route::get('/', [LeaveController::class, 'index'])->name('leaveRequests.index');
      Route::get('/getListAjax', [LeaveController::class, 'getListAjax'])->name('leaveRequests.getListAjax');
      Route::post('/actionAjax', [LeaveController::class, 'actionAjax'])->name('leaveRequests.actionAjax');
      Route::get('/getByIdAjax/{id}', [LeaveController::class, 'getByIdAjax'])->name('leaveRequests.getByIdAjax');
    });

    //Clients
    Route::prefix('clients/')->name('client.')->group(function () {
      Route::get('', [ClientController::class, 'index'])->name('index');
      Route::get('show/{id}', [ClientController::class, 'show'])->name('show');
      Route::get('create', [ClientController::class, 'create'])->name('create');
      Route::post('store', [ClientController::class, 'store'])->name('store');
      Route::get('edit/{id}', [Clientcontroller::class, 'edit'])->name('edit');
      Route::post('update/{id}', [Clientcontroller::class, 'update'])->name('update');
      Route::post('changeStatus', [Clientcontroller::class, 'changeStatus'])->name('changeStatus');
      Route::delete('destroy/{id}', [Clientcontroller::class, 'destroy'])->name('destroy');
    });

    //Employees
    Route::get('employee/getGeofenceGroups', [EmployeeController::class, 'getGeofenceGroups'])->name('employee.getGeofenceGroups');
    Route::get('employee/getIpGroups', [EmployeeController::class, 'getIpGroups'])->name('employee.getIpGroups');
    Route::get('employee/getQrGroups', [EmployeeController::class, 'getQrGroups'])->name('employee.getQrGroups');
    Route::get('employee/getSites', [EmployeeController::class, 'getSites'])->name('employee.getSites');
    Route::get('employee/getDynamicQrDevices', [EmployeeController::class, 'getDynamicQrDevices'])->name('employee.getDynamicQrDevices');

    Route::get('employee/myProfile', [EmployeeController::class, 'myProfile'])->name('employee.myProfile');
  });

  //Expense Requests
  Route::group(['prefix' => 'expenseRequests'], function () {
    Route::get('/', [ExpenseController::class, 'index'])->name('expenseRequests.index');
    Route::get('/indexAjax', [ExpenseController::class, 'indexAjax'])->name('expenseRequests.indexAjax');
    Route::get('/getByIdAjax/{id}', [ExpenseController::class, 'getByIdAjax'])->name('expenseRequests.getByIdAjax');
    Route::post('/actionAjax', [ExpenseController::class, 'actionAjax'])->name('expenseRequests.actionAjax');
  });

  //Departments
  Route::group(['prefix' => 'departments'], function () {
    Route::get('/', [DepartmentsController::class, 'index'])->name('departments.index');
    Route::get('/indexAjax', [DepartmentsController::class, 'indexAjax'])->name('departments.indexAjax');
    Route::post('/addOrUpdateDepartmentAjax', [DepartmentsController::class, 'addOrUpdateDepartmentAjax'])->name('departments.addOrUpdateDepartmentAjax');
    Route::get('/getListAjax', [DepartmentsController::class, 'getListAjax'])->name('departments.getListAjax');
    Route::get('/getParentDepartments', [DepartmentsController::class, 'getParentDepartments'])->name('departments.getParentDepartments');
    Route::get('/getDepartmentAjax/{id}', [DepartmentsController::class, 'getDepartmentAjax'])->name('departments.getDepartmentAjax');
    Route::delete('/deleteAjax/{id}', [DepartmentsController::class, 'deleteAjax'])->name('departments.deleteAjax');
    Route::post('/changeStatus/{id}', [DepartmentsController::class, 'changeStatus'])->name('departments.changeStatus');
  });

  //Designations
  Route::group(['prefix' => 'designations'], function () {
    Route::get('/', [DesignationController::class, 'index'])->name('designations.index');
    Route::get('/indexAjax', [DesignationController::class, 'indexAjax'])->name('designations.indexAjax');
    Route::get('/getDesignationListAjax', [DesignationController::class, 'getDesignationListAjax'])->name('getDesignationListAjax');
    Route::post('/addOrUpdateAjax', [DesignationController::class, 'addOrUpdateAjax'])->name('designations.addOrUpdateAjax');
    Route::get('/getByIdAjax/{id}', [DesignationController::class, 'getByIdAjax'])->name('designations.getByIdAjax');
    Route::delete('/deleteAjax/{id}', [DesignationController::class, 'deleteAjax'])->name('designations.deleteAjax');
    Route::post('/changeStatus/{id}', [DesignationController::class, 'changeStatus'])->name('designations.changeStatus');
    Route::get('/checkCodeValidationAjax', [DesignationController::class, 'checkCodeValidationAjax'])->name('designations.checkCodeValidationAjax');
  });

  // Device Status
  Route::group(['prefix' => 'device'], function () {
    Route::get('/', [DeviceController::class, 'index'])->name('device.index');
    Route::get('/indexAjax', [DeviceController::class, 'indexAjax'])->name('device.indexAjax');
    Route::get('/getByIdAjax/{id}', [DeviceController::class, 'getByIdAjax'])->name('device.getByIdAjax');
    Route::delete('/deleteAjax/{id}', [DeviceController::class, 'deleteAjax'])->name('device.deleteAjax');
  });

  //Organization Hierarchy
  Route::group(['prefix' => 'organizationHierarchy'], function () {
    Route::get('/', [OrganisationHierarchyController::class, 'index'])->name('organizationHierarchy.index');
  });


  Route::get('/tenant', function () {
    return response()->json([
      'message' => 'This is your multi-tenant application. The id of the current tenant is ',
      'tenant_id' => tenant('id'),
    ]);
  });


  Route::middleware(['auth:web'])->get('/attendance-requests/indexAjax', [AttendanceRequestController::class, 'indexAjax'])->name('admin.attendance_requests.indexAjax');
  Route::middleware(['auth:web'])->post('/attendance-requests/{id}/approve', [AttendanceRequestController::class, 'approve']);

  Route::middleware(['auth:web'])->get('/attendance-requests', function () {
    $users = \App\Models\User::orderBy('first_name')->get();
    return view('admin.attendance_requests', compact('users'));
  })->name('admin.attendance_requests.index');
});
