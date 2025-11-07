<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

    $this->command->info('Seeding permissions...');

    Permission::create([
      'name' => 'view role',
      'guard_name' => 'web',
    ]);

    Permission::create([
      'name' => 'create role',
      'guard_name' => 'web',
    ]);

    Permission::create([
      'name' => 'edit role',
      'guard_name' => 'web',
    ]);

    Permission::create([
      'name' => 'delete role',
      'guard_name' => 'web',
    ]);

    Permission::create([
      'name' => 'view user',
      'guard_name' => 'web',
    ]);

    Permission::create([
      'name' => 'create user',
      'guard_name' => 'web',
    ]);

    Permission::create([
      'name' => 'edit user',
      'guard_name' => 'web',
    ]);

    Permission::create([
      'name' => 'delete user',
      'guard_name' => 'web',
    ]);

    $this->command->info('Permissions seeded!');
  }
}
