<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\Holiday;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HolidayExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return Holiday::select('id', 'name', 'date', 'code', 'notes', 'status', 'created_at', 'updated_at')->get();
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
      'Date',
      'Code',
      'Notes',
      'Status',
      'Created At',
      'Updated At',
    ];
  }
}
