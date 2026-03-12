<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Header logic --}}
    <x-mail.header
        :headerText="$order->is_gifted ? 'Gift Delivered' : 'Completed'"
        :subtitle="$order->is_gifted ? 'Your gift order has been delivered successfully.' : 'Your order has been completed successfully.'" />

    {{-- Greeting logic --}}
    @if($order->is_gifted)
    <x-mail.greeting
        :order="$order"
        :name="$order->user->name ?? $order->name"
        message="Hello <strong>{{ $order->user->name ?? $order->name }}</strong>, your gift order <strong>{{ $order->order_no }}</strong> has been delivered to <strong>{{ $order->gifted_email }}</strong>. You can see the order details below." />
    @else
    <x-mail.greeting
        :order="$order"
        :name="$order->user->name ?? $order->name"
        message="Your order <strong>{{ $order->order_no }}</strong> has been completed successfully. You can now access your purchased items below." />
    @endif

    {{-- Order Summary --}}
    <x-mail.order-summary :order="$order" />

    <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">🎁 Product Details</h2>

    {{-- Item loop with gifted variables applied --}}
    @foreach($order->items as $item)
    <x-mail.order-item
        :item="$item"   
        :iteration="$loop->iteration"
        :showCodes="!$order->is_gifted"
        :showDetailedQuantity="!$order->is_gifted" />
    @endforeach

    {{-- Refund Details --}}
    @if(!$order->is_gifted && $order->refund)
    <x-mail.refund-details :refund="$order->refund" />
    @endif

    {{-- Next Steps --}}
    @if($order->user_id)
    <x-mail.next-steps :order="$order" />
    @endif

    {{-- Support --}}
    <x-mail.support />

    {{-- Footer --}}
    <x-mail.footer />

</div>