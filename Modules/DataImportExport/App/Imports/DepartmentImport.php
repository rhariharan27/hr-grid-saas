<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DepartmentImport implements ToModel, WithHeadingRow
{
  /**
   * @param Collection $collection
   */
  public function model(array $row)
  {
    Log::info('Processing row: ' . json_encode($row));

    // Validate if required keys exist
    if (!isset($row['name'], $row['code'], $row['notes'])) {
      Log::error('Invalid row: ' . json_encode($row));
      return null;
    }

    return new Department([
      'name' => $row['name'],
      'code' => $row['code'],
      'notes' => $row['notes'] ?? null,
      'parent_id' => $row['parent_id'] ?? null, // Ensure parent_id is numeric if provided
      'created_by_id' => auth()->id(),
      'updated_by_id' => auth()->id(),
    ]);
  }
}
