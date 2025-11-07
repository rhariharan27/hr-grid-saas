<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HolidayImport implements ToModel, WithHeadingRow
{
  /**
   * @param array $row
   *
   * @return Model|null
   */
  public function model(array $row)
  {
    // Return null if the row is invalid or does not have the required fields
    if (!isset($row['name'], $row['code'], $row['description'], $row['date'])) {
      return null;
    }

    return new Holiday([
      'name' => $row['name'],
      'code' => $row['code'],
      'notes' => $row['description'],
      'date' => Carbon::parse($row['date']),
      'created_by_id' => auth()->id(),
      'updated_by_id' => auth()->id(),
    ]);
  }
}
