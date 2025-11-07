<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return Product::with('category')->get()->map(function ($product) {
      return [
        'name' => $product->name,
        'description' => $product->description,
        'product_code' => $product->product_code,
        'status' => $product->status,
        'category_code' => $product->category->code ?? null, // Using category code
        'base_price' => $product->base_price,
        'discount' => $product->discount,
        'tax' => $product->tax,
        'price' => $product->price,
        'stock' => $product->stock,
        'images' => $product->images ? implode(',', $product->images) : null,
        'thumbnail' => $product->thumbnail,
      ];
    });
  }

  /**
   * Define column headings.
   *
   * @return array
   */
  public function headings(): array
  {
    return [
      'Name',
      'Description',
      'Product Code',
      'Status',
      'Category Code',
      'Base Price',
      'Discount',
      'Tax',
      'Price',
      'Stock',
      'Images',
      'Thumbnail',
    ];
  }
}
