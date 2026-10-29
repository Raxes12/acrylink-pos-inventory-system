<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Services\SystemSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'businessLogoUrl' => $settings->businessLogoUrl(),
            'hasCustomBusinessLogo' => $settings->hasCustomBusinessLogo(),
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
            'business_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'reset_business_logo' => ['nullable', 'boolean'],
        ]);

        $previousLogoPath = (string) $settings->get('business.logo_path', SystemSettingsService::DEFAULTS['business.logo_path']);
        $logoPath = $previousLogoPath;

        if ($request->boolean('reset_business_logo')) {
            $logoPath = SystemSettingsService::DEFAULTS['business.logo_path'];
        }

        if ($request->hasFile('business_logo')) {
            $logoPath = $request->file('business_logo')->store('business-logos', 'public');
        }

        $settings->putMany([
            'business.name' => $data['business_name'],
            'business.address' => $data['business_address'] ?? '',
            'business.contact_number' => $data['business_contact_number'] ?? '',
            'business.email' => $data['business_email'] ?? '',
            'business.logo_path' => $logoPath,
        ], 'general');

        if ($previousLogoPath !== $logoPath && str_starts_with($previousLogoPath, 'business-logos/')) {
            Storage::disk('public')->delete($previousLogoPath);
        }
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
