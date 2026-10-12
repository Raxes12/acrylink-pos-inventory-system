<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\BlockedEntity;
use App\Services\BlockedEntityService;
use Closure;
use Illuminate\Http\Request;

class CheckBlockedEntity
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->is('logout')) {
            return $next($request);
        }

        $user = $request->user();
        $block = app(BlockedEntityService::class)->activeBlockForRequest($request, $user);

        if (!$block) {
            return $next($request);
        }

        if ($this->canAdminManageBlock($request, $block)) {
            return $next($request);
        }

        AuditLog::record(
            'SECURITY',
            "Blocked entity denied access: {$block->entity_type} {$block->entity_value}. Reason: {$block->reason}",
            $user
        );

        abort(403, 'Access blocked by security policy. Please contact an administrator.');
    }

    private function canAdminManageBlock(Request $request, BlockedEntity $block): bool
    {
        $user = $request->user();

        return $request->is('security/blocked-entities*')
            && $user?->isAdmin()
            && $block->entity_type !== BlockedEntity::TYPE_USER;
    }
}
