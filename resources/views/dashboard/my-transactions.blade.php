@extends('layouts.app')
@section('title', 'My Transactions')
@section('page-label', 'My Transactions')

@section('content')
<x-page-header title="My Transactions" subtitle="Receipts processed by your cashier account.">
    <x-slot:actions><a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a></x-slot:actions>
</x-page-header>

<div class="grid-2 cashier-summary">
    <div class="card metric-card">
        <div class="metric-topline">
            <span class="metric-label">My Total Sales Today</span>
            <span class="metric-icon icon-sales">PHP</span>
        </div>
        <div class="metric-value" style="color:var(--accent2);">&#8369;{{ number_format($todaySales, 2) }}</div>
        <div class="metric-note">{{ $todayTxns }} receipt{{ $todayTxns !== 1 ? 's' : '' }} processed today</div>
    </div>
    <div class="card metric-card">
        <div class="metric-topline">
            <span class="metric-label">All My Receipts</span>
            <span class="metric-icon icon-txn">POS</span>
        </div>
        <div class="metric-value">{{ number_format($transactions->total()) }}</div>
        <div class="metric-note">Total receipts under your account</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Transaction History</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Payment</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td><span class="receipt-code">{{ $t->receipt_no }}</span></td>
                    <td>{{ $t->date->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->time)->format('h:i A') }}</td>
                    <td>{{ \App\Models\Transaction::paymentMethodLabel($t->payment_method) }}</td>
                    <td class="text-right fw-800" style="color:var(--accent2);">&#8369;{{ number_format($t->netTotal(), 2) }}</td>
                    <td><span class="status status-active">{{ ucfirst(str_replace('_', ' ', $t->status ?? 'completed')) }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('pos.receipt', $t) }}" class="btn btn-secondary btn-sm">Receipt</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">No transactions found for your account.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="transaction-pagination">{{ $transactions->links() }}</div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/dashboard-my-transactions-1.css')
@endpush
