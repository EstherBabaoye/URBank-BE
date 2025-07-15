<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Illuminate\Mail\Transport\TransportInterface;
use Illuminate\Support\Facades\Mail;
use App\Models\Admin;

class DynamicMailServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Mail::extend('admin_smtp', function ($config) {
            // Get the currently authenticated admin (or fallback)
            $admin = auth('admin')->user();

            if (!$admin || !$admin->smtp_host) {
                return Mail::createSymfonyTransport(config('mail.mailers.smtp')); // fallback
            }

            $transport = new EsmtpTransport(
                $admin->smtp_host,
                $admin->smtp_port,
                $admin->smtp_encryption
            );
            $transport->setUsername($admin->smtp_username);
            $transport->setPassword($admin->smtp_password);

            return new Mailer($transport);
        });
    }
}
