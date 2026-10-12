<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            Role::ADMIN => 'Full system access including users, inventory, POS, security, audit logs, and device controls.',
            Role::CASHIER => 'POS terminal and transaction access only.',
            Role::INVENTORY_STAFF => 'Stock overview, products, stock receipts, and supplier management only.',
        ];

        foreach ($roles as $name => $description) {
            Role::updateOrCreate(
                ['role_name' => $name],
                ['description' => $description]
            );
        }
    }
}
