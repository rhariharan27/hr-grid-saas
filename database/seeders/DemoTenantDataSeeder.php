<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\DocumentType;
use App\Models\ExpenseType;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTenantDataSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->info('Seeding Tenant data...');

    $this->command->info('Basic Master Data seeding..');

    Tenant::all()->runForEach(function () {

      DocumentType::create([
        'name' => 'Aadhar Card',
        'code' => 'AADHAR',
        'is_required' => true
      ]);

      DocumentType::create([
        'name' => 'PAN Card',
        'code' => 'PAN',
        'is_required' => true
      ]);

      DocumentType::create([
        'name' => 'Driving License',
        'code' => 'DL',
        'is_required' => false
      ]);

      LeaveType::create([
        'code' => 'CL',
        'name' => 'Casual Leave',
        'is_proof_required' => false
      ]);

      LeaveType::create([
        'code' => 'SL',
        'name' => 'Sick Leave',
        'is_proof_required' => true
      ]);

      ExpenseType::create([
        'name' => 'Travel',
        'code' => 'TRAVEL',
        'default_amount' => 100
      ]);

      ExpenseType::create([
        'name' => 'Food',
        'code' => 'FOOD',
        'default_amount' => 50
      ]);

      $chennaiTeam = Team::create([
        'name' => 'Chennai Team',
        'code' => 'CHNTM',
      ]);

      Team::create([
        'name' => 'Bangalore Team',
        'code' => 'BLRTM',
      ]);

      //Departments
      $hrDepartment = Department::create([
        'name' => 'HR',
        'code' => 'HR',
      ]);

      Department::create([
        'name' => 'IT',
        'code' => 'IT',
      ]);

      $adminDepartment = Department::create([
        'name' => 'Admin',
        'code' => 'ADM',
      ]);

      Department::create([
        'name' => 'Finance',
        'code' => 'FIN',
      ]);

      $sales = Department::create([
        'name' => 'Sales',
        'code' => 'SAL',
      ]);

      Department::create([
        'name' => "Field Sales",
        'code' => 'FSAL',
        'parent_id' => $sales->id
      ]);


      //Designations
      $hrDesignation = Designation::create([
        'name' => 'HR Admin',
        'code' => 'HRAD',
      ]);

      $adminDesignation = Designation::create([
        'name' => 'Admin',
        'code' => 'ADMN',
      ]);


      Designation::create([
        'name' => 'Field Sales Executive',
        'code' => 'FSE'
      ]);

      Designation::create([
        'name' => 'Field Sales Manager',
        'code' => 'FSM'
      ]);

      //Shifts
      $shift = Shift::create([
        'name' => 'General Shift',
        'code' => 'GS',
        'start_date' => now(),
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'is_default' => true
      ]);

      $this->command->info('Assigning ids to users...');

      $hrUser = User::where('email', 'hradmin@demo.com')
        ->first();

      $hrUser->code = "CHN-0001";
      $hrUser->department_id = $hrDepartment->id;
      $hrUser->designation_id = $hrDesignation->id;
      $hrUser->shift_id = $shift->id;
      $hrUser->team_id = $chennaiTeam->id;

      $hrUser->save();

      $adminUser = User::where('email', 'tenantadmin@demo.com')
        ->first();

      $adminUser->code = "CHN-0002";
      $adminUser->department_id = $adminDepartment->id;
      $adminUser->designation_id = $adminDesignation->id;
      $adminUser->shift_id = $shift->id;
      $adminUser->team_id = $chennaiTeam->id;

      $adminUser->save();
    });

    $this->command->info('Master Data seeded!');
  }
}
