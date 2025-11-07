<?php

namespace App\Http\Controllers\tenant;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\LeaveRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\Leave\LeaveRequestApproval;
use Constants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LeaveController extends Controller
{
  public function index()
  {
    $employees = User::all();
    $leaveTypes = LeaveType::all();
    return view('tenant.leave.index', compact('employees'), compact('leaveTypes'));
  }


  public function getListAjax(Request $request)
  {

    try {

      $columns = [
        1 => 'id',
        2 => 'user',
        3 => 'leaveType',
        4 => 'leaveDate',
        5 => 'status',
        6 => 'image',
      ];

      $query = LeaveRequest::query();

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      if ($request->has('status') && !empty($request->input('status'))) {
        $query->where('status', $request->input('status'));
      }

      if ($request->has('employeeFilter') && !empty($request->input('employeeFilter'))) {
        $query->where('user_id', $request->input('employeeFilter'));
      }

      if ($request->has('leaveTypeFilter') && !empty($request->input('leaveTypeFilter'))) {
        $query->where('leave_type_id', $request->input('leaveTypeFilter'));
      }

      if ($request->has('dateFilter') && !empty($request->input('dateFilter'))) {
        $dateFilter = $request->input('dateFilter');
        $query->whereDate('leave_requests.created_at', $dateFilter);
      }

      if ($request->has('statusFilter') && !empty($request->input('statusFilter'))) {
        $query->where('leave_requests.status', LeaveRequestStatus::from($request->input('statusFilter')));
      }


      $totalData = $query->count();


      if ($order == 'id') {
        $order = 'leave_requests.id';
        $query->orderBy($order, $dir);
      }


      if ($request->has('dateFilter') && !empty($request->input('dateFilter'))) {
        $dateFilter = $request->input('dateFilter');
        Log::info('Date Filter: ' . $dateFilter);
        $query->whereDate('leave_requests.created_at', $dateFilter);
      }

      if (empty($request->input('search.value'))) {
        $leaveRequests = $query->select('leave_requests.*', 'user.first_name', 'user.last_name', 'user.code', 'user.profile_picture', 'leave_type.name as leave_type_name')
          ->leftJoin('users as user', 'leave_requests.user_id', '=', 'user.id')
          ->leftJoin('leave_types as leave_type', 'leave_requests.leave_type_id', '=', 'leave_type.id')
          ->offset($start)
          ->limit($limit)
          ->get();
      } else {
        $search = $request->input('search.value');
        $leaveRequests = $query->select('leave_requests.*', 'user.first_name', 'user.last_name', 'user.code', 'user.profile_picture', 'leave_type.name as leave_type_name')
          ->leftJoin('users as user', 'leave_requests.user_id', '=', 'user.id')
          ->leftJoin('leave_types as leave_type', 'leave_requests.leave_type_id', '=', 'leave_type.id')
          ->where(function ($query) use ($search) {
            $query->where('leave_requests.id', 'LIKE', "%{$search}%")
              ->orWhere('leave_requests.user_id', 'LIKE', "%{$search}%")
              ->orWhere('user.first_name', 'LIKE', "%{$search}%")
              ->orWhere('user.last_name', 'LIKE', "%{$search}%")
              ->orWhere('user.code', 'LIKE', "%{$search}%")
              ->orWhere('leave_type.name', 'LIKE', "%{$search}%");
          })
          ->offset($start)
          ->limit($limit)
          ->get();
      }

      $totalFiltered = $query->count();

      $data = [];
      if (!empty($leaveRequests)) {
        foreach ($leaveRequests as $leaveRequest) {
          $nestedData['id'] = $leaveRequest->id;
          $nestedData['user_id'] = $leaveRequest->user_id;
          $nestedData['from_date'] = $leaveRequest->from_date->format(Constants::DateFormat);
          $nestedData['to_date'] = $leaveRequest->to_date->format(Constants::DateFormat);
          $nestedData['leave_type'] = $leaveRequest->leave_type_name;
          $nestedData['document'] = $leaveRequest->document != null ? tenant_asset(Constants::BaseFolderLeaveRequestDocument . $leaveRequest->document) : null;
          $nestedData['created_at'] = $leaveRequest->created_at->format(Constants::DateTimeFormat);
          $nestedData['status'] = $leaveRequest->status;

          $nestedData['user_name'] = $leaveRequest->user->getFullName();
          $nestedData['user_code'] = $leaveRequest->user->code;
          $nestedData['user_profile_image'] = $leaveRequest->user->profile_picture != null ? tenant_asset(Constants::BaseFolderEmployeeProfileWithSlash . $leaveRequest->user->profile_picture) : null;
          $nestedData['user_initial'] = $leaveRequest->user->getInitials();

          $data[] = $nestedData;
        }
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => $totalData,
        'recordsFiltered' => $totalFiltered,
        'data' => $data,
      ]);
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return Error::response('Something went wrong. Please try again.');
    }
  }

  public function actionAjax(Request $request)
  {

    $validated = $request->validate([
      'id' => 'required|exists:leave_requests,id',
      'status' => 'required|in:approved,rejected,cancelled',
      'adminNotes' => 'nullable|string',
    ]);

    try {

      $leaveRequest = LeaveRequest::findOrFail($validated['id']);
      $leaveRequest->status = $validated['status'];

      if ($validated['status'] == LeaveRequestStatus::CANCELLED) {
        $leaveRequest->cancel_reason = $validated['adminNotes'] ?? null;
      } else {
        $leaveRequest->approval_notes = $validated['adminNotes'] ?? null;
      }

      $leaveRequest->save();

      Notification::send($leaveRequest->user, new LeaveRequestApproval($leaveRequest, $validated['status']));

      return back()->with('success', 'Leave request ' . $validated['status'] . ' successfully.');
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return back()->with('error', 'Something went wrong. Please try again.');
    }
  }

  public function getByIdAjax($id)
  {
    $leaveRequest = LeaveRequest::findOrFail($id);

    if (!$leaveRequest) {
      return Error::response('Leave request not found');
    }

    $response = [
      'id' => $leaveRequest->id,
      'userName' => $leaveRequest->user->getFullName(),
      'userCode' => $leaveRequest->user->code,
      'leaveType' => $leaveRequest->leaveType->name,
      'fromDate' => $leaveRequest->from_date->format(Constants::DateFormat),
      'toDate' => $leaveRequest->to_date->format(Constants::DateFormat),
      'document' => $leaveRequest->document != null ? tenant_asset(Constants::BaseFolderLeaveRequestDocument . $leaveRequest->document) : null,
      'status' => $leaveRequest->status,
      'createdAt' => $leaveRequest->created_at->format(Constants::DateTimeFormat),
      'userNotes' => $leaveRequest->user_notes
    ];

    return Success::response($response);
  }
}
