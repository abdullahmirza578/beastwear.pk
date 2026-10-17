@extends('admin.layouts.layout')

@section('title', 'Payment Review | BeastWear.pk Admin')
@section('page', 'payments')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Home</a><span class="sep">/</span><span>Payment Review</span></div>
        <h1>Prepaid Payment Review</h1>
        <p class="subtitle">Verify customer receipts before marking prepaid orders as paid.</p>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-ok" role="status">{{ session('status') }}</div>
@endif

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th>Reference</th><th>Proof</th><th>Submitted</th><th>Decision</th></tr></thead>
            <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->order->order_number }}</td>
                    <td>{{ $payment->order->customer_name }}<br><small>{{ $payment->order->customer_phone }}</small></td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>Rs {{ number_format((float) $payment->amount, 0) }}</td>
                    <td>{{ $payment->transaction_id ?: '—' }}</td>
                    <td>
                        @if ($payment->proof_path)
                            <a class="btn btn-outline btn-sm" href="{{ route('admin.payments.proof', $payment) }}" target="_blank" rel="noopener">View receipt</a>
                        @else
                            <span class="hint">Not uploaded</span>
                        @endif
                    </td>
                    <td>{{ $payment->updated_at->format('d M Y H:i') }}</td>
                    <td>
                        @if ($payment->status === 'pending' && $payment->proof_path)
                            <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" style="display:inline">@csrf @method('PATCH')<button class="btn btn-primary btn-sm" type="submit">Approve</button></form>
                            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" style="display:inline" onsubmit="return confirm('Reject this payment and cancel its order? Stock will be returned.')">@csrf @method('PATCH')<button class="btn btn-danger btn-sm" type="submit">Reject</button></form>
                        @else
                            {{ ucfirst($payment->status) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="table-empty">No prepaid payments to review.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">{{ $payments->links() }}</div>
</div>
@endsection
