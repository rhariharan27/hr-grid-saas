<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ShiftImport implements ToModel, WithHeadingRow
{
  /**
   * @param array $row
   *
   * @return Model|null
   */
  public function model(array $row)
  {

    // Return null if the row is invalid or does not have the required fields
    if (!isset($row['name'], $row['code'], $row['description'], $row['start_time'], $row['end_time'], $row['sunday'], $row['monday'], $row['tuesday'], $row['wednesday'], $row['thursday'], $row['friday'], $row['saturday'])) {
      Log::info('Invalid row: ' . json_encode($row));
      return null;
    }

    $startTime = date('H:i:s', strtotime($row['start_time']));
    $endTime = date('H:i:s', strtotime($row['end_time']));
    return new Shift([
      'name' => $row['name'],
      'code' => $row['code'],
      'notes' => $row['description'],
      'start_time' => $startTime,
      'start_date' => now(),
      'end_time' => $endTime,
      'sunday' => $row['sunday'],
      'monday' => $row['monday'],
      'tuesday' => $row['tuesday'],
      'wednesday' => $row['wednesday'],
      'thursday' => $row['thursday'],
      'friday' => $row['friday'],
      'saturday' => $row['saturday'],
      'created_by_id' => auth()->id(),
      'updated_by_id' => auth()->id(),
    ]);
  }
}
