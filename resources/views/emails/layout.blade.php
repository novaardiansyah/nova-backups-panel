<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Nova Backups')</title>
  <style>
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    body { margin: 0; padding: 0; width: 100% !important; background-color: #f4f8f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #080f0b; }
    @media only screen and (max-width: 620px) {
      .email-container { width: 100% !important; border-radius: 0 !important; }
      .email-content { padding: 24px 20px !important; }
      .otp-code { font-size: 28px !important; letter-spacing: 6px !important; }
    }
  </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f8f5; color: #080f0b; -webkit-font-smoothing: antialiased;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f8f5; padding: 32px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; border: 1px solid #dde5df; overflow: hidden; box-shadow: 0 4px 20px rgba(10, 23, 16, 0.04);">
        <tr>
          <td style="height: 4px; background: linear-gradient(90deg, #0b7a53 0%, #10b981 100%);"></td>
        </tr>
        <tr>
          <td style="padding: 28px 36px 20px 36px; border-bottom: 1px solid #eff4f0;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td style="vertical-align: middle; padding-right: 12px;">
                  <img src="{{ url('/logo.png') }}" alt="Nova Backups" width="32" height="32" style="display: block; border-radius: 8px; object-fit: contain;">
                </td>
                <td style="vertical-align: middle;">
                  <span style="font-size: 17px; font-weight: 700; color: #080f0b; letter-spacing: -0.2px; display: block; line-height: 1.2;">Nova Backups</span>
                  <span style="font-size: 11px; font-weight: 500; color: #78857e; display: block; line-height: 1.2;">Scheduled Backup Management</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="email-content" style="padding: 32px 36px 36px 36px;">
            @yield('content')
          </td>
        </tr>
        <tr>
          <td style="background-color: #f8faf8; padding: 24px 36px; border-top: 1px solid #dde5df;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" style="font-size: 12px; line-height: 1.6; color: #53615a;">
                  <p style="margin: 0 0 10px 0;">
                    This email was sent automatically to <strong style="color: #080f0b;">{{ $email }}</strong> to protect your account security. Please do not reply to this email.
                  </p>
                  <p style="margin: 0; font-size: 11px; color: #78857e;">
                    <a href="{{ config('app.url') }}" style="color: #78857e; text-decoration: none;">&copy; {{ date('Y') }} Nova Backups. All rights reserved.</a>
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
