<div style="margin-bottom:32px; padding:16px; border-radius:6px; border:1px solid #ddd;">
    <h2 style="font-size:18px; font-weight:600; margin-bottom:16px; border-bottom:1px solid #eee; padding-bottom:10px;">🧾 Order Summary</h2>

    {{-- Using Flex-like behavior with spacing --}}
    <div style="font-size: 14px; line-height: 1.6;">

        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="font-weight:600; color: #555;">Order No</span>
            <span style="text-align: right;">{{ $order->order_no }}</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="font-weight:600; color: #555;">Order Date</span>
            <span style="text-align: right;">{{ formatDateTime($order->created_at) }}</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="font-weight:600; color: #555;">Payment Method</span>
            <span style="text-align: right;">{{ app(App\Services\Admin\PaymentMethodService::class)->getPaymentMethodName($order->payment_method_id) }}</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="font-weight:600; color: #555;">Order Status</span>
            <span style="text-align: right; font-weight: 600;">{{ ucwords(str_replace('_', ' ', $order->status->value)) }}</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #eee;">
            <span style="font-weight:600; color: #000;">Total Paid</span>
            <span style="text-align: right; font-weight: 700; color: #000;">{{ formatMoney($order->total) }} USD</span>
        </div>

        @if($order->refund)
        <div style="display: flex; justify-content: space-between; margin-top: 6px;">
            <span style="font-weight:600;">Refund Amount</span>
            <span style="text-align: right; font-weight: 600;">
                {{ formatMoney($order->refund->amount) }} USD
            </span>
        </div>
        @endif
    </div>
</div>