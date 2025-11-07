<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeaveTypeImport implements ToModel, WithHeadingRow
{
  /**
   * @param array $row
   *
   * @return Model|null
   */
  public function model(array $row)
  {

    // Return null if the row is invalid or does not have the required fields
    if (!isset($row['name'], $row['code'], $row['description'], $row['isimagerequired'])) {
      Log::info('Invalid row: ' . json_encode($row));
      return null;
    }

    Log::info('Processing row: ' . json_encode($row));

    return new LeaveType([
      'name' => $row['name'],
      'code' => $row['code'],
      'notes' => $row['description'],
      'is_proof_required' => $row['isimagerequired'],
      'created_by_id' => auth()->id(),
      'updated_by_id' => auth()->id(),
    ]);
  }
}
