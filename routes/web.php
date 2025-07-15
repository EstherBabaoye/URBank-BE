<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

// ✅ Test routes come first
Route::get('/ping', fn() => 'pong');

Route::get('/test-email', function () {
    try {
        Mail::raw('Test email via SSL port 465', function ($message) {
            $message->to('estherbabaoye@gmail.com')
                    ->subject('SSL Test');
        });
        return '✅ Email sent!';
    } catch (\Exception $e) {
        return '❌ Failed to send: ' . $e->getMessage();
    }
});

// 👇 This might be your React fallback or Laravel homepage
Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug', function () {
    return response()->json(['status' => 'working']);
});
