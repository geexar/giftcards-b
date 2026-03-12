<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>{{ $order->order_no }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            margin: 0;
        }

        body.rtl {
            text-align: right;
        }

        body.ltr {
            text-align: left;
        }

        .invoice-header {
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .section {
            margin-bottom: 20px;
        }

        h3 {
            font-size: 12px;
            color: #2c3e50;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 4px 5px;
            vertical-align: top;
        }

        .bold {
            font-weight: bold;
            color: #555;
            width: 120px;
        }

        .notes {
            padding: 8px;
            border-left: 3px solid #cbd5e0;
            background-color: #f8fafc;
            color: #555;
            font-style: italic;
        }

        .text-total {
            font-size: 13px;
            font-weight: bold;
        }

        /* Item card */
        .item-card {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
            background: #f8f8f8;
        }

        .item-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .item-card .name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .item-card .details div,
        .item-card .pricing div {
            margin-bottom: 3px;
            line-height: 1.4;
        }

        .item-card .details {
            font-size: 12px;
            color: #333;
            margin-bottom: 6px;
        }

        .item-card .pricing {
            font-size: 12px;
            color: #333;
        }

        .item-card .rejection {
            color: red;
        }
    </style>
</head>

<body class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    {{-- ORDER HEADER --}}
    <div class="invoice-header">
        <table style="width:100%; border:none;">
            <tr>
                <td class="header-title">{{ $order->order_no }}</td>
                <td dir="ltr" style="font-size:10px;color:#718096; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">
                    {{ formatDateTime($order->created_at) }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ORDER DETAILS --}}
    <div class="section">
        <h3>{{ __('Order Details') }}</h3>
        <table style="width:100%; border:none;">
            <tr>
                <td style="width:48%; vertical-align:top; border:none; padding:0;">
                    <table class="data-table">
                        <tr>
                            <td class="bold">{{ __('Status') }}:</td>
                            <td>{{ __(ucwords(str_replace('_', ' ', $order->status->value))) }}</td>
                        </tr>
                        <tr>
                            <td class="bold">{{ __('Payment Method') }}:</td>
                            <td>{{ app(\App\Services\Admin\PaymentMethodService::class)->getPaymentMethodName($order->payment_method_id) }}</td>
                        </tr>
                        <tr>
                            <td class="bold">{{ __('Transaction ID') }}:</td>
                            <td>{{ $order->transaction->transaction_no ?? '-' }}</td>
                        </tr>
                        @if($order->payment_method_id == 2)
                        <tr>
                            <td class="bold">{{ __('Reference ID') }}:</td>
                            <td>{{ $order->transaction->reference_id ?? '-' }}</td>
                        </tr>
                        @endif
                        @if($order->processor)
                        <tr>
                            <td class="bold">{{ __('Processed By') }}:</td>
                            <td>{{ $order->processor->name }}</td>
                        </tr>
                        <tr>
                            <td class="bold">{{ __('Processed At') }}:</td>
                            <td dir="ltr">{{ formatDateTime($order->processed_at) }}</td>
                        </tr>
                        @endif
                    </table>
                </td>

                <td style="width:4%; border:none;"></td>

                <td style="width:48%; vertical-align:top; border:none; padding:0;">
                    <table class="data-table">
                        <tr>
                            <td class="bold">{{ __('Name') }}:</td>
                            <td>{{ $order->user->name ?? $order->name }}</td>
                        </tr>
                        <tr>
                            <td class="bold">{{ __('Email') }}:</td>
                            <td>{{ $order->user->email ?? $order->email }}</td>
                        </tr>
                        <tr>
                            <td class="bold">{{ __('Gifted') }}:</td>
                            <td>{{ $order->is_gifted ? __('Yes') : __('No') }}</td>
                        </tr>
                        <tr>
                            <td class="bold">{{ __('Gifted Email') }}:</td>
                            <td>{{ $order->gifted_email ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ITEMS --}}
    <div class="section">
        <h3>{{ __('Items') }}</h3>
        @foreach($order->items as $item)
        <div class="item-card">
            <div>
                <img src="{{ $item->product->image->getUrl() }}" alt="Product Image" style="width: 100px; height:100px">
            </div>

            <div class="name">{{ $item->product->name }} @if($item->variantValue) - {{ $item->variantValue->value }} @endif</div>

            <div class="details">
                <div><strong>{{ __('Type') }}:</strong> {{ __(ucwords(str_replace('_',' ',$item->product->source->value))) }}</div>
                <div><strong>{{ __('Delivery Type') }}:</strong> {{ __(ucwords(str_replace('_',' ',$item->delivery_type->value))) }}</div>
                <div><strong>{{ __('Category') }}:</strong> {{
                    ($item->product->category->parent?->parent?->name ? $item->product->category->parent->parent->name . ' / ' : '') .
                    ($item->product->category->parent?->name ? $item->product->category->parent->name . ' / ' : '') .
                    $item->product->category->name
                }}</div>
                <div><strong>{{ __('Regions') }}:</strong> {{ $item->product->is_global ? __('Global') : $item->product->countries->pluck('name')->join(', ') }}</div>
            </div>

            <div class="pricing">
                <div><strong>{{ __('Quantity') }}:</strong> {{ $item->quantity }}</div>
                <div><strong>{{ __('Displayed Price') }}:</strong> {{ formatMoney($item->user_facing_price) }} USD</div>
                <div>
                    <strong>{{ __('Markup Fee') }}:</strong>
                    @if($item->markup_fee_type === 'percentage')
                    {{ $item->markup_fee_value }}%
                    @else
                    {{ formatMoney($item->markup_fee_value) }} USD
                    @endif
                    ({{ __($item->markup_fee_origin->value) }})
                </div>
                <div><strong>{{ __('Total') }}:</strong> {{ formatMoney($item->total) }} USD</div>
                <div><strong>{{ __('Fulfilled Qty') }}:</strong> {{ $item->fulfilled_quantity }}</div>
                <div><strong>{{ __('Status') }}:</strong> {{ __(ucwords(str_replace('_',' ', $item->status->value))) }}</div>
                @if($item->rejection_reason)
                <div class="rejection"><strong>{{ __('Reason') }}:</strong> {{ $item->rejection_reason }}</div>
                @endif
            </div>

            {{-- CODES SECTION --}}
            @if($item->codes->isNotEmpty())
            <div style="margin-top:15px;">
                <h5 style="margin-bottom:10px;">{{ __('Codes') }}</h5>

                @foreach($item->codes as $code)
                <div style="border:1px solid #eee; border-radius:4px; padding:12px; margin-bottom:12px; background:#fafafa;">

                    <p style="margin:0 0 4px;">
                        <strong>{{ __('Code') }}:</strong> {{ $code->code }}
                    </p>

                    @if($code->pin_code)
                    <p style="margin:0 0 4px;">
                        <strong>{{ __('PIN Code') }}:</strong> {{ $code->pin_code }}
                    </p>
                    @endif

                    @if($code->expiry_date)
                    <p style="margin:0 0 4px;">
                        <strong>{{ __('Expiry') }}:</strong> <span dir="ltr">{{ formatDate($code->expiry_date) }}</span>
                    </p>
                    @endif

                    @if($code->info_1)
                    <p style="margin:0 0 4px;">
                        <strong>{{ __('Info 1') }}:</strong> {{ $code->info_1 }}
                    </p>
                    @endif

                    @if($code->info_2)
                    <p style="margin:0;">
                        <strong>{{ __('Info 2') }}:</strong> {{ $code->info_2 }}
                    </p>
                    @endif

                </div>
                @endforeach
            </div>
            @endif

        </div>
        @endforeach
    </div>

    {{-- SUMMARY --}}
    <div class="section">
        <h3>{{ __('Summary') }}</h3>
        <table class="data-table">
            <tr>
                <td class="bold">{{ __('Items Count') }}</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ $order->items->sum('quantity') }}</td>
            </tr>
            <tr>
                <td class="bold">{{ __('Total') }}</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ formatMoney($order->total) }} USD</td>
            </tr>
            @if($order->transaction->actual_profit)
            <tr>
                <td class="bold">{{ __('Profit') }}:</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ formatMoney($order->transaction->actual_profit) }} USD</td>
            </tr>
            @endif
            @if($order->refund)
            <tr>
                <td class="bold">{{ __('Net Amount') }}</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ $order->net_amount }} USD</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- REFUND --}}
    @if($order->refund)
    <div class="section">
        <h3>{{ __('Refund') }}</h3>
        <table class="data-table">
            <tr>
                <td class="bold">{{ __('Status') }}</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ __(ucwords(str_replace('_', ' ', $order->refund->status->value))) }}</td>
            </tr>
            <tr>
                <td class="bold">{{ __('Refund Amount') }}</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ $order->refund->amount }} USD</td>
            </tr>
            @if($order->refund->processor)
            <tr>
                <td class="bold">{{ __('Processed By') }}</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};">{{ $order->refund->processor->name }}</td>
            </tr>
            <tr>
                <td class="bold">{{ __('Processed At') }}</td>
                <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};" dir="ltr">{{ formatDateTime($order->refund->processed_at) }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    {{-- NOTES --}}
    @if($order->notes)
    <div class="section">
        <h3>{{ __('Notes') }}</h3>
        <div class="notes">{{ $order->notes }}</div>
    </div>
    @endif

    {{-- RATING --}}
    @if($order->ratings->count())
    <div class="section">
        <h3>{{ __('Rating') }}</h3>

        {{-- Overall Rating --}}
        @if($order->overallRating)
        <div class="item-card">
            <div style="font-weight:bold; font-size:14px; margin-bottom:5px;">{{ __('Overall Rating') }}</div>
            <div style="font-size:12px; color:#333; margin-bottom:4px;">
                <div><strong>{{ __('Rating') }}:</strong> {{ formatRating($order->overallRating->rating) }}</div>
                <div><strong>{{ __('Comment') }}:</strong> {{ $order->overallRating->comment ?? '-' }}</div>
                <div><strong>{{ __('Time') }}:</strong> <span dir="ltr">{{ formatDateTime($order->overallRating->created_at) }}</span></div>
            </div>
        </div>
        @endif

        {{-- Item Ratings --}}
        @if($order->itemRatings->count())
        <div style="margin-top:10px;">
            @foreach($order->itemRatings as $itemRating)
            <div class="item-card">
                <div style="font-weight:bold; font-size:13px; margin-bottom:4px;">
                    {{ $itemRating->orderItem->product->name }} @if($itemRating->orderItem->variantValue) - {{ $itemRating->orderItem->variantValue->value }} @endif
                </div>
                <div style="font-size:12px; color:#333; line-height:1.4;">
                    <div><strong>{{ __('Rating') }}:</strong> {{ (int) $itemRating->rating }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
    @endif

</body>

</html>