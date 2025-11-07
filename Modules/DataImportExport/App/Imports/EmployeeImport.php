<?php

namespace Modules\DataImportExport\App\Imports;

use App\Enums\UserAccountStatus;
use App\Models\Designation;
use App\Models\Settings;
use App\Models\Shift;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;
use function auth;

class EmployeeImport implements ToModel, WithHeadingRow
{
  /**
   * Map each row to the User model.
   *
   * @param array $row
   * @return User|null
   */
  public function model(array $row)
  {
    try {
      $designation = Designation::where('code', $row['designation'])->first();
      $team = Team::where('code', $row['team'])->first();
      $shift = Shift::where('code', $row['shift'])->first();

      if (!$designation || !$team || !$shift) {
        Log::error('Invalid designation, team, or shift for row: ' . json_encode($row));
        return null;
      }

      $settings = Settings::first();

      $user = User::create([
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'code' => $row['employee_code'],
        'date_of_joining' => $row['date_of_joining'],
        'designation_id' => $designation->id,
        'team_id' => $team->id,
        'shift_id' => $shift->id,
        'status' => UserAccountStatus::ACTIVE,
        'base_salary' => $row['base_salary'],
        'password' => Hash::make($settings->default_password), // Set default password
        'created_by_id' => auth()->id(),
      ]);

      // Assign role to the user if specified in the row
      if (isset($row['role'])) {
        $role = Role::where('name', $row['role'])->first();
        if ($role) {
          $user->assignRole($role);
        } else {
          Log::warning('Role not found for user: ' . $row['email']);
        }
      }

      return $user;
    } catch (Exception $e) {
      Log::error('Failed to import employee: ' . $e->getMessage());
      return null;
    }
  }
}
