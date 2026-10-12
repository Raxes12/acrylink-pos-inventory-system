<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LookupSeeder::class,
            BrandSeeder::class,
            RoleSeeder::class,
            SecurityRuleSeeder::class,
            UserSeeder::class,
            AuthorizedDeviceSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
