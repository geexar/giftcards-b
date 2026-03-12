@php
$showDetailedQuantity = $showDetailedQuantity ?? true;
$showCodes = $showCodes ?? true;
$showStatus = $showStatus ?? true;
$showPrice = $showPrice ?? true;
@endphp

<div style="border:1px solid #ddd; border-radius:6px; padding:16px; margin-bottom:16px;">

    <p style="margin:0 0 8px; font-weight:600;">
        #{{ $iteration ?? '' }}
        {{ $item->product->name }}
        @if($item->variantValue)
        - {{ $item->variantValue->value }}
        @endif
    </p>

    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
        <span>Quantity</span>
        <span>
            @if($showDetailedQuantity)
                @if($item->status == \App\Enums\OrderItemStatus::PARTIALLY_FULFILLED)
                {{ $item->fulfilled_quantity }} of {{ $item->quantity }}
                @else
                {{ $item->quantity }}
                @endif
            @else
            {{ $item->fulfilled_quantity }}
            @endif
        </span>
    </div>
    @if($showStatus)
    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
        <span>Status</span><span>{{ ucwords(str_replace('_', ' ', $item->status->value)) }}</span>
    </div>
    @endif
    @if($item->status == \App\Enums\OrderItemStatus::REJECTED && !empty($item->rejection_reason))
        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
            <span>Rejection Reason</span>
            <span>{{ $item->rejection_reason }}</span>
        </div>
    @endif

    @if($showPrice)
    <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
        <span>Total Price</span><span>{{ formatMoney($item->total) }} USD</span>
    </div>
    @endif

    @if($showCodes)
    <div style="margin-top:12px;">
        @foreach($item->codes as $code)
        <div style="border:1px solid #eee; border-radius:4px; padding:12px; margin-bottom:12px; background:#fafafa;">
            <p style="margin:0 0 4px;"><strong>Code:</strong> {{ $code->code }}</p>
            @if($code->pin_code)
            <p style="margin:0 0 4px;"><strong>PIN Code:</strong> {{ $code->pin_code }}</p>
            @endif
            @if($code->expiry_date)
            <p style="margin:0;"><strong>Expiry:</strong> {{ formatDate($code->expiry_date) }}</p>
            @endif
            @if($code->info_1)
            <p style="margin:0;"><strong>Info 1:</strong> {{ $code->info_1 }}</p>
            @endif
            @if($code->info_2)
            <p style="margin:0;"><strong>Info 2:</strong> {{ $code->info_2 }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>