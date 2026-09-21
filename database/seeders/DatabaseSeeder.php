<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Models\Ticketing\Customer;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@rbac.com'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => bcrypt('password'),
                'phone' => '+91 9876543210',
                'designation' => 'Platform Owner',
                'department' => 'Operations',
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super-admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@rbac.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'phone' => '+91 9876543211',
                'designation' => 'Business Admin',
                'department' => 'Sales',
                'created_by' => $superAdmin->id,
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        $customerUser = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Demo Customer',
                'username' => 'customer',
                'password' => bcrypt('password'),
                'phone' => '+91 9876543212',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );
        $customerUser->assignRole('customer');

        $customer = Customer::firstOrCreate(
            ['customer_code' => 'CUST100001'],
            [
                'admin_id' => $admin->id,
                'user_id' => $customerUser->id,
                'full_name' => 'Demo Customer',
                'mobile' => '9876543212',
                'email' => 'customer@example.com',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'status' => 'active',
            ]
        );

        foreach (['daily', 'weekly', 'monthly', 'festival'] as $frequency) {
            $type = TicketType::firstOrCreate(
                ['admin_id' => $admin->id, 'name' => ucfirst($frequency).' Ticket'],
                [
                    'frequency' => $frequency,
                    'ticket_price' => 50,
                    'description' => ucfirst($frequency).' draw ticket.',
                    'festival_name' => $frequency === 'festival' ? 'Festival Special' : null,
                    'festival_date' => $frequency === 'festival' ? now()->addMonth()->toDateString() : null,
                    'is_active' => true,
                ]
            );

            $type->prizes()->updateOrCreate(['position' => 1], ['amount' => 1000]);
            $type->prizes()->updateOrCreate(['position' => 2], ['amount' => 500]);
            $type->prizes()->updateOrCreate(['position' => 3], ['amount' => 250]);
        }

        foreach (SiteSetting::getDefaultSettings() as $setting) {
            SiteSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command?->info('Fresh ticketing system seeded.');
        $this->command?->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'superadmin@rbac.com', 'password'],
                ['Admin', 'admin@rbac.com', 'password'],
                ['Customer', 'customer@example.com', 'password'],
            ]
        );
    }
}
