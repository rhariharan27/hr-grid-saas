<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductCategoryExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return ProductCategory::select(
      'id',
      'name',
      'code',
      'description',
      'parent_id',
      'status',
      'created_at',
      'updated_at'
    )->get();
  }

  /**
   * Define column headings.
   *
   * @return array
   */
  public function headings(): array
  {
    return [
      'ID',
      'Name',
      'Code',
      'Description',
      'Parent ID',
      'Status',
      'Created At',
      'Updated At',
    ];
  }
}
