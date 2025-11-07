<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\LeaveType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveTypeExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return LeaveType::select('id', 'name', 'code', 'notes', 'is_proof_required', 'status', 'created_at', 'updated_at')->get();
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
      'Proof Required',
      'Status',
      'Created At',
      'Updated At',
    ];
  }
}
