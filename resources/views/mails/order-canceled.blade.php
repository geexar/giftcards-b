<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Dynamic Header --}}
    <x-mail.header
        :headerText="$order->is_gifted ? 'Gift Order Canceled' : 'Order Canceled'"
        :subtitle="$order->is_gifted ? 'The gift order has been canceled.' : 'Your order has been canceled successfully.'" />

    {{-- Dynamic Greeting --}}
    <x-mail.greeting
        :order="$order"
        :name="$order->user->name ?? $order->name"
        :message="$order->is_gifted 
            ? 'Your gift order <strong>' . $order->order_no . '</strong> has been canceled. The gift will not be delivered to <strong>' . $order->gifted_email . '</strong>.<br><br>Refund processing has been initiated for all items in this order. Our team will review and complete the refund manually within the standard processing period.'
            : 'Your order <strong>' . $order->order_no . '</strong> has been canceled successfully.<br><br>Refund processing has been initiated for all items in this order. Our team will review and complete the refund manually within the standard processing period.'" />

    {{-- Order Summary --}}
    <x-mail.order-summary :order="$order" />

    {{-- Refund Details --}}
    <x-mail.refund-details :refund="$order->refund" />

    {{-- Support --}}
    <x-mail.support />

    {{-- Footer --}}
    <x-mail.footer />

</div>