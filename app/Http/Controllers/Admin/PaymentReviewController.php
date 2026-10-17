<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.payments.index', [
            'payments' => Payment::with('order.user')
                ->whereIn('payment_method', ['Bank Transfer', 'JazzCash', 'EasyPaisa'])
                ->where('status', 'pending')
                ->latest()
                ->paginate(25),
        ]);
    }

    public function proof(Payment $payment)
    {
        abort_unless($payment->proof_path, 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($payment->proof_path), 404, 'The stored payment proof file is missing.');

        return response()->file($disk->path($payment->proof_path), [
            'Content-Disposition' => 'inline; filename="payment-proof-'.$payment->id.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function approve(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment): void {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            abort_unless(
                in_array($lockedPayment->payment_method, ['Bank Transfer', 'JazzCash', 'EasyPaisa'], true)
                && $lockedPayment->status === 'pending'
                && $lockedPayment->proof_path
                && $order->payment_status === 'pending'
                && $order->order_status !== 'cancelled',
                422,
                'Only a pending payment with a submitted receipt can be approved.'
            );

            $lockedPayment->update(['status' => 'paid', 'paid_at' => now()]);
            $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
        });

        return back()->with('status', 'Payment approved and marked paid.');
    }

    public function reject(Payment $payment): RedirectResponse
    {
        $proofPath = DB::transaction(function () use ($payment): ?string {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            abort_unless(
                in_array($lockedPayment->payment_method, ['Bank Transfer', 'JazzCash', 'EasyPaisa'], true)
                && $lockedPayment->status === 'pending'
                && $lockedPayment->proof_path
                && $order->payment_status === 'pending'
                && $order->order_status !== 'cancelled',
                422,
                'Only a pending payment with a submitted receipt can be rejected.'
            );

            foreach ($order->items()->get() as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->first();
                    $variant?->increment('stock_quantity', $item->quantity);
                } elseif ($item->product_id) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                    $product?->increment('stock_quantity', $item->quantity);
                }
            }

            $proofPath = $lockedPayment->proof_path;
            $lockedPayment->update(['status' => 'rejected']);
            $order->update([
                'payment_status' => 'rejected',
                'order_status' => 'cancelled',
            ]);
            if ($order->coupon_id && CouponUsage::where('order_id', $order->id)->delete()) {
                Coupon::whereKey($order->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
            }

            return $proofPath;
        });

        if ($proofPath) {
            Storage::disk('local')->delete($proofPath);
        }

        return back()->with('status', 'Payment rejected. The order was cancelled and its stock returned to inventory.');
    }
}
