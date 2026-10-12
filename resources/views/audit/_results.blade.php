<div class="card audit-results" id="auditResults" aria-live="polite">
    <div class="audit-results__head"><div><strong>Activity history</strong><span>{{ number_format($logs->total()) }} {{ Str::plural('entry', $logs->total()) }} found</span></div><span class="audit-results__hint"><i class="bi bi-clock-history" aria-hidden="true"></i> Newest activity first</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Module / Record</th>
                    <th>Detail</th>
                    <th>IP / MAC</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
            @php
                $badgeClass = 'action-badge--' . strtolower($log->action);
                if (!in_array($badgeClass, [
                    'action-badge--login', 'action-badge--login_failed', 'action-badge--logout',
                    'action-badge--sale', 'action-badge--product', 'action-badge--supplier',
                    'action-badge--user', 'action-badge--category', 'action-badge--security',
                    'action-badge--ids', 'action-badge--refund', 'action-badge--report',
                ], true)) {
                    $badgeClass = 'action-badge--default';
                }
            @endphp
            <tr>
                <td class="td-nowrap"><div class="audit-time"><strong>{{ \Carbon\Carbon::parse($log->timestamp)->diffForHumans() }}</strong><span>{{ \Carbon\Carbon::parse($log->timestamp)->format('M d, Y · h:i:s A') }}</span></div></td>
                <td><div class="audit-actor"><span class="audit-actor__avatar" aria-hidden="true">{{ strtoupper(substr($log->username ?: 'S', 0, 2)) }}</span><strong>{{ $log->username ?: 'System' }}</strong></div></td>
                <td class="td-muted td-muted--sm">{{ ucfirst($log->role) }}</td>
                <td><span class="action-badge {{ $badgeClass }}">{{ $log->action }}</span></td>
                <td class="td-muted">
                    <div class="td-stack">
                        <span>{{ $log->affected_table ? str_replace('_', ' ', ucfirst($log->affected_table)) : '-' }}</span>
                        @if($log->affected_record_id)<span class="td-mono">#{{ $log->affected_record_id }}</span>@endif
                    </div>
                </td>
                <td class="td-muted td-truncate"><div title="{{ $log->detail }}">{{ $log->detail }}</div></td>
                <td class="td-mono">
                    <div class="td-stack">
                        <span>{{ $log->ip_address }}</span>
                        @if($log->mac_address)<span>{{ $log->mac_address }}</span>@endif
                    </div>
                </td>
            </tr>
            @empty
            <x-empty-state
                colspan="7"
                title="No audit entries found"
                message="Activity from sign-ins, sales, and inventory changes will appear here."
                icon="audit"
            />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer" data-audit-pagination>
        <div class="pagination-summary">
            @if($logs->total() > 0)
                Showing {{ $logs->firstItem() }}-{{ $logs->lastItem() }} of {{ $logs->total() }} audit entries
            @else
                No audit entries found
            @endif
        </div>

        @if($logs->hasPages())
            @php
                $currentPage = $logs->currentPage();
                $lastPage = $logs->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp

            <nav class="rules-pagination" aria-label="Audit log pagination">
                @if($logs->onFirstPage())
                    <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
                @else
                    <a class="rules-page" href="{{ $logs->previousPageUrl() }}" rel="prev">Previous</a>
                @endif

                @if($startPage > 1)
                    <a class="rules-page" href="{{ $logs->url(1) }}">1</a>
                    @if($startPage > 2)<span class="rules-page rules-page--gap" aria-hidden="true">...</span>@endif
                @endif

                @for($page = $startPage; $page <= $endPage; $page++)
                    @if($page === $currentPage)
                        <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="rules-page" href="{{ $logs->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)<span class="rules-page rules-page--gap" aria-hidden="true">...</span>@endif
                    <a class="rules-page" href="{{ $logs->url($lastPage) }}">{{ $lastPage }}</a>
                @endif

                @if($logs->hasMorePages())
                    <a class="rules-page" href="{{ $logs->nextPageUrl() }}" rel="next">Next</a>
                @else
                    <span class="rules-page is-disabled" aria-disabled="true">Next</span>
                @endif
            </nav>
        @endif
    </div>
</div>
