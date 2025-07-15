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
      <p>We regret to inform you that your <strong>URBank</strong> account application was <strong>not approved</strong>.</p>
      <p>Please ensure your details and documents are accurate. You may try applying again in the future.</p>
      <p>If you have any questions, kindly reach out to our support team.</p>
      <p><a href="{{ $url }}" target="_blank" style="color:#051d40;text-decoration:underline;">Visit URBank</a></p>
      <p>Thank you for considering URBank.</p>
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
