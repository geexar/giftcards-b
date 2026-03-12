@php
$whatsapp = getSetting('contact_support', 'whatsapp');
$telegram = getSetting('contact_support', 'telegram');
$email = getSetting('contact_support', 'email');
@endphp

<div style="margin-bottom:32px; padding:16px; border-radius:6px; border:1px solid #ddd;">
    <h2 style="font-size:18px; font-weight:600; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:6px;">💬 Need Help?</h2>
    <p>We’re here to assist you. Reach us via your preferred channel:</p>
    <ul style="margin:0 0 0 18px; padding:0;">
        <li>WhatsApp: <a href="https://wa.me/{{$whatsapp}}" style="color:#1a73e8;">{{ $whatsapp }}</a></li>
        <li>Telegram: <a href="https://t.me/{{$telegram}}" style="color:#1a73e8;">{{ $telegram }}</a></li>
        <li>Email: <a href="mailto:{{$email}}" style="color:#1a73e8;">{{ $email }}</a></li>
    </ul>
</div>