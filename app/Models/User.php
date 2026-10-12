<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_INVENTORY = 'inventory_staff';
    public const ROLE_CASHIER = 'cashier';

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'status',
        'mfa_enabled',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'mfa_enabled' => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    public function receivedTransactionPayments()
    {
        return $this->hasMany(TransactionPayment::class, 'received_by');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function processedRefunds()
    {
        return $this->hasMany(Refund::class, 'processed_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class);
    }

    public function mfaChallenges()
    {
        return $this->hasMany(MfaChallenge::class);
    }

    public function intrusionEvents()
    {
        return $this->hasMany(IntrusionEvent::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function authorizedDevices()
    {
        return $this->hasMany(AuthorizedDevice::class, 'assigned_user_id');
    }

    public function stockReceipts()
    {
        return $this->hasMany(StockReceipt::class, 'received_by');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, Role::ADMIN]);
    }

    public function isInvStaff(): bool
    {
        return $this->hasAnyRole([self::ROLE_INVENTORY, Role::INVENTORY_STAFF, 'inventory_staff', 'inventory-staff']);
    }

    public function isCashier(): bool
    {
        return $this->hasAnyRole([self::ROLE_CASHIER, Role::CASHIER]);
    }

    public function canEdit(): bool
    {
        return $this->isAdmin() || $this->isInvStaff();
    }

    public function canAccessSales(): bool
    {
        return $this->isAdmin() || $this->isCashier();
    }

    public function canAccessInventory(): bool
    {
        return $this->isAdmin() || $this->isInvStaff();
    }

    public function defaultLandingRoute(): string
    {
        if ($this->isCashier()) {
            return route('pos.index');
        }

        if ($this->isInvStaff()) {
            return route('inventory.stock-overview');
        }

        return route('dashboard');
    }

    public function hasAnyRole(array $roles): bool
    {
        $allowed = collect($roles)->map(fn ($role) => $this->normalizeRole($role))->all();

        return in_array($this->normalizedRoleName(), $allowed, true);
    }

    public function roleLabel(): string
    {
        $role = $this->relationLoaded('role')
            ? $this->getRelation('role')
            : $this->role()->first();

        return $role?->role_name ?? match ($this->getAttribute('role')) {
            self::ROLE_ADMIN => Role::ADMIN,
            self::ROLE_INVENTORY => Role::INVENTORY_STAFF,
            default => Role::CASHIER,
        };
    }

    public function roleColorKey(): string
    {
        return match ($this->normalizedRoleName()) {
            $this->normalizeRole(Role::ADMIN) => self::ROLE_ADMIN,
            $this->normalizeRole(Role::INVENTORY_STAFF) => self::ROLE_INVENTORY,
            default => self::ROLE_CASHIER,
        };
    }

    private function normalizedRoleName(): string
    {
        $role = $this->relationLoaded('role')
            ? $this->getRelation('role')
            : $this->role()->first();

        return $this->normalizeRole($role?->role_name ?: $this->getAttribute('role'));
    }

    private function normalizeRole(?string $role): string
    {
        return Str::of($role ?? '')
            ->lower()
            ->replace(['-', '_'], ' ')
            ->squish()
            ->toString();
    }
}
