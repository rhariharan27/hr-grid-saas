<?php

namespace App\Http\Controllers\tenant;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\ExpenseType;
use App\Models\User;
use App\Notifications\Expense\ExpenseRequestApproval;
use Constants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ExpenseController extends Controller
{
  public function index()
  {
    $employees = User::all();
    $expenseTypes = ExpenseType::all();
    return view('tenant.expenses.index', compact('employees'), compact('expenseTypes'));
  }

  public function indexAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'user',
        3 => 'expenseType',
        4 => 'expenseDate',
        5 => 'amount',
        6 => 'status',
        7 => 'image',
      ];

      $query = ExpenseRequest::query();

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');


      if ($request->has('employeeFilter') && !empty($request->input('employeeFilter'))) {
        $query->where('user_id', $request->input('employeeFilter'));
      }

      if ($request->has('dateFilter') && !empty($request->input('dateFilter'))) {
        $query->whereDate('for_date', $request->input('dateFilter'));
      }

      if ($request->has('expenseTypeFilter') && !empty($request->input('expenseTypeFilter'))) {
        $query->where('expense_type_id', $request->input('expenseTypeFilter'));
      }

      if ($request->has('statusFilter') && !empty($request->input('statusFilter'))) {
        $query->where('expense_requests.status', $request->input('statusFilter'));
      }


      $totalData = $query->count();


      if ($order == 'id') {
        $order = 'expense_requests.id';
        $query->orderBy($order, $dir);
      }


      if (empty($request->input('search.value'))) {
        $expenseRequests = $query->select(
          'expense_requests.*',
          'user.first_name',
          'user.last_name',
          'user.code',
          'user.profile_picture',
          'expense_type.name as expense_type_name',
        )
          ->leftJoin('users as user', 'expense_requests.user_id', '=', 'user.id')
          ->leftJoin('expense_types as expense_type', 'expense_requests.expense_type_id', '=', 'expense_type.id')
          ->offset($start)
          ->limit($limit)
          ->get();
      } else {
        $search = $request->input('search.value');
        $expenseRequests = $query->select(
          'expense_requests.*',
          'user.first_name',
          'user.last_name',
          'user.code',
          'user.profile_picture',
          'expense_type.name as expense_type_name',
        )
          ->leftJoin('users as user', 'expense_requests.user_id', '=', 'user.id')
          ->leftJoin('expense_types as expense_type', 'expense_requests.expense_type_id', '=', 'expense_type.id')
          ->where(function ($query) use ($search) {
            $query->where('expense_requests.id', 'LIKE', "%{$search}%")
              ->orWhere('expense_requests.user_id', 'LIKE', "%{$search}%")
              ->orWhere('user.first_name', 'LIKE', "%{$search}%")
              ->orWhere('user.last_name', 'LIKE', "%{$search}%")
              ->orWhere('user.code', 'LIKE', "%{$search}%")
              ->orWhere('expense_type.name', 'LIKE', "%{$search}%");
          })
          ->offset($start)
          ->limit($limit)
          ->get();
      }

      $totalFiltered = $query->count();

      $data = [];
      if (!empty($expenseRequests)) {
        foreach ($expenseRequests as $expenseRequest) {
          $nestedData['id'] = $expenseRequest->id;
          $nestedData['user_id'] = $expenseRequest->user_id;
          $nestedData['for_date'] = $expenseRequest->for_date->format(Constants::DateFormat);
          $nestedData['expense_type'] = $expenseRequest->expense_type_name;
          $nestedData['amount'] = $expenseRequest->amount;
          $nestedData['approved_amount'] = $expenseRequest->approved_amount;
          $nestedData['document_url'] = $expenseRequest->document_url != null ? tenant_asset(Constants::BaseFolderExpenseProofs . $expenseRequest->document_url) : null;
          $nestedData['created_at'] = $expenseRequest->created_at->format(Constants::DateTimeFormat);
          $nestedData['status'] = $expenseRequest->status;

          $nestedData['user_name'] = $expenseRequest->user->getFullName();
          $nestedData['user_code'] = $expenseRequest->user->code;
          $nestedData['user_profile_image'] = $expenseRequest->user->profile_picture != null ? tenant_asset(Constants::BaseFolderEmployeeProfileWithSlash . $expenseRequest->user->profile_picture) : null;
          $nestedData['user_initial'] = $expenseRequest->user->getInitials();

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
      'id' => 'required|exists:expense_requests,id',
      'status' => 'required|in:approved,rejected',
      'approvedAmount' => 'nullable|numeric',
      'adminRemarks' => 'nullable|string',
    ]);

    try {
      $expenseRequest = ExpenseRequest::findOrFail($validated['id']);
      $expenseRequest->status = $validated['status'];
      $expenseRequest->approved_amount = $validated['approvedAmount'];
      $expenseRequest->admin_remarks = $validated['adminRemarks'];
      $expenseRequest->save();

      Notification::send($expenseRequest->user, new ExpenseRequestApproval($expenseRequest, $validated['status']));

      return back()->with('success', 'Expense request ' . $validated['status'] . ' successfully.');
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return back()->with('error', 'Something went wrong. Please try again.');
    }
  }

  public function getByIdAjax($id)
  {
    $expenseRequest = ExpenseRequest::findOrFail($id);

    if (!$expenseRequest) {
      return Error::response('Expense request not found.');
    }

    $response = [
      'id' => $expenseRequest->id,
      'userName' => $expenseRequest->user->getFullName(),
      'userCode' => $expenseRequest->user->code,
      'expenseType' => $expenseRequest->expenseType->name,
      'forDate' => $expenseRequest->for_date->format(Constants::DateFormat),
      'amount' => $expenseRequest->amount,
      'approvedAmount' => $expenseRequest->approved_amount,
      'document' => $expenseRequest->document_url != null ? tenant_asset(Constants::BaseFolderExpenseProofs . $expenseRequest->document_url) : null,
      'status' => $expenseRequest->status,
      'createdAt' => $expenseRequest->created_at->format(Constants::DateTimeFormat),
      'userNotes' => $expenseRequest->remarks
    ];

    return Success::response($response);
  }


}
