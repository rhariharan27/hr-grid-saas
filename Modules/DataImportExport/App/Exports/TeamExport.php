<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\Team;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeamExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return Team::select(
      'id',
      'name',
      'code',
      'notes',
      'status',
      'team_head_id',
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
      'Status',
      'Team Head ID',
      'Created At',
      'Updated At',
    ];
  }
}
