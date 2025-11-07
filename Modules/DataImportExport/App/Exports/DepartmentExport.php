<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DepartmentExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return Department::select(
      'id',
      'name',
      'code',
      'notes',
      'parent_id',
      'status',
      'created_by_id',
      'updated_by_id',
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
      'Notes',
      'Parent ID',
      'Status',
      'Created By',
      'Updated By',
      'Created At',
      'Updated At',
    ];
  }
}
