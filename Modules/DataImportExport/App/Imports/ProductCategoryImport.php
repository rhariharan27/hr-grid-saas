<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductCategoryImport implements ToModel, WithHeadingRow
{
  /**
   * Create a new ProductCategory instance for each row.
   *
   * @param array $row
   * @return ProductCategory|null
   */
  public function model(array $row)
  {
    return new ProductCategory([
      'name' => $row['name'],
      'code' => $row['code'],
      'description' => $row['description'] ?? null,
      'parent_id' => $row['parent_id'] ?? null,
      'status' => $row['status'] ?? 'active',
      'created_by_id' => Auth::id(),
      'updated_by_id' => Auth::id(),
    ]);
  }
}
