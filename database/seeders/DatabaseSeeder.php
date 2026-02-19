<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\{User, Role, Module, RoleModulePermission};
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $role = Role::Create([
            'title' => 'Super Admin'
        ]);
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@escape.sa',
            'role_id' => $role->id,
            'password' => Hash::make('QWqw12!@'),
            'profile_pic' => 'dummy_profile.webp',
            'is_admin' => 1
        ]);

        $modules = ['Reports', 'Roles & Permissions', 'Users', 'Packages', 
        'Bookings', 
        'Payments',
        'WorkingHours',
        'Coupons',
        ];
        $modulesIds = array();
        foreach ($modules as  $module) {
            $module_id = Module::insertGetId(['name' => $module]);
            array_push($modulesIds, $module_id);
        }
        
        foreach ($modulesIds as $id) {
            RoleModulePermission::create([
                'role_id' => $role->id,
                'module_id' => $id,
                'can_create' => 1,
                'can_view' => 1,
                'can_update' => 1,
                'can_delete' => 1,
                'can_view_report' => 1,
            ]);
        }
    }
}
