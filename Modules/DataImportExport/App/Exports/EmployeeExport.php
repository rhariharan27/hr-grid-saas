<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return User::with('team', 'shift', 'designation')
      ->get()
      ->map(function ($user) {
        return [
          'first_name' => $user->first_name,
          'last_name' => $user->last_name,
          'email' => $user->email,
          'phone' => $user->phone,
          'code' => $user->code,
          'date_of_joining' => $user->date_of_joining,
          'designation' => $user->designation->name ?? null,
          'team' => $user->team->name ?? null,
          'shift' => $user->shift->name ?? null,
          'status' => $user->status->value,
          'base_salary' => $user->base_salary,
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
      'First Name',
      'Last Name',
      'Email',
      'Phone',
      'Employee Code',
      'Date of Joining',
      'Designation',
      'Team',
      'Shift',
      'Status',
      'Base Salary',
    ];
  }
}
