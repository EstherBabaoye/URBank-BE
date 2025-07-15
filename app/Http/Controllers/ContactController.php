<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function handleContactSubmission(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:5000',
        ]);

        $name = $validated['name'];
        $email = $validated['email'];
        $userMessage = $validated['message'];

        try {
            // ✅ 1. Notify Admin
            Mail::send('emails.contact-admin', compact('name', 'email', 'userMessage'), function ($msg) use ($name) {
                $msg->to(config('mail.from.address'))  // ✅ FIXED THIS LINE
                    ->subject("New message from {$name}")
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            // ✅ 2. Auto-reply to sender
            Mail::send('emails.contact-user', compact('name', 'userMessage'), function ($msg) use ($email) {
                $msg->to($email)
                    ->subject("We’ve received your message – URBank")
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            return response()->json(['message' => 'Your message has been sent successfully.']);
        } catch (\Exception $e) {
            Log::error('Contact email error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send message. Please try again later.'], 500);
        }
    }
}
