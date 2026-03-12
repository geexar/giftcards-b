<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Dynamic Header --}}
    <x-mail.header
        :headerText="$order->is_gifted ? 'Gift Order Failed' : 'Order Failed'"
        :subtitle="$order->is_gifted ? 'The gift could not be delivered.' : 'Your order could not be completed.'" />

    {{-- Dynamic Greeting --}}
    <x-mail.greeting
        :order="$order"
        :name="$order->user->name ?? $order->name"
        :message="$order->is_gifted 
            ? 'We’re sorry to inform you that your gift order <strong>' . $order->order_no . '</strong> could not be completed and has been marked as <strong>Failed</strong>.<br><br>The gift could not be delivered to <strong>' . $order->gifted_email . '</strong>. This usually happens if the items are unavailable at the time of delivery or due to a provider error.<br><br>A full refund for the charged amount has been initiated and will be processed shortly.'
            : 'We’re sorry to inform you that your order <strong>' . $order->order_no . '</strong> could not be completed and has been marked as <strong>Failed</strong>.<br><br>This may happen when items become unavailable at the provider, or due to a processing issue during fulfillment.<br><br>A refund for the charged amount will be processed shortly.'" />

    {{-- Order Summary --}}
    <x-mail.order-summary :order="$order" />

    {{-- Item Details --}}
    <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">🎁 Order Items</h2>
    @foreach($order->items as $item)
    <x-mail.order-item
        :item="$item"
        :iteration="$loop->iteration"
        :showCodes="false"
        :showDetailedQuantity="true" />
    @endforeach

    {{-- Refund Details --}}
    @if($order->refund)
    <x-mail.refund-details :refund="$order->refund" />
    @endif

    {{-- Next Steps (registered users only) --}}
    @if($order->user_id)
    <x-mail.next-steps :order="$order" />
    @endif

    {{-- Support --}}
    <x-mail.support />

    {{-- Footer --}}
    <x-mail.footer />

</div>