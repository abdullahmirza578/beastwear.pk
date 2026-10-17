<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentProofController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $existingPaymentId = $order->payments()->latest()->value('id');
        $data = $request->validate([
            'transaction_id' => ['nullable', 'string', 'max:255', Rule::unique('payments', 'transaction_id')->ignore($existingPaymentId)],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $oldProof = DB::transaction(function () use ($order, $request, $data): ?string {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = $lockedOrder->payments()->latest()->lockForUpdate()->first();
            if (! $payment || $lockedOrder->payment_method === 'COD'
                || $lockedOrder->payment_status !== 'pending'
                || $lockedOrder->order_status === 'cancelled') {
                throw ValidationException::withMessages([
                    'proof' => 'This order is not awaiting a prepaid receipt.',
                ]);
            }

            $oldProof = $payment->proof_path;
            $path = $request->file('proof')->store('payment-proofs', 'local');
            $payment->update([
                'proof_path' => $path,
                'transaction_id' => $data['transaction_id'] ?? null,
                'status' => 'pending',
                'payment_details' => [
                    'proof_submitted_at' => now()->toISOString(),
                ],
            ]);

            return $oldProof;
        });

        if ($oldProof) {
            Storage::disk('local')->delete($oldProof);
        }

        return redirect()->route('orders.show', $order)
            ->with('status', 'Payment receipt submitted. Your payment will remain pending until an admin verifies it.');
    }
}
