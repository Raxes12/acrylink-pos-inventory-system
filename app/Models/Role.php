<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ADMIN = 'Admin';
    public const CASHIER = 'Cashier';
    public const INVENTORY_STAFF = 'Inventory Staff';

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public static function legacySlugForName(?string $roleName): string
    {
        return match ($roleName) {
            self::ADMIN => User::ROLE_ADMIN,
            self::INVENTORY_STAFF => User::ROLE_INVENTORY,
            default => User::ROLE_CASHIER,
        };
    }
}
