<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use App\Models\AccountOpening;
use App\Models\FailedAccount;
use App\Models\CardApplication;
use App\Models\Card;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;


class AdminController extends Controller
{
    // 🔐 Admin Login (JWT Based)
    public function adminLogin(Request $request)
    {

        auth()->shouldUse('admin');
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'admin' => auth('admin')->user()
        ]);
    }

    // 🔓 Admin Logout
    public function adminLogout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken());
            return response()->json(['message' => 'Logged out successfully']);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout, token invalid'], 400);
        }
    }

    // 🙋‍♂️ Current Admin Info
    public function me(Request $request)
    {
        try {
            $admin = JWTAuth::parseToken()->authenticate();
            return response()->json($admin);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invalid or expired'], 401);
        }
    }

    // 📥 Get Pending Accounts
    public function getPendingAccounts()
    {
        $pendingAccounts = AccountOpening::where('status', 'pending')->get();
        return response()->json($pendingAccounts);
    }

    public function approveAccount($id)
    {
        $application = AccountOpening::find($id);
        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'This application has already been processed.'], 400);
        }

        if (Account::where('bvn', $application->bvn)->exists()) {
            return response()->json(['message' => 'An account has already been created with this BVN.'], 400);
        }

        $admin = auth('admin')->user();
        if (!$admin || !$admin->email) {
            return response()->json(['message' => 'Admin email not found.'], 500);
        }

        $accountNumber = rand(1000000000, 9999999999);
        $folderPath = "uploads/{$accountNumber}";

        Log::info('Application file paths', [
            'passport_photo' => $application->passport_photo,
            'utility_bill' => $application->utility_bill,
            'uploaded_id_file' => $application->uploaded_id_file,
        ]);


        // Move files first
        $passport = $this->moveFile($application->passport_photo, $folderPath);
        $utility = $this->moveFile($application->utility_bill, $folderPath);
        $idFile = $this->moveFile($application->uploaded_id_file, $folderPath);

        if (!$passport || !$utility || !$idFile) {
            Log::error('File move failed', [
                'passport' => $passport,
                'utility' => $utility,
                'idFile' => $idFile,
            ]);
            return response()->json(['message' => 'File upload failed. Account not created.'], 500);
        }

        // Create the account
        $account = Account::create([
            'account_number' => $accountNumber,
            'account_type' => $application->account_type,
            'first_name' => $application->first_name,
            'middle_name' => $application->middle_name,
            'surname' => $application->surname,
            'email' => $application->email,
            'phone' => $application->phone,
            'bvn' => $application->bvn,
            'house_number' => $application->house_number,
            'street_name' => $application->street_name,
            'city' => $application->city,
            'residential_lga' => $application->residential_lga,
            'residential_state' => $application->residential_state,
            'id_type' => $application->id_type,
            'id_number' => $application->id_number,
            'id_issue_date' => $application->id_issue_date,
            'id_expiry_date' => $application->id_expiry_date,
            'passport_photo' => $passport,
            'utility_bill' => $utility,
            'uploaded_id_file' => $idFile,
            'account_created_at' => now(),
        ]);

        $application->update(['status' => 'approved']);

        try {
            $fullName = implode(' ', array_filter([
                $application->first_name,
                $application->middle_name,
                $application->surname,
            ]));

            Mail::send('emails.account-approval', [
                'fullName' => $fullName,
                'accountNumber' => $accountNumber,
                'url' => env('APP_URL'),
            ], function ($message) use ($application, $admin) {
                $message->to($application->email)
                    ->subject('URBank Account Approved')
                    ->from($admin->email, $admin->name ?? 'URBank Admin');
            });
        } catch (\Throwable $e) {
            Log::error('❌ Mail sending failed after account creation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json(['message' => 'Account approved and user notified.']);
    }

    public function rejectAccount($id)
    {
        $application = AccountOpening::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found'], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'This application has already been processed.'], 400);
        }

        $admin = auth('admin')->user();
        if (!$admin || !$admin->email) {
            return response()->json(['message' => 'Admin email not found.'], 500);
        }

        // Move to failed_accounts table
        FailedAccount::create([
            'first_name' => $application->first_name,
            'middle_name' => $application->middle_name,
            'surname' => $application->surname,
            'email' => $application->email,
            'bvn' => $application->bvn,
            'phone' => $application->phone,
            'passport_photo' => $application->passport_photo,
            'utility_bill' => $application->utility_bill,
            'uploaded_id_file' => $application->uploaded_id_file,
            'rejection_reason' => 'Application rejected by admin',
            'rejected_at' => now(),
        ]);

        $application->delete();

        try {
            $fullName = implode(' ', array_filter([
                $application->first_name,
                $application->middle_name,
                $application->surname,
            ]));

            Mail::send('emails.account-rejection', [
                'fullName' => $fullName,
                'url' => env('APP_URL'),
            ], function ($message) use ($application, $admin) {
                $message->to($application->email)
                    ->subject('URBank Application Rejected')
                    ->from($admin->email, $admin->name ?? 'URBank Admin');
            });
        } catch (\Throwable $e) {
            Log::error('❌ Mail sending failed after account rejection', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json(['message' => 'Application rejected and user notified.']);
    }


    // ✅ moveFile is outside approveAccount now
    private function moveFile($filePath, $newFolder)
    {
        Log::info('moveFile() called with:', ['filePath' => $filePath, 'newFolder' => $newFolder]);

        if (!$filePath) {
            Log::warning('moveFile() received null file path');
            return null;
        }

        // Make sure it's relative to storage/app/public
        $relativePath = str_starts_with($filePath, 'uploads/')
            ? $filePath
            : 'uploads/' . ltrim($filePath, '/');

        $fullOldPath = storage_path('app/public/' . $relativePath);
        Log::debug('🔍 Verifying real path:', ['realPath' => $fullOldPath]);
        Log::debug('🧪 file_exists() result:', ['exists' => file_exists($fullOldPath)]);

        if (!file_exists($fullOldPath)) {
            Log::error('moveFile() error: File does not exist on disk', ['path' => $fullOldPath]);
            return null;
        }

        // Destination: public/uploads/newFolder
        $fullNewFolderPath = public_path($newFolder);
        if (!file_exists($fullNewFolderPath)) {
            mkdir($fullNewFolderPath, 0777, true);
        }

        $fileName = basename($filePath);
        $newPath = "{$newFolder}/{$fileName}";
        $fullNewPath = public_path($newPath);

        if (rename($fullOldPath, $fullNewPath)) {
            Log::info('moveFile() success', ['newPath' => $newPath]);
            return $newPath;
        } else {
            Log::error('moveFile() rename failed', ['from' => $fullOldPath, 'to' => $fullNewPath]);
            return null;
        }
    }

    ////CARDS

    // ✅  Get Pending Card Applications
    public function getPendingCards()
    {
        $cards = CardApplication::where('status', 'pending')
            ->get()
            ->map(function ($card) {
                $card->full_name = collect([
                    $card->first_name,
                    $card->middle_name,
                    $card->surname,
                ])->filter()->implode(' ');
                return $card;
            });

        return response()->json($cards);
    }


    // ✅ 2. Approve Card Application
    public function approveCard(Request $request, $id)
    {
        $cardApp = CardApplication::where('id', $id)->where('status', 'pending')->first();

        if (!$cardApp) {
            return response()->json(['error' => 'Application not found or already processed.'], 404);
        }

        $cardNumber = $this->generateUniqueCardNumber($cardApp->card_type, $cardApp->sub_card_type);
        $cvv = $this->generateCvv();
        $atm_pin_plain = '0000';
        $atm_pin_hashed = Hash::make($atm_pin_plain);

        $expiryDate = now()->addYears(3)->format('m/Y');

        $cardNumberHash = Hash::make($cardNumber);
        $first4 = substr($cardNumber, 0, 4);
        $last6 = substr($cardNumber, -6);
        $cardNumberMasked = $first4 . str_repeat('*', strlen($cardNumber) - 10) . $last6;

        $card = Card::create([
            'account_number' => $cardApp->account_number,
            'card_type' => $cardApp->card_type,
            'sub_card_type' => $cardApp->sub_card_type,
            'card_number' => $cardNumber,
            'card_number_hash' => $cardNumberHash,
            'first4' => $first4,
            'last6' => $last6,
            'card_number_masked' => $cardNumberMasked,
            'cvv' => $cvv,
            'atm_pin' => $atm_pin_hashed, 
            'expiry_date' => $expiryDate,
        ]);


        // ✅ Update linked account
        Account::where('account_number', $card->account_number)->update([
            'card_number_masked' => $cardNumberMasked,
            'card_expiry' => $expiryDate,
        ]);

        $cardApp->update(['status' => 'approved']);

        $fullName = collect([
            $cardApp->first_name,
            $cardApp->middle_name,
            $cardApp->surname,
        ])->filter()->implode(' ');

        $this->sendApprovalEmail(
            $cardApp->email,
            $fullName,
            $cardNumberMasked,
            $cardApp->sub_card_type,
            $cvv,
             $atm_pin_plain,
            $expiryDate,
            $cardApp->account_number
        );

        return response()->json(['message' => 'Application approved and card details sent.']);
    }

    // ✅ 3. Reject Card Application
    public function rejectCard(Request $request, $id)
    {
        $cardApp = CardApplication::where('id', $id)->where('status', 'pending')->first();

        if (!$cardApp) {
            return response()->json(['error' => 'Application not found or already processed.'], 404);
        }

        $cardApp->update(['status' => 'rejected']);

        $fullName = trim("{$cardApp->first_name} {$cardApp->middle_name} {$cardApp->surname}");
        $this->sendRejectionEmail($cardApp->email, $fullName);

        dispatch(function () use ($cardApp) {
            sleep(259200); // 3 days
            $cardApp->delete();
        })->afterResponse();

        return response()->json(['message' => 'Application rejected and user notified.']);
    }

    // ✅ Email: Blade-based Card Approval
    private function sendApprovalEmail($email, $fullName, $cardNumber, $subType, $cvv,  $atm_pin_plain, $expiryDate, $accountNumber)
    {
        try {
            Mail::send('emails.card-approval', [
                'fullName' => $fullName,
                'cardNumber' => $cardNumber,
                'cvv' => $cvv,
                'atm_pin' =>  $atm_pin_plain,
                'expiryDate' => $expiryDate,
                'accountNumber' => $accountNumber,
                'subType' => $subType,
                'url' => env('APP_URL'),
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('URBank Card Approved')
                    ->from(env('MAIL_FROM_ADDRESS'), 'URBank');
            });
        } catch (\Throwable $e) {
            Log::error('❌ Card approval mail sending failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // ✅ Email: Blade-based Card Rejection
    private function sendRejectionEmail($email, $fullName)
    {
        try {
            Mail::send('emails.card-rejection', [
                'fullName' => $fullName,
                'url' => env('APP_URL'),
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('URBank Card Application Rejected')
                    ->from(env('MAIL_FROM_ADDRESS'), 'URBank');
            });
        } catch (\Throwable $e) {
            Log::error('❌ Card rejection mail sending failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // ✅ Generate Card Number Logic
    private function generateUniqueCardNumber($type, $sub)
    {
        $sub = strtolower($sub ?? '');

        do {
            if ($sub === 'naira mastercard' || $sub === 'dollar mastercard') {
                $number = '5399' . $this->generateRandomDigits(12);
            } elseif ($sub === 'naira visa card' || $sub === 'dollar visa card') {
                $number = '4' . $this->generateRandomDigits(15);
            } elseif ($sub === 'verve') {
                $prefix = collect(['506', '507', '650'])->random();
                $number = $prefix . $this->generateRandomDigits(16);
            } elseif ($sub === 'basic') {
                $number = '34' . $this->generateRandomDigits(13);
            } elseif ($sub === 'rewards') {
                $number = '35' . $this->generateRandomDigits(13);
            } elseif ($sub === 'savings') {
                $number = '36' . $this->generateRandomDigits(13);
            } else {
                $number = $this->generateRandomDigits(16);
            }
        } while (Card::where('card_number', $number)->exists());

        return $number;
    }

    private function generateCvv()
    {
        return str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
    }

    // 🔢 Used to generate sequences of numeric strings
    private function generateRandomDigits($length)
    {
        return collect(range(1, $length))->map(fn() => rand(0, 9))->join('');
    }
}
