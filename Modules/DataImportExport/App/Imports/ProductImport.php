<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow
{
  /**
   * Create a new Product instance for each row.
   *
   * @param array $row
   * @return Product|null
   */
  public function model(array $row)
  {
    $category = ProductCategory::where('code', $row['category_code'])->first();

    // Skip the row if the category is not found
    if (!$category) {
      return null;
    }

    return new Product([
      'name' => $row['name'],
      'description' => $row['description'] ?? null,
      'product_code' => $row['product_code'],
      'status' => $row['status'] ?? 'active',
      'category_id' => $category->id, // Assign category ID based on category code
      'base_price' => $row['base_price'],
      'discount' => $row['discount'] ?? 0,
      'tax' => $row['tax'] ?? 0,
      'price' => $row['price'],
      'stock' => $row['stock'] ?? 0,
      'images' => isset($row['images']) ? explode(',', $row['images']) : null,
      'thumbnail' => $row['thumbnail'] ?? null,
      'created_by_id' => Auth::id(),
      'updated_by_id' => Auth::id(),
    ]);
  }
}
