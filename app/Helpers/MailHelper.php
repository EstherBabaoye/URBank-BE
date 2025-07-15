<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class MailHelper
{
    public static function sendFromAdmin($admin, $to, $subject, $view, $data = [])
    {
        // 1. Decrypt SMTP password
        $decryptedPassword = Crypt::decrypt($admin->smtp_password);

        // 2. Set SMTP config at runtime
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $admin->smtp_host,
            'port' => $admin->smtp_port,
            'encryption' => $admin->smtp_encryption,
            'username' => $admin->smtp_username,
            'password' => $decryptedPassword,
        ]);

        Config::set('mail.from.address', $admin->from_email);
        Config::set('mail.from.name', $admin->from_name);

        // 3. Send the email
        Mail::send($view, $data, function ($msg) use ($to, $subject) {
            $msg->to($to)->subject($subject);
        });
    }
}
