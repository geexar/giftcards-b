<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Header: Specific to Top-Up --}}
    <x-mail.header
        :headerText="'Deposit Confirmed'"
        :subtitle="'Your USDT deposit has been confirmed and your wallet has been credited.'" />

    {{-- Greeting: Personalized with User Name --}}
    <x-mail.greeting
        :name="$transaction->user->name"
        :message="'Your USDT deposit of <strong>' . formatMoney($transaction->amount) . ' USDT</strong> on the <strong>' . ($transaction->usdt_network) . '</strong> network has been confirmed and your wallet balance updated.'" />

    {{-- Transaction Summary --}}
    <div style="margin-bottom:32px; padding:16px; border-radius:6px; border:1px solid #ddd;">
        <h2 style="font-size:18px; font-weight:600; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:6px;">🧾 Transaction Summary</h2>
        <div style="font-size: 14px; line-height: 1.8;">
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Reference ID</span>
                <span style="word-break: break-all; margin-left: 10px;">{{ $transaction->reference_id }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Date & Time</span>
                <span>{{ formatDateTime($transaction->created_at) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #eee;">
                <span style="font-weight:600; color: #28a745;">Amount Credited</span>
                <span style="font-weight:700; color: #28a745;">{{ formatMoney($transaction->amount) }} USD</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Current Balance</span>
                <span style="font-weight:700;">{{ formatMoney($transaction->user->balance) }} USD</span>
            </div>
        </div>
    </div>

    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">
        Deposits are final once confirmed on-chain. If you have any issue, contact support and include your Transaction ID.
    </p>

    {{-- Support Channels --}}
    <x-mail.support />

    {{-- Footer --}}
    <x-mail.footer />

</div>