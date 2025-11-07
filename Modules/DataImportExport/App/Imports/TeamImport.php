<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeamImport implements ToModel, WithHeadingRow
{
  /**
   * @param array $row
   *
   * @return Model|null
   */
  public function model(array $row)
  {
    // Return null if the row is invalid or does not have the required fields
    if (!isset($row['name'], $row['code'], $row['description'])) {
      Log::info('Invalid row: ' . json_encode($row));
      return null;
    }

    return new Team([
      'name' => $row['name'],
      'code' => $row['code'],
      'notes' => $row['description'],
      'is_chat_enabled' => true,
      'created_by_id' => auth()->id(),
      'updated_by_id' => auth()->id(),
    ]);
  }
}
