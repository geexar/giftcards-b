<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Dynamic Header --}}
    <x-mail.header
        headerText="Order Item Canceled"
        :subtitle="'Your item from order ' . $order->order_no . ' has been canceled.'" />

    {{-- Dynamic Greeting --}}
    <x-mail.greeting
        :order="$order"
        :message="'Your product <strong>' . $productName . '</strong> from order <strong>' . $order->order_no . '</strong> has been canceled successfully.<br><br>Refund processing has been initiated for this item. Our team will review and complete the refund manually within the standard processing period.'" />

    {{-- Order Summary --}}
    <x-mail.order-summary :order="$order" />

    {{-- Refund Details --}}
    <x-mail.refund-details :refund="$order->refund" />

    {{-- Support --}}
    <x-mail.support />

    {{-- Footer --}}
    <x-mail.footer />

</div>