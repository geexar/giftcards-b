<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Header - Styled like Completed Template --}}
    <x-mail.header
        headerText="Back in Stock!"
        :subtitle="'🎉 ' . $product->name . ($variantValue ? ' - ' . $variantValue->value : '') . ' is available again!'" 
    />

    {{-- Body Content --}}
    <p style="margin:0 0 24px; text-align: center; font-size: 16px;">
        Good news! <strong>{{ $product->name }} {{ $variantValue ? '(' . $variantValue->value . ')' : '' }}</strong> is now available again. Don’t miss out — order it before it’s gone!
    </p>

    {{-- CTA Button --}}
    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ $url }}" style="display: inline-block; padding: 14px 32px; background-color: #000; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
            View Product →
        </a>
    </div>

    {{-- Closing --}}
    <p style="text-align: center; margin-top: 32px; font-size: 14px; color: #666;">
        Thank you for choosing {{ config('app.name') }}!
    </p>

    {{-- Support Section (Keep style consistent) --}}
    <div style="margin-top: 32px; border-top: 1px solid #eee; padding-top: 24px;">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">💬 Need Help?</h3>
        <x-mail.support />
    </div>

    {{-- Footer --}}
    <x-mail.footer />

</div>