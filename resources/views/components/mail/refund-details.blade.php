<div style="margin-bottom:32px; padding:16px; border-radius:6px; border:1px solid #ddd;">
    <h2 style="font-size:18px; font-weight:600; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:6px;">💬 Refund Details</h2>
    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
        <span>Status</span><span>{{ ucwords(str_replace('_', ' ', $refund->status->value)) }}</span>
    </div>
    <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
        <span>Amount</span><span>{{ formatMoney($refund->amount) }} USD</span>
    </div>
    <p>You will receive a confirmation once the refund is completed.</p>
</div>