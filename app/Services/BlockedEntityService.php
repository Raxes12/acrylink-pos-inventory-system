<?php

namespace App\Services;

use App\Models\AuthorizedDevice;
use App\Models\BlockedEntity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BlockedEntityService
{
    public function activeBlockForRequest(Request $request, ?User $user = null): ?BlockedEntity
    {
        if (!Schema::hasTable('blocked_entities')) {
            return null;
        }

        $this->expireOldBlocks();

        $identity = app(DeviceIdentityService::class);
        $ipAddress = $identity->ipAddress($request);
        $macAddress = $identity->macAddress($request);

        if ($user) {
            $block = $this->activeBlock(BlockedEntity::TYPE_USER, (string) $user->id);
            if ($block) {
                return $block;
            }
        }

        if ($ipAddress) {
            $block = $this->activeBlock(BlockedEntity::TYPE_IP_ADDRESS, $ipAddress);
            if ($block) {
                return $block;
            }
        }

        if ($macAddress) {
            $block = $this->activeBlock(BlockedEntity::TYPE_MAC_ADDRESS, $macAddress);
            if ($block) {
                return $block;
            }
        }

        $device = $this->matchingKnownDevice($request);
        if ($device) {
            return $this->activeBlock(BlockedEntity::TYPE_DEVICE, (string) $device->id);
        }

        return null;
    }

    public function block(
        string $entityType,
        ?string $entityValue,
        string $reason,
        ?User $blockedBy = null,
        mixed $expiresAt = null
    ): ?BlockedEntity {
        if (!Schema::hasTable('blocked_entities')) {
            return null;
        }

        $entityValue = $this->normalizeEntityValue($entityType, $entityValue);
        if (!$entityValue) {
            return null;
        }

        $expiresAt ??= $this->defaultExpiresAt();

        $existing = $this->activeBlock($entityType, $entityValue);
        if ($existing) {
            $existing->update([
                'reason' => $reason,
                'blocked_by' => $blockedBy?->id,
                'expires_at' => $expiresAt,
            ]);

            return $existing;
        }

        return BlockedEntity::create([
            'entity_type' => $entityType,
            'entity_value' => $entityValue,
            'reason' => $reason,
            'blocked_by' => $blockedBy?->id,
            'blocked_at' => now(),
            'expires_at' => $expiresAt,
            'status' => BlockedEntity::STATUS_ACTIVE,
        ]);
    }

    public function blockIp(?string $ipAddress, string $reason, ?User $blockedBy = null): ?BlockedEntity
    {
        return $this->block(BlockedEntity::TYPE_IP_ADDRESS, $ipAddress, $reason, $blockedBy);
    }

    public function blockMac(?string $macAddress, string $reason, ?User $blockedBy = null): ?BlockedEntity
    {
        return $this->block(BlockedEntity::TYPE_MAC_ADDRESS, $macAddress, $reason, $blockedBy);
    }

    public function blockUser(User $user, string $reason, ?User $blockedBy = null): ?BlockedEntity
    {
        return $this->block(BlockedEntity::TYPE_USER, (string) $user->id, $reason, $blockedBy);
    }

    public function blockMatchingDevice(Request $request, string $reason, ?User $blockedBy = null): ?BlockedEntity
    {
        $device = $this->matchingKnownDevice($request);

        if (!$device) {
            return null;
        }

        return $this->block(BlockedEntity::TYPE_DEVICE, (string) $device->id, $reason, $blockedBy);
    }

    public function expireOldBlocks(): void
    {
        if (!Schema::hasTable('blocked_entities')) {
            return;
        }

        BlockedEntity::where('status', BlockedEntity::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => BlockedEntity::STATUS_EXPIRED]);
    }

    private function activeBlock(string $entityType, string $entityValue): ?BlockedEntity
    {
        return BlockedEntity::active()
            ->where('entity_type', $entityType)
            ->where('entity_value', $entityValue)
            ->first();
    }

    private function normalizeEntityValue(string $entityType, ?string $entityValue): ?string
    {
        if ($entityValue === null || trim($entityValue) === '') {
            return null;
        }

        $entityValue = trim($entityValue);

        if ($entityType === BlockedEntity::TYPE_MAC_ADDRESS) {
            return app(DeviceIdentityService::class)->normalizeMac($entityValue);
        }

        return $entityValue;
    }

    private function defaultExpiresAt()
    {
        $minutes = (int) config('security.auto_block_minutes', 30);

        return $minutes > 0 ? now()->addMinutes($minutes) : null;
    }

    private function matchingKnownDevice(Request $request): ?AuthorizedDevice
    {
        return app(DeviceIdentityService::class)->matchingDevices($request)->first();
    }
}
