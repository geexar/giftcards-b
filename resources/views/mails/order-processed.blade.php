<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Dynamic Header --}}
    <x-mail.header
        :headerText="$order->is_gifted ? 'Gift Order Processed' : 'Order Processed'"
        subtitle="Refund is underway for unavailable items." />

    {{-- Dynamic Greeting --}}
    <x-mail.greeting
        :order="$order"
        :name="$order->user->name ?? $order->name"
        :message="$order->is_gifted 
            ? 'Your gift order <strong>' . $order->order_no . '</strong> has been processed. Some items in your gift could not be delivered and a refund has been initiated. Items that were successfully delivered have already been sent to the recipient (<strong>' . $order->gifted_email . '</strong>).'
            : 'Your order <strong>' . $order->order_no . '</strong> has been fully processed. Some products in your order were unavailable or canceled, and a refund has been initiated for those items. You can still access items that were successfully delivered below.'" />

    {{-- Order Summary --}}
    <x-mail.order-summary :order="$order" />

    {{-- Fulfilled Products --}}
    <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">
        {{ $order->is_gifted ? '🎁 Delivered to Recipient' : '🎁 Fulfilled Products' }}
    </h2>

    @php
    $fulfilledItems = $order->items->whereIn('status', [
    \App\Enums\OrderItemStatus::COMPLETED,
    \App\Enums\OrderItemStatus::PARTIALLY_FULFILLED
    ]);
    @endphp

    @foreach($fulfilledItems as $item)
    <x-mail.order-item
        :item="$item"
        :iteration="$loop->iteration"
        :showCodes="!$order->is_gifted"
        :showDetailedQuantity="true" />
    @endforeach

    {{-- Not Delivered --}}
    @php
    $notDeliveredItems = $order->items->whereIn('status', [
    \App\Enums\OrderItemStatus::CANCELED,
    \App\Enums\OrderItemStatus::REJECTED,
    \App\Enums\OrderItemStatus::FAILED,
    ]);
    @endphp

    @if($notDeliveredItems->isNotEmpty())
    <h2 style="font-size:16px; font-weight:600; margin:32px 0 16px;">
        ❌ Not Delivered
    </h2>

    @foreach($notDeliveredItems as $item)
    <x-mail.order-item
        :item="$item"
        :iteration="$loop->iteration"
        :showCodes="false"
        :showDetailedQuantity="true" />
    @endforeach
    @endif

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