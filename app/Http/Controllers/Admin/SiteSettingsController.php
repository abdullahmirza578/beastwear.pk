<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiteSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(SiteSetting::current()->toAdminArray());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'storeName' => ['required', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'whatsapp' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'instagram' => ['nullable', 'url', 'max:2048'],
            'facebook' => ['nullable', 'url', 'max:2048'],
            'tiktok' => ['nullable', 'url', 'max:2048'],
            'youtube' => ['nullable', 'url', 'max:2048'],
            'minOrder' => ['required', 'integer', 'min:0'],
            'orderConfirmation' => ['required', Rule::in(['whatsapp', 'call', 'sms', 'email', 'none'])],
            'codEnabled' => ['required', 'boolean'],
            'autoConfirmPrepaid' => ['required', 'boolean'],
            'lowStockAlerts' => ['required', 'boolean'],
            'maintenance' => ['required', 'boolean'],
            'currency' => ['required', Rule::in(['PKR'])],
            'timezone' => ['required', Rule::in(['Asia/Karachi', 'Asia/Dubai', 'UTC'])],
            'bankName' => ['nullable', 'string', 'max:255'],
            'bankAccountTitle' => ['nullable', 'string', 'max:255'],
            'bankAccountNumber' => ['nullable', 'string', 'max:100'],
            'jazzcashAccountTitle' => ['nullable', 'string', 'max:255'],
            'jazzcashAccountNumber' => ['nullable', 'string', 'max:100'],
            'easypaisaAccountTitle' => ['nullable', 'string', 'max:255'],
            'easypaisaAccountNumber' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:1024'],
        ]);

        $settings = SiteSetting::current();
        $oldLogo = $settings->logo;
        $oldFavicon = $settings->favicon;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->storePublicly('site-settings', 'public');
        }
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->storePublicly('site-settings', 'public');
        }

        DB::transaction(function () use ($settings, $data): void {
            $settings->update([
                'store_name' => $data['storeName'],
                'tagline' => $data['tagline'] ?? '',
                'logo' => $data['logo'] ?? $settings->logo,
                'favicon' => $data['favicon'] ?? $settings->favicon,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'whatsapp' => $data['whatsapp'],
                'address' => $data['address'] ?? '',
                'instagram' => $data['instagram'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'tiktok' => $data['tiktok'] ?? null,
                'youtube' => $data['youtube'] ?? null,
                'min_order' => $data['minOrder'],
                'order_confirmation' => $data['orderConfirmation'],
                'cod_enabled' => $data['codEnabled'],
                'auto_confirm_prepaid' => $data['autoConfirmPrepaid'],
                'low_stock_alerts' => $data['lowStockAlerts'],
                'maintenance' => $data['maintenance'],
                'currency' => $data['currency'],
                'timezone' => $data['timezone'],
                'bank_name' => $data['bankName'] ?? null,
                'bank_account_title' => $data['bankAccountTitle'] ?? null,
                'bank_account_number' => $data['bankAccountNumber'] ?? null,
                'jazzcash_account_title' => $data['jazzcashAccountTitle'] ?? null,
                'jazzcash_account_number' => $data['jazzcashAccountNumber'] ?? null,
                'easypaisa_account_title' => $data['easypaisaAccountTitle'] ?? null,
                'easypaisa_account_number' => $data['easypaisaAccountNumber'] ?? null,
            ]);

            ShippingSetting::current()->update(['cod_enabled' => $data['codEnabled']]);
        });

        if (isset($data['logo']) && $oldLogo !== $data['logo']) {
            SiteSetting::deleteUploadedAsset($oldLogo);
        }
        if (isset($data['favicon']) && $oldFavicon !== $data['favicon']) {
            SiteSetting::deleteUploadedAsset($oldFavicon);
        }

        return response()->json($settings->fresh()->toAdminArray());
    }
}
