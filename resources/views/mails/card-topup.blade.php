<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Header: Specific to Card Top-Up --}}
    <x-mail.header
        :headerText="'Deposit Confirmed'"
        subtitle="Your card deposit has been processed and your wallet has been credited." />

    {{-- Greeting: Personalized --}}
    <x-mail.greeting
        :name="$transaction->user->name"
        :message="'Your <strong>Credit/Debit Card</strong> deposit of <strong>' . formatMoney($transaction->amount) . ' USD</strong> has been confirmed and your wallet balance updated.'" />

    {{-- Transaction Summary --}}
    <div style="margin-bottom:32px; padding:16px; border-radius:6px; border:1px solid #ddd;">
        <h2 style="font-size:18px; font-weight:600; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:6px;">🧾 Transaction Summary</h2>
        <div style="font-size: 14px; line-height: 1.8;">
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Reference ID</span>
                <span style="word-break: break-all; margin-left: 10px;">{{ $transaction->reference_id }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="font-weight:600;">Payment Method</span>
                <span>Credit/Debit Card</span>
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
        If you do not recognize this transaction or have any issues, please contact our support team immediately and include your Reference ID.
    </p>

    {{-- Support Channels --}}
    <x-mail.support />

    {{-- Footer --}}
    <x-mail.footer />

</div>