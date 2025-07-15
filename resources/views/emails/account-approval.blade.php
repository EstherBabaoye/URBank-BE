<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>URBank Email</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
  <div style="max-width:600px;margin:0 auto;background:white;border-radius:8px;overflow:hidden;">

    <!-- Header -->
    <div style="background:#051d40;padding:20px 0;text-align:center;">
      <img src="{{ asset('images/email/urb-logo.png') }}" alt="URBank Logo" style="height:60px;">
    </div>

    <!-- Body -->
    <div style="padding:30px;">
      <h2>Hello {{ $fullName }},</h2>
      <p>Congratulations! Your <strong>URBank</strong> account has been <strong>approved</strong>.</p>
      <p>Your account number is: <strong>{{ $accountNumber }}</strong>.</p>
      <p>You can now start using your account for banking services.</p>
      <p><a href="{{ $url }}" target="_blank" style="color:#051d40;text-decoration:underline;">Login to URBank</a></p>
      <p>Thank you for choosing URBank!</p>
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
