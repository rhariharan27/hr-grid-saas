<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\ExpenseType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpenseTypeExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return ExpenseType::select(
      'id',
      'name',
      'code',
      'notes',
      'default_amount',
      'max_amount',
      'is_proof_required',
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
      'Notes',
      'Default Amount',
      'Max Amount',
      'Proof Required',
      'Status',
      'Created At',
      'Updated At',
    ];
  }
}
