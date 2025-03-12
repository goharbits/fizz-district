<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;



class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $customerRole = Role::create(['name' => 'customer']);

        // Create a user with the Admin role
        $adminUser = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@fizz-district.com',  // Replace with your desired email
            'phone_number' => '1234567890',   // Replace with your desired phone number
            'password' => Hash::make('password'),  // Replace with your desired password
        ]);

        // Assign the Admin role to the user
        $adminUser->assignRole($adminRole);
    }
}
