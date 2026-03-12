<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Header --}}
    <x-mail.header
        headerText="🎁 You’ve Received a Gift!"
        subtitle="A special surprise from {{ $order->user?->name ?? $order->name }}" />

    {{-- Greeting --}}
    <p style="margin:0 0 32px;">
        You’ve received a gift from <strong> {{ $order->user?->name ?? $order->name }} </strong>! 🎉<br>Here are your gift details and codes below.
    </p>

    {{-- Recipient Info Box --}}
    <div style="margin: 24px 0; border-top: 1px dashed #ddd; border-bottom: 1px dashed #ddd; padding: 16px 0; background: #fafafa; text-align: center;">
        <p style="margin: 0; font-size: 14px; color: #666;">Order ID: <strong>#{{ $order->order_no }}</strong></p>
        <p style="margin: 0; font-size: 14px; color: #666;">Received On: <strong>{{ formatDate($order->created_at) }}</strong></p>
    </div>

    {{-- Gift Items Section --}}
    <h2 style="font-size:16px; font-weight:600; margin-bottom:16px; text-transform: uppercase;">
        🎁 Your Gift Items
    </h2>

    @php
    $items = $order->items->whereIn('status', [\App\Enums\OrderItemStatus::COMPLETED, \App\Enums\OrderItemStatus::PARTIALLY_FULFILLED]);
    @endphp

    @foreach($items as $item)
    <x-mail.order-item
        :item="$item"
        :iteration="$loop->iteration"
        :showCodes="true"
        :showDetailedQuantity="false"
        :showStatus="false"
        :showPrice="false" />
    @endforeach

    {{-- Support --}}
    <div style="margin-top: 32px;">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">💬 Need Help?</h3>
        <x-mail.support />
    </div>

    {{-- Footer --}}
    <x-mail.footer />

</div>