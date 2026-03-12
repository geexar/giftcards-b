<div style="text-align:center; margin-bottom:32px;">
    <img src="{{ asset('logo.png') }}" alt="Platform Logo" style="max-height:60px; margin-bottom:16px;">
    <h1 style="margin:0; font-size:22px; font-weight:600;">
        {{ $headerText }}
    </h1>
    @if($subtitle ?? false)
        <p style="margin:8px 0 0; font-size:14px; color:#555;">{{ $subtitle }}</p>
    @endif
</div>