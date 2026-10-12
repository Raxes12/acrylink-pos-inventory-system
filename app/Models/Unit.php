<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['code', 'name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public static function defaults(): array
    {
        return ['BAG', 'CYL', 'DRUM', 'FEET', 'GALLON', 'KILOS', 'KIT', 'LITER', 'METER', 'PAIL', 'PIECE', 'PO', 'ROLL', 'SET'];
    }

    public static function idForCode(?string $code): ?int
    {
        if (!$code) {
            return null;
        }

        return static::firstOrCreate(
            ['code' => $code],
            ['name' => $code],
        )->id;
    }
}
