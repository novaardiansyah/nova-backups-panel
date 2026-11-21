@extends('emails.layout')

@section('title', 'Verify Your Account - Nova Backups')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td style="padding-bottom: 20px;">
      <h1 style="margin: 0 0 10px 0; font-size: 22px; font-weight: 700; color: #080f0b; letter-spacing: -0.3px; line-height: 1.3;">
        Verify Your Email Address
      </h1>
      <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #53615a;">
        Hello <strong style="color: #080f0b;">{{ $name }}</strong>,
      </p>
      <p style="margin: 10px 0 0 0; font-size: 14px; line-height: 1.6; color: #53615a;">
        Thank you for joining Nova Backups. To complete your account registration and secure your profile, please use the following One-Time Password (OTP) verification code:
      </p>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding: 12px 0 24px 0;">
      <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td align="center" style="background-color: #f4f8f5; border: 1.5px dashed #9fc3a9; border-radius: 12px; padding: 24px 16px;">
            <div style="font-size: 11px; font-weight: 700; color: #46845f; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 6px;">
              VERIFICATION CODE (OTP)
            </div>
            <div class="otp-code" style="font-family: 'JetBrains Mono', Consolas, Monaco, monospace; font-size: 34px; font-weight: 700; color: #0b7a53; letter-spacing: 8px; line-height: 1.2; padding: 6px 0;">
              {{ $otp }}
            </div>
            <div style="font-size: 12px; color: #4e5f55; margin-top: 6px;">
              Valid for <strong style="color: #173325;">{{ $expiresIn }}</strong>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding-bottom: 24px;">
      <div style="font-size: 13px; font-weight: 700; color: #080f0b; margin-bottom: 6px;">
        Important Security Notice
      </div>
      <div style="font-size: 12px; line-height: 1.6; color: #53615a;">
        &bull; Never share this OTP code with anyone, including Nova Backups administrators or customer support.<br>
        &bull; This verification code is single-use and will automatically expire once the time window ends.<br>
        &bull; If you did not sign up for Nova Backups, you can safely ignore this email.
      </div>
    </td>
  </tr>
  <tr>
    <td style="font-size: 13px; line-height: 1.6; color: #53615a; border-top: 1px solid #eff4f0; padding-top: 18px;">
      Warm regards,<br>
      <strong style="color: #080f0b;">The Nova Backups Team</strong>
    </td>
  </tr>
</table>
@endsection
