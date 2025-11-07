<?php

namespace App\Http\Controllers\tenant;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Services\CommonService\SettingsService\ISettings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentTypeController extends Controller
{
  private ISettings $settings;

  public function __construct(ISettings $settings)
  {
    $this->settings = $settings;
  }

  public function index()
  {
    return view('tenant.documentTypes.index');
  }

  public function getListAjax(Request $request)
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

      $totalData = DocumentType::count();

      $totalFiltered = $totalData;

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      if (empty($request->input('search.value'))) {
        $proofTypes = DocumentType::offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();
      } else {
        $search = $request->input('search.value');
        $proofTypes = DocumentType::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->get();

        $totalFiltered = DocumentType::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->count();
      }

      $data = [];
      if (!empty($proofTypes)) {
        foreach ($proofTypes as $proofType) {
          $nestedData['id'] = $proofType->id;
          $nestedData['name'] = $proofType->name;
          $nestedData['code'] = $proofType->code;
          $nestedData['notes'] = $proofType->notes;
          $nestedData['status'] = $proofType->status;
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

  public function getCodeAjax()
  {
    return response()->json($this->getCode());
  }

  private function getCode()
  {
    $prefix = $this->settings->getDocumentTypePrefix();

    $proofId = DocumentType::withTrashed()->max('id');
    $proofId += 1;
    $proofId = str_pad($proofId, 4, "0", STR_PAD_LEFT);

    $fullCode = "{$prefix}-{$proofId}";

    return $fullCode;
  }


  public function addOrUpdateAjax(Request $request)
  {
    $proofTypeId = $request->id;
    $request->validate([
      'name' => 'required',
      'notes' => 'nullable',
      'code' => 'required',
      'isRequired' => 'required',

    ]);

    try {

      if ($proofTypeId) {
        $proofType = DocumentType::find($proofTypeId);
        $proofType->name = $request->name;
        $proofType->notes = $request->notes;
        $proofType->code = $request->code;
        $proofType->is_required = $request->isRequired;
        $proofType->save();

        return Success::response('Updated');
      } else {

        $proofType = new DocumentType();
        $proofType->name = $request->name;
        $proofType->notes = $request->notes;
        $proofType->code = $request->code;
        $proofType->is_required = $request->isRequired;

        $proofType->save();

        return Success::response('Added');
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return Error::response('Something went wrong. Please try again later');
    }
  }

  public function getByIdAjax($id)
  {
    $proofType = DocumentType::findOrFail($id);

    if (!$proofType) {
      return Error::response('Proof type not found');
    }

    $response = [
      'id' => $proofType->id,
      'name' => $proofType->name,
      'code' => $proofType->code,
      'notes' => $proofType->notes,
      'isRequired' => $proofType->is_required

    ];

    return Success::response($response);
  }

  public function deleteAjax($id)
  {
    $proofType = DocumentType::findOrFail($id);
    if (!$proofType) {
      return Error::response('Proof type not found');
    }

    $proofType->delete();
    return Success::response('Proof type deleted successfully');
  }

  public function changeStatusAjax($id)
  {
    $proofType = DocumentType::findOrFail($id);

    try {

      if (!$proofType) {
        return Error::response('Proof type not found');
      }
      $proofType->status = $proofType->status == Status::ACTIVE ? Status::INACTIVE : Status::ACTIVE;

      $proofType->save();

      return Success::response('Proof type status changed successfully');
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return Error::response('Something went wrong. Please try again later');
    }
  }
}
