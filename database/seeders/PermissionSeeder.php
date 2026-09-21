<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'customers', 'ticket-types', 'ticket-sales', 'draws', 'winners',
            'withdrawals', 'charges', 'bank-accounts', 'reports', 'security',
            'notifications', 'settings', 'users', 'roles', 'permissions',
        ];

        foreach ($modules as $module) {
            foreach (['index', 'show', 'create', 'edit', 'delete'] as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}", 'guard_name' => 'web'],
                    ['module' => $module, 'group' => $action, 'description' => "{$action} access for {$module}"]
                );
            }
        }
    }
}
