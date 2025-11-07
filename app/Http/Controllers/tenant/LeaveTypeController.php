<?php

namespace App\Http\Controllers\tenant;

use App\Enums\Status;
use App\Models\Settings;
use App\ApiClasses\Error;
use App\Models\LeaveType;
use App\ApiClasses\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class LeaveTypeController extends Controller
{
  public function index()
  {
    return view('tenant.leaveTypes.index');
  }

  public function getLeaveTypesAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'name',
        3 => 'notes',
        4 => 'code',
        5 => 'status',
      ];

      $search = [];

      $totalData = LeaveType::count();

      $totalFiltered = $totalData;

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      if (empty($request->input('search.value'))) {
        $leaveTypes = LeaveType::offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();
      } else {
        $search = $request->input('search.value');
        $leaveTypes = LeaveType::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->get();

        $totalFiltered = LeaveType::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->count();
      }

      $data = [];
      if (!empty($leaveTypes)) {
        foreach ($leaveTypes as $leaveType) {
          $nestedData['id'] = $leaveType->id;
          $nestedData['name'] = $leaveType->name;
          $nestedData['code'] = $leaveType->code;
          $nestedData['notes'] = $leaveType->notes;
          $nestedData['status'] = $leaveType->status;
          $data[] = $nestedData;
        }
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'code' => 200,
        'data' => $data
      ]);
    } catch (\Exception $e) {
      return Error::response($e->getMessage());
    }
  }

  public function addOrUpdateLeaveTypeAjax(Request $request)
  {
    $leaveTypeId = $request->id;
    $request->validate([
      'name' => 'required',
      'code' => ['required', 'unique:leave_types,code,' . $leaveTypeId],
      'notes' => 'nullable',
      'isProofRequired' => 'nullable',
      'per_month' => 'nullable|integer|min:0',
      'per_year' => 'nullable|integer|min:0',
    ]);

    if ($leaveTypeId) {
      $leaveType = LeaveType::find($leaveTypeId);
      $leaveType->name = $request->name;
      $leaveType->notes = $request->notes;
      $leaveType->code = $request->code;
      $leaveType->is_proof_required = $request->isProofRequired;
      $leaveType->per_month = $request->per_month;
      $leaveType->per_year = $request->per_year;
      $leaveType->save();

      return response()->json([
        'code' => 200,
        'message' => 'Updated',
      ]);
    } else {
      $leaveType = new LeaveType();
      $leaveType->name = $request->name;
      $leaveType->notes = $request->notes;
      $leaveType->code = $request->code;
      $leaveType->is_proof_required = $request->isProofRequired;
      $leaveType->per_month = $request->per_month;
      $leaveType->per_year = $request->per_year;
      $leaveType->save();

      return response()->json([
        'code' => 200,
        'message' => 'Added',
      ]);
    }
  }

  public function checkCodeValidationAjax(Request $request)
  {
    $code = $request->code;


    if (!$code) {
      return response()->json(["valid" => false]);
    }

    if ($request->has('id')) {
      $id = $request->input('id');
      if (LeaveType::where('code', $code)->where('id', '!=', $id)->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }
    if (LeaveType::where('code', $code)->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }
    return response()->json([
      "valid" => true,
    ]);
  }

  public function getLeaveTypeAjax($id)
  {
    $leaveType = LeaveType::findOrFail($id);

    if (!$leaveType) {
      return Error::response('Leave type not found');
    }
    $response = [
      'id' => $leaveType->id,
      'name' => $leaveType->name,
      'code' => $leaveType->code,
      'notes' => $leaveType->notes,
      'isProofRequired' => $leaveType->is_proof_required,
      'per_month' => $leaveType->per_month,
      'per_year' => $leaveType->per_year,
    ];

    return response()->json($response);
  }

  public function deleteLeaveTypeAjax($id)
  {
    $leaveType = LeaveType::findOrFail($id);
    if (!$leaveType) {
      return Error::response('Leave type not found');
    }

    $leaveType->delete();
    return Success::response('Leave type deleted successfully');
  }

  public function changeStatus($id)
  {
    $leaveType = LeaveType::findOrFail($id);

    if (!$leaveType) {
      return response()->json([
        'code' => 404,
        'message' => 'Leave type not found',
      ]);
    }
    $leaveType->status = $leaveType->status == Status::ACTIVE ? Status::INACTIVE : Status::ACTIVE;
    $leaveType->save();
    return response()->json([
      'code' => 200,
      'message' => 'Leave type status changed successfully',
    ]);
  }
}
