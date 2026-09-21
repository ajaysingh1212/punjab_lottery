<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'web'],
            ['description' => 'Full platform owner', 'color' => '#dc3545', 'icon' => 'fas fa-crown']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['description' => 'Business administrator', 'color' => '#2563eb', 'icon' => 'fas fa-user-shield']
        );

        Role::firstOrCreate(
            ['name' => 'customer', 'guard_name' => 'web'],
            ['description' => 'Customer portal user', 'color' => '#0f766e', 'icon' => 'fas fa-user']
        );

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions(Permission::whereNotIn('module', ['roles', 'permissions'])->get());
    }
}
