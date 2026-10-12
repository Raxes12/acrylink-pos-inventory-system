<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public static function defaults(): array
    {
        return [
            'Filters & Air Intake',
            'Brake System',
            'Engine Components',
            'Gaskets & Seals',
            'Fuel System',
            'Cooling System',
            'Electrical & Lighting',
            'Battery & Charging System',
            'Clutch System',
            'Transmission & Drivetrain',
            'Steering System',
            'Suspension System',
            'Tires & Wheels',
            'Lubricants, Fluids & Chemicals',
            'Body & Exterior',
            'Interior & Accessories',
            'Fittings, Hoses & Tubing',
            'Tools & Shop Equipment',
            'Safety & Warning Devices',
            'Adhesives, Sealants & Tapes',
            'Hardware & Fasteners',
            'Cleaning & Car Care',
            'Other',
        ];
    }

    public static function idForName(?string $name): ?int
    {
        if (!$name) {
            return null;
        }

        return static::firstOrCreate(['name' => $name])->id;
    }
}
