<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Services\SystemSettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const SECTIONS = ['general', 'inventory', 'sales', 'receipt', 'security', 'maintenance'];

    public function index(Request $request, SystemSettingsService $settings)
    {
        $section = in_array($request->query('section'), self::SECTIONS, true)
            ? $request->query('section')
            : 'general';

        return view('settings.index', [
            'section' => $section,
            'settings' => $settings->all(),
            'categoryCount' => ProductCategory::count(),
            'units' => Unit::orderBy('name')->get(),
            'databaseDriver' => config('database.default'),
        ]);
    }

    public function updateGeneral(Request $request, SystemSettingsService $settings)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'business_contact_number' => ['nullable', 'string', 'max:40'],
            'business_email' => ['nullable', 'email', 'max:120'],
        ]);

        $settings->putMany([
            'business.name' => $data['business_name'],
            'business.address' => $data['business_address'] ?? '',
            'business.contact_number' => $data['business_contact_number'] ?? '',
            'business.email' => $data['business_email'] ?? '',
        ], 'general');
        AuditLog::record('SETTINGS', 'Updated general business settings.');

        return back()->with('success', 'General settings saved.');
    }

    public function updateReceipt(Request $request, SystemSettingsService $settings)
    {
        $data = $request->validate([
            'footer_message' => ['required', 'string', 'max:240'],
            'show_cashier' => ['nullable', 'boolean'],
            'show_customer' => ['nullable', 'boolean'],
            'show_payment_method' => ['nullable', 'boolean'],
        ]);

        $settings->putMany([
            'receipt.footer_message' => $data['footer_message'],
            'receipt.show_cashier' => $request->boolean('show_cashier'),
            'receipt.show_customer' => $request->boolean('show_customer'),
            'receipt.show_payment_method' => $request->boolean('show_payment_method'),
        ], 'receipt');
        AuditLog::record('SETTINGS', 'Updated receipt presentation settings.');

        return back()->with('success', 'Receipt settings saved.');
    }
}
