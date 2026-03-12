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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice-header img {
            max-height: 60px;
        }

        .header-info {
            text-align: {
                    {
                    app()->getLocale()==='ar' ? 'right': 'left'
                }
            }

            ;
            font-size: 12px;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 8px;
            font-size: 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f1;
        }

        .activity-table th {
            background-color: #f1f5f9;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
        }

        .badge-type {
            font-size: 9px;
            padding: 2px 5px;
            color: #475569;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body class="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    {{-- HEADER --}}
    <div class="invoice-header">
        <div class="header-info">
            <div><strong>{{ __('Order No') }}:</strong> #{{ $order->order_no }}</div>
            <div><strong>{{ __('Time') }}:</strong> {{ formatDateTime($order->created_at) }}</div>
        </div>
    </div>

    {{-- ACTIVITY LOGS --}}
    <div class="section">
        <h3>{{ __('Activity Logs') }}</h3>
        <table class="activity-table" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
            <thead>
                <tr>
                    <th style="width: 20%;">{{ __('Time') }}</th>
                    <th style="width: 20%;">{{ __('Actor') }}</th>
                    <th style="width: 15%;">{{ __('Type') }}</th>
                    <th style="width: 45%;">{{ __('Description') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="color: #64748b;" dir="ltr">{{ $log->created_at }}</td>
                    <td class="bold" style="color: #334155;">
                        {{ $log->actor_type === 'system' ? __('System') : $log->actor_name }}
                    </td>
                    <td>
                        <span class="badge-type">{{ __($log->actor_type) }}</span>
                    </td>
                    <td>{{ $log->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">
                        {{ __('no activity recorded') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>

</html>