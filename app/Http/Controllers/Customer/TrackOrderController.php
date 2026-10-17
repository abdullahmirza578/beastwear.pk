<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    public function show(): View
    {
        return view('account.track-order', ['order' => null]);
    }

    public function lookup(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:30'],
        ]);

        $identifier = trim($data['order_number']);
        $normalizedIdentifier = strtoupper(preg_replace('/[\s-]+/', '', $identifier));
        $orders = Order::with('items')
            ->where(function ($query) use ($identifier, $normalizedIdentifier): void {
                $query->whereRaw('UPPER(order_number) = ?', [strtoupper($identifier)])
                    ->orWhereRaw(
                        "REPLACE(REPLACE(UPPER(public_tracking_id), '-', ''), ' ', '') = ?",
                        [$normalizedIdentifier]
                    )
                    ->orWhereRaw(
                        "UPPER(REPLACE(REPLACE(tracking_number, ' ', ''), '-', '')) = ?",
                        [$normalizedIdentifier]
                    );
            })
            ->get();

        $phone = $this->normalizePhone($data['customer_phone']);
        $order = $orders->first(
            fn (Order $candidate): bool => $this->normalizePhone($candidate->customer_phone) === $phone
        );

        if (! $order) {
            return back()->withInput()->withErrors([
                'order_number' => 'No order matches that order or parcel tracking number and phone number.',
            ]);
        }

        return view('account.track-order', compact('order'));
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0092')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '92') && strlen($digits) === 12) {
            $digits = '0'.substr($digits, 2);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '3')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }
}
