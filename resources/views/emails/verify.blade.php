<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify Your Email</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
  <div style="max-width:600px;margin:0 auto;background:white;border-radius:8px;overflow:hidden;">

    <!-- Header -->
    <div style="background:#051d40;padding:20px 0;text-align:center;">
      <img src="{{ asset('images/email/urb-logo.png') }}" alt="URBank Logo" style="height:60px;" onerror="this.style.display='none'">
    </div>

    <!-- Body -->
    <div style="padding:30px; color:#051d40; font-size:15px; line-height:1.6;">
      <h2>Hello {{ $name }},</h2>

      <p>Welcome to <strong>URBank</strong>! Please verify your email address by clicking the button below:</p>

      <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:12px 24px;font-size:16px;background-color:#051d40;color:#ffffff !important;text-decoration:none;border-radius:6px;margin-top:20px;">
        Verify Email
      </a>

      <p style="margin-top: 20px;">This link will expire in 1 hour. If you did not create an account with us, you can safely ignore this message.</p>

      <p>Thank you,<br><strong>URBank Team</strong></p>
    </div>

    <!-- Footer -->
    <div style="background:#72cded;text-align:center;padding:20px;color:#051d40;font-size:14px;line-height:1.6;">
      &copy; {{ date('Y') }} URBank. All rights reserved.<br>
      148/150 Bode Thomas Street, Surulere, Lagos, Nigeria<br>
      Phone: 08140475605<br>
      Need help? Email <a href="mailto:urbank-support@nhsurulere.site" style="color:#051d40;text-decoration:underline;">urbank-support@nhsurulere.site</a><br>
      Need to speak to someone? <a href="mailto:urbank-admin@nhsurulere.site" style="color:#051d40;text-decoration:underline;">urbank-admin@nhsurulere.site</a>
    </div>

  </div>
</body>
</html>
