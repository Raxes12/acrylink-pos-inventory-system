@extends('layouts.app')
@section('title', 'Security Rules')
@section('page-label', 'Security Rules')

@section('content')
<x-security-nav />
<x-page-header
    title="Security Rules"
    subtitle="Control when suspicious activity is detected and how the system responds."
/>

<div class="security-rule-guide" aria-label="How security rules work">
    <div><span class="security-rule-guide__icon"><i class="bi bi-sliders" aria-hidden="true"></i></span><span><strong>Detection</strong><small>Threshold is the whole-number trigger. Window is how many minutes the system watches.</small></span></div>
    <div><span class="security-rule-guide__icon"><i class="bi bi-exclamation-diamond" aria-hidden="true"></i></span><span><strong>Response</strong><small>Severity sets priority. Action decides whether to log, alert, notify, or block.</small></span></div>
    <div><span class="security-rule-guide__icon"><i class="bi bi-toggle-on" aria-hidden="true"></i></span><span><strong>Status</strong><small>Disabled rules stop future detection immediately after you save the row.</small></span></div>
</div>

<div class="card security-rules-card">
    <div class="card-header">
        <div><span class="card-title"><x-icon name="rules" /> IDS Rule Configuration</span><span class="security-rules-caption">Each row saves independently. Changes affect future activity only.</span></div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Rule</th>
                    <th>Type</th>
                    <th>Threshold</th>
                    <th>Window</th>
                    <th>Severity</th>
                    <th>Action</th>
                    <th>Status</th>
                    <th class="td-actions">Save</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rules as $rule)
            @php
                $formId = 'security-rule-'.$rule->security_rule_id;
                $hasRowErrors = (string) old('_rule_id') === (string) $rule->security_rule_id && $errors->any();
                $thresholdValue = $hasRowErrors ? old('threshold_value') : ($rule->threshold_value !== null ? (int) $rule->threshold_value : null);
                $windowValue = $hasRowErrors ? old('time_window_minutes') : $rule->time_window_minutes;
                $severityValue = $hasRowErrors ? old('severity') : $rule->severity;
                $actionValue = $hasRowErrors ? old('action') : $rule->action;
                $activeValue = $hasRowErrors ? old('is_active') : $rule->is_active;
                $thresholdUnit = match ($rule->rule_name) {
                    'large_transaction' => 'amount (₱)',
                    'excessive_discount' => 'percent (%)',
                    'bulk_inventory_change' => 'items',
                    'large_report_date_range' => 'days',
                    default => 'occurrences',
                };
            @endphp
            <tr class="security-rule-row {{ $hasRowErrors ? 'has-errors' : '' }}">
                <td>
                    <div class="fw-bold">{{ str_replace('_', ' ', ucwords($rule->rule_name, '_')) }}</div>
                    <div class="security-rule-meta">{{ $rule->rule_name }}</div>
                    @if($rule->rule_name === 'rogue_device')
                    <div class="security-rule-scope"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Controls detection and auto-blocking. Device whitelist access remains enforced separately.</span></div>
                    @elseif($rule->rule_name === 'failed_login_limit')
                    <div class="security-rule-scope"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Threshold controls login attempts; Window controls lockout duration and detection history.</span></div>
                    @endif
                </td>
                <td>
                    <span class="status status-low security-rule-type">{{ str_replace('_', ' ', $rule->rule_type) }}</span>
                </td>
                <td>
                    @if($rule->usesThreshold())
                    <label class="security-rule-control"><span class="visually-hidden">Threshold for {{ $rule->rule_name }}</span><input form="{{ $formId }}" class="security-rule-input" type="number" name="threshold_value" value="{{ $thresholdValue }}" min="{{ $rule->minimumThreshold() }}" max="9999999999" step="1" inputmode="numeric" required><small>{{ $thresholdUnit }}</small></label>@else<span class="security-rule-na" title="This rule triggers without a numeric threshold">Not used</span>@endif</td>
                <td>
                    @if($rule->usesWindow())
                    <label class="security-rule-control"><span class="visually-hidden">Time window for {{ $rule->rule_name }}</span><input form="{{ $formId }}" class="security-rule-input" type="number" name="time_window_minutes" value="{{ $windowValue }}" min="1" max="10080" step="1" inputmode="numeric" required><small>minutes</small></label>@else<span class="security-rule-na" title="This rule evaluates each activity immediately">Immediate</span>@endif</td>
                <td>
                    <select form="{{ $formId }}" name="severity" class="security-rule-select-md" aria-label="Severity for {{ $rule->rule_name }}">
                        @foreach($severityOptions as $severity)
                        <option value="{{ $severity }}" {{ $severityValue === $severity ? 'selected' : '' }}>{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select form="{{ $formId }}" name="action" class="security-rule-select-sm" aria-label="Action for {{ $rule->rule_name }}">
                        @foreach($rule->supportedActions() as $action)
                        <option value="{{ $action }}" {{ $actionValue === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input form="{{ $formId }}" type="hidden" name="is_active" value="0">
                    <label class="security-rule-toggle">
                        <input form="{{ $formId }}" type="checkbox" name="is_active" value="1" {{ $activeValue ? 'checked' : '' }}>
                        <span class="security-rule-toggle__track" aria-hidden="true"><span></span></span>
                        <span class="security-rule-toggle__state"></span>
                    </label>
                </td>
                <td>
                    <form id="{{ $formId }}" method="POST" action="{{ route('security.rules.update', $rule) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_rule_id" value="{{ $rule->security_rule_id }}">
                        <button class="btn btn-primary btn-sm security-save-rule" type="submit"><i class="bi bi-check-lg" aria-hidden="true"></i> Save</button>
                    </form>
                </td>
            </tr>
            @empty
            <x-empty-state colspan="8" title="No security rules configured" message="Add IDS rules to control anomaly thresholds and responses." icon="rules" />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing {{ $rules->firstItem() ?? 0 }}-{{ $rules->lastItem() ?? 0 }} of {{ $rules->total() }} rules
        </div>

        @if($rules->hasPages())
        @php
            $currentPage = $rules->currentPage();
            $lastPage = $rules->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        @endphp
        <nav class="rules-pagination" aria-label="Security rules pagination">
            @if($rules->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $rules->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                @endif

                @if($page === $currentPage)
                    <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="rules-page" href="{{ $rules->url($page) }}">{{ $page }}</a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if($rules->hasMorePages())
                <a class="rules-page" href="{{ $rules->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            @endif
        </nav>
        @endif
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/security-pages-1.css')
@endpush
