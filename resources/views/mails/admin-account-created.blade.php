<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width:600px; margin:0 auto; padding:24px; background:#fff; color:#333; line-height:1.6; border-radius:8px; border:1px solid #ddd;">

    {{-- Header --}}
    <x-mail.header
        :headerText="'Admin Account Created'"
        subtitle="Your administrator account has been successfully set up." />

    {{-- Greeting --}}
    <x-mail.greeting
        :name="$admin->name"
        :message="'Your <strong>Admin account</strong> has been created successfully. You now have access to the administration panel based on the permissions assigned to your role.'" />

    {{-- Login Info --}}
    <p style="font-size: 14px; margin-bottom: 12px;">
        You can log in using the following credentials:
    </p>

    <div style="background:#f7f7f7; padding:16px; border-radius:6px; margin-bottom:20px; font-size:14px;">
        <p style="margin:0 0 8px;">
            <strong>Email:</strong> {{ $admin->email }}
        </p>
        <p style="margin:0;">
            <strong>Password:</strong> {!! $password !!}
        </p>
    </div>

    <p style="font-size: 14px; margin-bottom: 20px;">
        For security reasons, please change your password immediately after your first login.
    </p>

    {{-- CTA Button --}}
    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ $url }}"
            style="display: inline-block; padding: 14px 32px; background-color: #000; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
            Go to Admin Dashboard
        </a>
    </div>

    <p style="font-size: 13px; color: #666; margin-top: 20px;">
        If you did not expect this account or believe this was sent in error, please contact our support team immediately.
    </p>

</div>