<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Header: Specific to Refund --}}
    <x-mail.header
        headerText="Refund Completed"
        subtitle="The refund for your order has been successfully processed." />

    {{-- Greeting: Personalized with Order No --}}
    <x-mail.greeting
        :order="$order"
        :name="$order->user->name ?? $order->name"
        message="Good news! The refund for your order <strong>{{ $order->order_no }}</strong> has been successfully processed. You can find the summary of the refund below." />

    {{-- Refund Summary --}}
    <div style="margin-bottom:32px; padding:16px; border-radius:6px; border:1px solid #ddd;">
        <h2 style="font-size:18px; font-weight:600; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:6px;">🧾 Refund Summary</h2>
        <div style="font-size: 14px; line-height: 1.8;">
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Order No</span>
                <span>{{ $order->order_no }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Order Date</span>
                <span>{{ formatDateTime($order->created_at) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #eee;">
                <span style="font-weight:600; color: #d9534f;">Refund Amount</span>
                <span style="font-weight:700; color: #d9534f;">{{ formatMoney($order->refund->amount) }} USD</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Refund Date</span>
                <span>{{ formatDateTime($order->refund->updated_at) }}</span>
            </div>
        </div>
    </div>

    {{-- Support Channels --}}
    <x-mail.support />

    {{-- Footer --}}
    <x-mail.footer />

</div>