<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'name' => 'System Admin',
                'email' => 'alexispagulayan23@gmail.com',
                'role' => User::ROLE_ADMIN,
                'role_id' => $this->roleId(Role::ADMIN),
                'status' => 'active',
                'mfa_enabled' => true,
            ],
            [
                'username' => 'inventory1',
                'password' => Hash::make('eugene123'),
                'name' => 'Eugene Del Rosario',
                'email' => 'deugenn@gmail.com',
                'role' => User::ROLE_INVENTORY,
                'role_id' => $this->roleId(Role::INVENTORY_STAFF),
                'status' => 'active',
                'mfa_enabled' => false,
            ],
            [
                'username' => 'cashier1',
                'password' => Hash::make('james123'),
                'name' => 'James Maverick Prudenciado',
                'email' => 'qwertyjames@gmail.com',
                'role' => User::ROLE_CASHIER,
                'role_id' => $this->roleId(Role::CASHIER),
                'status' => 'active',
                'mfa_enabled' => false,
            ],
            [
                'username' => 'cashier2',
                'password' => Hash::make('george456'),
                'name' => 'George',
                'email' => 'george@gmail.com',
                'role' => User::ROLE_CASHIER,
                'role_id' => $this->roleId(Role::CASHIER),
                'status' => 'inactive',
                'mfa_enabled' => false,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }
    }

    private function roleId(string $roleName): ?int
    {
        return Role::where('role_name', $roleName)->value('role_id');
    }
}
