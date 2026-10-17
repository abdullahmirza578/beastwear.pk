<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(ShippingSetting::current()->toAdminArray());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nationwide' => ['required', 'boolean'],
            'defaultCharge' => ['required', 'integer', 'min:0'],
            'freeThreshold' => ['required', 'integer', 'min:0'],
            'karachiCharge' => ['required', 'integer', 'min:0'],
            'otherCityCharge' => ['required', 'integer', 'min:0'],
            'remoteAreaCharge' => ['required', 'integer', 'min:0'],
            'karachiDays' => ['required', 'string', 'max:50'],
            'otherDays' => ['required', 'string', 'max:50'],
            'courier' => ['required', 'string', 'max:100'],
            'codEnabled' => ['required', 'boolean'],
            'codFee' => ['required', 'integer', 'min:0'],
        ]);

        $settings = ShippingSetting::current();
        $settings->update([
            'nationwide' => $data['nationwide'],
            'default_charge' => $data['defaultCharge'],
            'free_threshold' => $data['freeThreshold'],
            'karachi_charge' => $data['karachiCharge'],
            'other_city_charge' => $data['otherCityCharge'],
            'remote_area_charge' => $data['remoteAreaCharge'],
            'karachi_days' => $data['karachiDays'],
            'other_days' => $data['otherDays'],
            'courier' => $data['courier'],
            'cod_enabled' => $data['codEnabled'],
            'cod_fee' => $data['codFee'],
        ]);
        SiteSetting::current()->update(['cod_enabled' => $data['codEnabled']]);

        return response()->json($settings->fresh()->toAdminArray());
    }
}
