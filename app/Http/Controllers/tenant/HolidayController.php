<?php

namespace App\Http\Controllers\tenant;

use Exception;
use App\Enums\Status;
use App\Models\Holiday;
use App\Models\Location;
use App\ApiClasses\Error;
use App\ApiClasses\Success;
use Illuminate\Http\Request;
use App\Imports\HolidaysImport;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Constants;

class HolidayController extends Controller
{
  public function index()
  {

    return view('tenant.holidays.index');
  }

  public function indexAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'name',
        3 => 'code',
        4 => 'date',
        5 => 'notes',
        6 => 'status',
      ];

      $search = [];

      $totalData = Holiday::count();

      $totalFiltered = $totalData;

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      if (empty($request->input('search.value'))) {
        $holidays = Holiday::offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();
      } else {
        $search = $request->input('search.value');
        $holidays = Holiday::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->get();

        $totalFiltered = Holiday::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->count();
      }
      $data = [];
      if (!empty($holidays)) {
        foreach ($holidays as $holiday) {
          $nestedData['id'] = $holiday->id;
          $nestedData['name'] = $holiday->name;
          $nestedData['code'] = $holiday->code;
          $nestedData['date'] = $holiday->date->format(Constants::DateFormat);
          $nestedData['notes'] = $holiday->notes;
          $nestedData['status'] = $holiday->status;
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

  public function addOrUpdateHolidayAjax(Request $request)
  {
    $holidayId = $request->id;
    $request->validate([
      'name' => 'required',
      'code' => ['required', 'unique:holidays,code,' . $holidayId],
      'date' => 'required',
      'notes' => 'nullable',
    ]);

    if ($holidayId) {
      $holiday = Holiday::find($holidayId);
      $holiday->name = $request->name;
      $holiday->code = $request->code;
      $holiday->date = $request->date;
      $holiday->notes = $request->notes;
      $holiday->save();

      return Success::response('Updated');
    } else {

      $holiday = new Holiday();
      $holiday->name = $request->name;
      $holiday->code = $request->code;
      $holiday->date = $request->date;
      $holiday->notes = $request->notes;
      $holiday->save();

      return Success::response('Added');
    }
  }

  public function getByIdAjax($id)
  {
    $holiday = Holiday::findOrFail($id);

    if (!$holiday) {
      return Error::response('Holiday not found');
    }

    $response = [
      'id' => $holiday->id,
      'name' => $holiday->name,
      'code' => $holiday->code,
      'date' => $holiday->date->format('Y-m-d'),
      'notes' => $holiday->notes
    ];

    return Success::response($response);
  }

  public function deleteAjax($id)
  {
    $holiday = Holiday::findOrFail($id);
    if (!$holiday) {
      return Error::response('Holiday not found');
    }

    $holiday->delete();
    return Success::response('Holiday deleted successfully');
  }

  public function changeStatusAjax($id)
  {
    $holiday = Holiday::findOrFail($id);
    if (!$holiday) {
      return Error::response('Holiday not found');
    }

    $holiday->status = $holiday->status == Status::ACTIVE ? Status::INACTIVE : Status::ACTIVE;
    $holiday->save();
    return Success::response('Holiday status changed successfully');
  }
}
