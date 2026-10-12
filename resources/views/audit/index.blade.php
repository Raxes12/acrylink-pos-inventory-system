@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-label', 'Audit Log')

@section('content')
<x-page-header
    title="Audit Log"
    subtitle="Investigate system activity, trace changes, and identify who performed each action."
/>

<section class="audit-summary" aria-label="Audit activity overview">
    <article><span class="audit-summary__icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span><span><small>All activity</small><strong>{{ number_format($auditSummary['total']) }}</strong><em>Complete recorded history</em></span></article>
    <article><span class="audit-summary__icon is-today"><i class="bi bi-calendar-check" aria-hidden="true"></i></span><span><small>Today</small><strong>{{ number_format($auditSummary['today']) }}</strong><em>Events recorded today</em></span></article>
    <article><span class="audit-summary__icon is-security"><i class="bi bi-shield-check" aria-hidden="true"></i></span><span><small>Security activity</small><strong>{{ number_format($auditSummary['security']) }}</strong><em>IDS and access events</em></span></article>
    <article><span class="audit-summary__icon is-users"><i class="bi bi-people" aria-hidden="true"></i></span><span><small>Accountable users</small><strong>{{ number_format($auditSummary['users']) }}</strong><em>Users represented in logs</em></span></article>
</section>

<form
    method="GET"
    action="{{ route('audit.index') }}"
    data-results-url="{{ route('audit.results', [], false) }}"
    class="audit-filter"
    id="auditLiveFilter"
    role="search"
>
    <div class="audit-filter__primary">
        <label class="audit-search" for="auditSearch">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Search audit activity</span>
            <input id="auditSearch" type="search" name="search" value="{{ request('search') }}" placeholder="Search activity, user, action, IP, MAC, module, or record ID..." autocomplete="off">
        </label>
        <button type="button" class="audit-clear" data-audit-clear {{ collect(request()->except('page'))->filter()->isNotEmpty() ? '' : 'hidden' }}><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reset filters</button>
    </div>
    <div class="audit-filter__controls">
        <label class="audit-select"><span>Action</span><select name="action"><option value="">All actions</option>@foreach($actions as $a)<option value="{{ $a }}" @selected(request('action') == $a)>{{ ucwords(str_replace('_', ' ', $a)) }}</option>@endforeach</select></label>
        <label class="audit-select"><span>Module</span><select name="module"><option value="">All modules</option>@foreach($modules as $module)<option value="{{ $module }}" @selected(request('module') === $module)>{{ ucwords(str_replace('_', ' ', $module)) }}</option>@endforeach</select></label>
        <label class="audit-select"><span>Performed by</span><select name="user_id"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name ?: $user->username }}</option>@endforeach</select></label>
        <label class="audit-date"><span>From date</span><input type="date" name="date_from" value="{{ request('date_from') }}" max="{{ now()->toDateString() }}"></label>
        <label class="audit-date"><span>To date</span><input type="date" name="date_to" value="{{ request('date_to') }}" max="{{ now()->toDateString() }}"></label>
    </div>
    <div class="audit-filter__feedback"><span class="audit-filter-count" data-audit-active-count></span><span class="audit-live-status" data-audit-status role="status" aria-live="polite"></span></div>
</form>

@include('audit._results', ['logs' => $logs])
@endsection

@push('scripts')
@vite('resources/js/views/audit-index-1.js')
@endpush

@push('styles')
@vite('resources/css/views/audit-index-1.css')
@endpush
