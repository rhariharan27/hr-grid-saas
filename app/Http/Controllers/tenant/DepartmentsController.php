<?php

namespace App\Http\Controllers\tenant;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Department;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentsController extends Controller
{
  public function index()
  {
    return view('tenant.departments.index');
  }

  public function getListAjax()
  {
    $departments = Department::where('status', Status::ACTIVE)
      ->get(['id', 'name', 'code']);

    return Success::response($departments);
  }


  public function indexAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'name',
        3 => 'code',
        4 => 'parent_id',
        5 => 'notes',

      ];

      $search = [];
      $totalData = Department::count();
      $totalFiltered = $totalData;

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      if (empty($request->input('search.value'))) {
        $departments = Department::with('parentDepartment:id,name')
          ->offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();
      } else {
        $search = $request->input('search.value');
        $departments = Department::with('parentDepartment:id,name')
          ->where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->get();

        $totalFiltered = Department::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->count();
      }

      $data = [];
      if (!empty($departments)) {
        foreach ($departments as $department) {
          $nestedData['id'] = $department->id;
          $nestedData['name'] = $department->name;
          $nestedData['code'] = $department->code;
          $nestedData['parent_department'] = $department->parentDepartment ? $department->parentDepartment->name : 'No Parent';
          $nestedData['notes'] = $department->notes;
          $nestedData['status'] = $department->status;

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
    } catch (Exception $e) {
      return Error::response($e->getMessage());
    }

  }

  public function getParentDepartments()
  {
    $departments = Department::with('parentDepartment:id,name')->get(['id', 'name', 'parent_id']);
    return response()->json($departments);
  }

  public function addOrUpdateDepartmentAjax(Request $request)
  {
    $departmentId = $request->input('departmentId', null);
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'code' => [
        'required',
        'string',
        'max:10',
        Rule::unique('departments')->ignore($departmentId)
      ],
      'notes' => 'nullable|string|max:225',
      'parent_department' => 'nullable|exists:departments,id',
    ]);

    try {
      if ($departmentId) {

        $department = Department::find($departmentId);
        if (!$department) {
          return Error::response('Department not found', 404);
        }
        $department->name = $validatedData['name'];
        $department->code = $validatedData['code'];
        $department->notes = $validatedData['notes'];
        $department->parent_id = $validatedData['parent_department'];
        $department->save();

        return Success:: response('Updated');
      } else {
        // Create a new department
        $department = new Department();
        $department->name = $request->name;
        $department->code = $request->code;
        $department->notes = $request->notes;
        $department->parent_id = $request->parent_department;
        $department->save();

        return Success::response('Created');
      }
    } catch (Exception $e) {
      return Error::response($e->getMessage());
    }
  }

  public function getDepartmentAjax($id)
  {
    try {

      $department = Department::find($id);

      if (!$department) {
        return Error::response('Department not found', 404);
      }

      $response = [
        'id' => $department->id,
        'name' => $department->name,
        'code' => $department->code,
        'notes' => $department->notes,
        'parent_id' => $department->parent_id,
        'status' => $department->status,
      ];

      return Success::response($response);
    } catch (Exception $e) {
      return Error::response($e->getMessage());
    }
  }

  public function deleteAjax($id)
  {
    $department = Department::findOrFail($id);

    $department->delete();

    return Success::response('Department deleted successfully');
  }

  public function changeStatus($id)
  {
    $departments = Department::findOrFail($id);

    if (!$departments) {
      return Error::response('Department not found', 404);
    }
    $departments->status = $departments->status == Status::ACTIVE ? Status::INACTIVE : Status::ACTIVE;
    $departments->save();
    return Success::response('Department status changed successfully');
  }


}
