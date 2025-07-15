<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\InternetBanking;
use App\Models\Card;
use App\Models\AccountOpening;
use App\Models\Account;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller

{
    //ACCOUNT OPENING
    // ... Account Opening Logic (Validation, File Uploads, DB Save, Email)

    public function register(Request $request)
    {

        Log::info('📥 Incoming register request', $request->all());

        try {
            //  VALIDATE REQUEST
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'firstName' => 'required|string',
                'middleName' => 'nullable|string',
                'surname' => 'required|string',
                'mothersMaidenName' => 'required|string',
                'gender' => 'required|in:male,female,other',
                'dateOfBirth' => 'required|date|before:today',
                'maritalStatus' => 'required|in:single,married,divorced,widowed',
                'nationality' => 'required|string',
                'stateOfOrigin' => 'required|string',
                'lgaOfOrigin' => 'required|string',
                'houseNumber' => 'required|string',
                'streetName' => 'required|string',
                'city' => 'required|string',
                'residentialLGA' => 'required|string',
                'residentialState' => 'required|string',
                'phone' => [
                    'required',
                    'string',
                    'regex:/^(\+234|0)[789][01]\d{8}$/'
                ],

                'email' => 'required|email',
                'idType' => 'required|string',
                'idNumber' => 'required|string',
                'idIssueDate' => 'required|date',
                'idExpiryDate' => 'required|date|after:idIssueDate',

                //  Duplicate Check
                'bvn' => [
                    'required',
                    'digits:11',
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = DB::table('users')
                            ->where('bvn', $request->bvn)
                            ->where('nin', $request->nin)
                            ->where('account_type', $request->accountType)
                            ->where('status', '!=', 'rejected')
                            ->exists();

                        if ($exists) {
                            $fail('An account of this type already exists for this BVN and NIN.');
                        }
                    },
                ],

                'nin' => 'required|digits:11',
                'tin' => 'nullable|digits:10',
                'employmentStatus' => 'required|in:employed,self-employed,unemployed,student,retired',
                'employerName' => 'nullable|string',
                'employerAddress' => 'nullable|string',
                'annualIncome' => 'required|numeric|min:0',

                //  Next of Kin
                'nokFirstName' => 'required|string',
                'nokMiddleName' => 'nullable|string',
                'nokSurname' => 'required|string',
                'nokGender' => 'required|in:male,female,other',
                'nokDOB' => 'required|date|before:today',
                'nokRelationship' => 'required|string',
                'nokPhone' => [
                    'required',
                    'string',
                    'regex:/^(\+234|0)[789][01]\d{8}$/'
                ],

                'nokEmail' => 'nullable|email',
                'nokAddress' => 'required|string',

                //  Account Info
                'accountType' => 'required|string',
                'cardType' => 'required|string',
                'electronicBanking' => 'required|array',
                'electronicBanking.*' => 'in:online,mobile,wallet',
                'alertPreference'   => 'required|array',
                'alertPreference.*' => 'in:sms,email',


                //  Mandate Info
                'mandateFirstName' => 'required|string',
                'mandateMiddleName' => 'required|string',
                'mandateSurname' => 'required|string',
                'mandateIdType' => 'required|string',
                'mandateIdNumber' => 'required|string',
                'mandatePhone' => [
                    'required',
                    'string',
                    'regex:/^(\+234|0)[789][01]\d{8}$/'
                ],

                'mandateDate' => 'required|date|before_or_equal:today',

                //  Declaration
                'declarationName' => 'required|string',
                'declarationDate' => 'required|date|before_or_equal:today',

                //  File Uploads
                'passportPhoto' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                'uploadedIdFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
                'utilityBill' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'errors' => $validator->errors(),
            //         'message' => 'Account Creation Failed',
            //     ], 400);
            // }

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            Log::info('✅ Passed validation');

            //  STORE FILES
            $folder = 'uploads/' . time();
            $passportPath = $request->file('passportPhoto')->store($folder, 'public');
            $idFilePath = $request->file('uploadedIdFile')->store($folder, 'public');
            $utilityPath = $request->file('utilityBill')->store($folder, 'public');
            $mandateSignaturePath = $request->file('mandateSignature')->store($folder, 'public');
            $declarationSignaturePath = $request->file('declarationSignature')->store($folder, 'public');


            Log::info('✅ Stored files');
            Log::info('🧾 Creating User with:', [
                'mandate_signature' => $mandateSignaturePath,
                'declaration_signature' => $declarationSignaturePath
            ]);

            //  SAVE TO DB
            $user = User::create([
                'title' => $request->title,
                'first_name' => $request->firstName,
                'middle_name' => $request->middleName,
                'surname' => $request->surname,
                'mothers_maiden_name' => $request->mothersMaidenName,
                'gender' => $request->gender,
                'dob' => $request->dateOfBirth,
                'marital_status' => $request->maritalStatus,
                'nationality' => $request->nationality,
                'state_of_origin' => $request->stateOfOrigin,
                'lga_of_origin' => $request->lgaOfOrigin,
                'house_number' => $request->houseNumber,
                'street_name' => $request->streetName,
                'city' => $request->city,
                'residential_lga' => $request->residentialLGA,
                'residential_state' => $request->residentialState,
                'phone' => $request->phone,
                'email' => $request->email,
                'id_type' => $request->idType,
                'id_number' => $request->idNumber,
                'id_issue_date' => $request->idIssueDate,
                'id_expiry_date' => $request->idExpiryDate,
                'bvn' => $request->bvn,
                'nin' => $request->nin,
                'tin' => $request->tin,
                'employment_status' => $request->employmentStatus,
                'employer_name' => $request->employerName,
                'employer_address' => $request->employerAddress,
                'annual_income' => $request->annualIncome,
                'nok_first_name' => $request->nokFirstName,
                'nok_middle_name' => $request->nokMiddleName,
                'nok_surname' => $request->nokSurname,
                'nok_gender' => $request->nokGender,
                'nok_dob' => $request->nokDOB,
                'nok_relationship' => $request->nokRelationship,
                'nok_phone' => $request->nokPhone,
                'nok_email' => $request->nokEmail,
                'nok_address' => $request->nokAddress,
                'account_type' => $request->accountType,
                'card_type' => $request->cardType,
                'electronic_banking' => is_array($request->electronicBanking)
                    ? implode(',', array_unique($request->electronicBanking))
                    : $request->electronicBanking,



                'alert_preference' => is_array($request->alertPreference)
                    ? implode(',', array_unique($request->alertPreference))
                    : $request->alertPreference,


                'mandate_first_name' => $request->mandateFirstName,
                'mandate_middle_name' => $request->mandateMiddleName,
                'mandate_surname' => $request->mandateSurname,
                'mandate_id_type' => $request->mandateIdType,
                'mandate_id_number' => $request->mandateIdNumber,
                'mandate_phone' => $request->mandatePhone,
                'mandate_date' => $request->mandateDate,


                'declaration_name' => $request->declarationName,
                'declaration_date' => $request->declarationDate,
                'mandate_signature' => $mandateSignaturePath,
                'declaration_signature' => $declarationSignaturePath,

                'passport_photo' => $passportPath,
                'uploaded_id_file' => $idFilePath,
                'utility_bill' => $utilityPath,
                'status' => 'pending',
            ]);
            Log::info('✅ Created User  table');

            $folder = 'uploads/' . time(); // or use uniqid() or user ID

            $passportPath = $request->file('passportPhoto')->store($folder, 'public');
            $utilityPath = $request->file('utilityBill')->store($folder, 'public');
            $idFilePath = $request->file('uploadedIdFile')->store($folder, 'public');

            $mandateSignaturePath = $request->file('mandateSignature')->store($folder, 'public');
            $declarationSignaturePath = $request->file('declarationSignature')->store($folder, 'public');


            $user = AccountOpening::create([
                'title' => $request->title,
                'first_name' => $request->firstName,
                'middle_name' => $request->middleName,
                'surname' => $request->surname,
                'mothers_maiden_name' => $request->mothersMaidenName,
                'gender' => $request->gender,
                'dob' => $request->dateOfBirth,
                'marital_status' => $request->maritalStatus,
                'nationality' => $request->nationality,
                'state_of_origin' => $request->stateOfOrigin,
                'lga_of_origin' => $request->lgaOfOrigin,
                'house_number' => $request->houseNumber,
                'street_name' => $request->streetName,
                'city' => $request->city,
                'residential_lga' => $request->residentialLGA,
                'residential_state' => $request->residentialState,
                'phone' => $request->phone,
                'email' => $request->email,
                'id_type' => $request->idType,
                'id_number' => $request->idNumber,
                'id_issue_date' => $request->idIssueDate,
                'id_expiry_date' => $request->idExpiryDate,
                'bvn' => $request->bvn,
                'nin' => $request->nin,
                'tin' => $request->tin,
                'employment_status' => $request->employmentStatus,
                'employer_name' => $request->employerName,
                'employer_address' => $request->employerAddress,
                'annual_income' => $request->annualIncome,
                'nok_first_name' => $request->nokFirstName,
                'nok_middle_name' => $request->nokMiddleName,
                'nok_surname' => $request->nokSurname,
                'nok_gender' => $request->nokGender,
                'nok_dob' => $request->nokDOB,
                'nok_relationship' => $request->nokRelationship,
                'nok_phone' => $request->nokPhone,
                'nok_email' => $request->nokEmail,
                'nok_address' => $request->nokAddress,
                'account_type' => $request->accountType,
                'card_type' => $request->cardType,
                'electronic_banking' => is_array($request->electronicBanking)
                    ? implode(',', array_unique($request->electronicBanking))
                    : $request->electronicBanking,



                'alert_preference' => is_array($request->alertPreference)
                    ? implode(',', array_unique($request->alertPreference))
                    : $request->alertPreference,


                'mandate_first_name' => $request->mandateFirstName,
                'mandate_middle_name' => $request->mandateMiddleName,
                'mandate_surname' => $request->mandateSurname,
                'mandate_id_type' => $request->mandateIdType,
                'mandate_id_number' => $request->mandateIdNumber,
                'mandate_phone' => $request->mandatePhone,
                'mandate_date' => $request->mandateDate,
                'declaration_name' => $request->declarationName,
                'declaration_date' => $request->declarationDate,
                'passport_photo' => $passportPath,
                'mandate_signature' => $mandateSignaturePath,
                'declaration_signature' => $declarationSignaturePath,

                'uploaded_id_file' => $idFilePath,
                'utility_bill' => $utilityPath,
                'status' => 'pending',
            ]);

            Log::info('✅ Created accountopening table');

            //  Send Welcome Email with Verification Style Format
            $fullName = implode(' ', array_filter([
                $request->firstName,
                $request->middleName,
                $request->surname
            ]));
            $url = env('APP_URL', 'http://localhost:5173'); // Or your React frontend URL


            // 🟠 Try to send email separately
            try {;

                Mail::send([], [], function ($msg) use ($request, $fullName, $url) {
                    $msg->to($request->email)
                        ->subject('Welcome to URBank – Application Received')
                        ->from(env('MAIL_FROM_ADDRESS'), 'URBank')
                        ->html(view('emails.account-welcome', compact('fullName', 'url'))->render());
                });
            } catch (\Exception $e) {
                // Log mail failure but don't crash the whole request
                Log::error('Mail send failed: ' . $e->getMessage());
            }
            Log::info('✅ Created AccountOpening');
            return response()->json(['message' => 'Account created successfully.'], 200);

            // } catch (\Exception $e) {
            //     return response()->json([
            //         'message' => 'An error occurred during registration',
            //         'error' => 'Failed to send confirmation email. Please try again or contact support.'
            //     ], 500);
            // }
        } catch (\Exception $e) {
            Log::error('❌ Registration failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during registration',
                'error' => $e->getMessage(), // Show actual error temporarily
            ], 500);
        }
    }


    //INTERNETBANKING REGISTRATION
    // ... Internet Banking Registration Logic
    public function registerInternetBanking(Request $request)
    {
        Log::info("🌐 Incoming Internet Banking registration request", $request->all());

        // 1. VALIDATION
        try {
            $validated = $request->validate([
                'first_name' => 'required|string',
                'middle_name' => 'nullable|string',
                'surname' => 'required|string',
                'account_number' => 'required|string',
                'bvn' => 'required|string|digits:11',
                'atm_first4' => 'required|string|size:4',
                'atm_last6' => 'required|string|size:6',
                'atm_pin' => 'required|digits:4',
                'login_pin' => 'required|digits:6',
                'email' => 'required|email',
                'sec_question1' => 'required|string',
                'sec_answer1' => 'required|string',
                'sec_question2' => 'required|string',
                'sec_answer2' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('❌ Validation failed', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        }

        Log::info("✅ Validation passed");

        // 2. CHECK DUPLICATES
        foreach (['email', 'account_number', 'bvn'] as $field) {
            if (InternetBanking::where($field, $validated[$field])->exists()) {
                Log::warning("⚠️ Duplicate found for: $field", [$field => $validated[$field]]);
                return response()->json(["message" => "This {$field} is already registered for internet banking."], 400);
            }
        }

        if (InternetBanking::where('atm_first4', $validated['atm_first4'])
            ->where('atm_last6', $validated['atm_last6'])->exists()
        ) {
            Log::warning("⚠️ ATM card already used", [
                'first4' => $validated['atm_first4'],
                'last6' => $validated['atm_last6']
            ]);
            return response()->json(["message" => "This ATM card has already been used."], 400);
        }

        Log::info("✅ Duplicate checks passed");

        // 3. CARD VERIFICATION
        $card = Card::where('account_number', $validated['account_number'])->first();
        if (!$card) {
            Log::warning("❌ Card not found for account", ['account_number' => $validated['account_number']]);
            return response()->json(['message' => 'Card not found'], 400);
        }

        if ($card->first4 !== $validated['atm_first4'] || $card->last6 !== $validated['atm_last6']) {
            Log::warning("❌ ATM card mismatch", [
                'provided_first4' => $validated['atm_first4'],
                'actual_first4' => $card->first4,
                'provided_last6' => $validated['atm_last6'],
                'actual_last6' => $card->last6
            ]);
            return response()->json(['message' => 'ATM card number parts do not match records'], 400);
        }

        Log::info("✅ Card verified successfully");

        // 4. ACCOUNT INFO VERIFICATION
        $account = Account::where('account_number', $validated['account_number'])->first();
        if (!$account) {
            Log::warning("❌ Account not found", ['account_number' => $validated['account_number']]);
            return response()->json(['message' => 'Account not found'], 400);
        }

        foreach (['first_name', 'middle_name', 'surname', 'email', 'bvn'] as $field) {
            $dbValue = strtolower(trim($account->$field));
            $inputValue = strtolower(trim($validated[$field] ?? ''));
            if ($dbValue !== $inputValue) {
                Log::warning("❌ Mismatch for field: $field", [
                    'provided' => $inputValue,
                    'expected' => $dbValue
                ]);
                return response()->json(['message' => "Account details do not match records"], 400);
            }
        }

        Log::info("✅ Account verified successfully");

        // 5. SAVE USER
        try {
            $user = new InternetBanking();
            $user->fill($validated);
            $user->login_pin = Hash::make($validated['login_pin']);
            $user->atm_pin = Hash::make($validated['atm_pin']);
            $user->verification_token = Str::random(64);
            $user->save();

            Log::info("✅ Internet Banking user saved", [
                'id' => $user->id,
                'email' => $user->email,
                'account_number' => $user->account_number
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Failed to save Internet Banking user", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Registration failed. Try again later.'], 500);
        }

        // 6. SEND VERIFICATION EMAIL
        $fullName = implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'],
            $validated['surname']
        ]));

        try {
            $this->sendVerificationEmail($validated['email'], $user->verification_token, $fullName);
            Log::info("📧 Verification email sent to " . $validated['email']);
        } catch (\Exception $e) {
            Log::error("❌ Failed to send verification email", ['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Registration successful. Please verify your email.'], 201);
    }

    private function sendVerificationEmail($email, $token, $name)
    {
        Log::info("📨 Preparing verification email for: $email");
        Log::debug("🔐 Token: $token");

        $url = env('FRONTEND_URL', 'http://localhost:5173') . "/services/internet-banking/verify?token={$token}";

        try {
            Mail::send('emails.verify', compact('name', 'url'), function ($msg) use ($email) {
                $msg->to($email)
                    ->subject('Verify Your Email for URBank')
                    ->from(env('MAIL_FROM_ADDRESS'), 'URBank');
            });
            Log::info("✅ Email sent successfully to: $email");
        } catch (\Exception $e) {
            Log::error("❌ Email failed to send", ['error' => $e->getMessage()]);
        }
    }

    //VERIFY EMAIL
    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');
        Log::info("🔍 Email verification attempt received.", ['token' => $token]);

        if (!$token) {
            Log::warning("❌ Missing token.");
            return response()->json([
                'success' => false,
                'message' => 'Missing token'
            ], 400);
        }

        // ✅ Try to find user by token
        $user = InternetBanking::where('verification_token', $token)->first();

        if (!$user) {
            // 🔁 Gracefully handle if token was already used and nullified
            $alreadyVerified = InternetBanking::where('verified', true)
                ->whereNull('verification_token')
                ->orderByDesc('verified_at')
                ->first();

            if ($alreadyVerified) {
                Log::info("🔁 Token already used. User already verified.", [
                    'email' => $alreadyVerified->email
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Email was already verified.'
                ], 200);
            }

            Log::warning("❌ No user found for token.", ['token' => $token]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 400);
        }

        Log::info("👤 User found for verification:", [
            'email' => $user->email,
            'verified' => $user->verified
        ]);

        // ✅ Update verification fields
        $user->verified = true;
        $user->verified_at = now();
        $user->verification_token = null;

        if ($user->save()) {
            Log::info('✅ Email successfully verified.', [
                'email' => $user->email,
                'id' => $user->id,
                'verified_at' => $user->verified_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully.'
            ], 200);
        } else {
            Log::error('❌ Failed to save user verification status.', [
                'email' => $user->email
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not verify email.'
            ], 500);
        }
    }

    //INTERNET BANKING LOGIN
    // ... Login with email & login_pin using JWT
    public function loginInternetBanking(Request $request)
    {
        $credentials = $request->only('email', 'login_pin');

        // Map login_pin to password for JWT to work
        $user = InternetBanking::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['login_pin'], $user->login_pin)) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        if (!$user->verified) {
            return response()->json(['message' => 'Email not verified.'], 403);
        }

        // Generate token using JWTAuth
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'fullName' => "{$user->first_name} {$user->middle_name} {$user->surname}",
                'accountNumber' => $user->account_number,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }

    public function getProfile($email)
    {
        $user = Auth::user();

        if (!$user || $user->email !== $email) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $account = $user->account;

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $fullName = trim("{$account->first_name} " . ($account->middle_name ?? '') . " {$account->surname}");

        $addressParts = [
            $account->house_number ?? null,
            $account->street_name ?? null,
            $account->city ?? null,
            $account->residential_lga ?? null,
            $account->residential_state ?? null,
        ];

        $address = implode(', ', array_filter($addressParts));

        return response()->json([
            'accountName' => $fullName,
            'accountNumber' => $account->account_number ?? $user->account_number,
            'bvn' => $account->bvn,
            'address' => $address,
            'email' => $account->email,
            'phone' => $account->phone ?? 'N/A',
            'passportPhoto' => $account->passport_photo, // ✅ now from accounts table
        ]);
    }



    //RESEND EMAIL VERIFICATION
    // ... Resend Verification Token if not yet verified
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = InternetBanking::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->verified) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        // Generate new token
        $user->verification_token = Str::random(64);
        $user->save();

        $fullName = implode(' ', array_filter([
            $user->first_name,
            $user->middle_name,
            $user->surname
        ]));

        // Send email
        $this->sendVerificationEmail($user->email, $user->verification_token, $fullName);

        return response()->json(['message' => 'Verification email resent.']);
    }

    // FORGOT INTERNET BANKING PIN
    // ... Validate sec question/answer, generate token, email link
    public function forgotInternetBankingPin(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string',
            'email' => 'required|email',
            'sec_question' => 'required|string',
            'sec_answer' => 'required|string',
        ]);

        $user = InternetBanking::where('account_number', $request->account_number)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'No matching user found.'], 404);
        }

        $question = strtolower(trim($request->sec_question));
        $answer = strtolower(trim($request->sec_answer));

        $match1 = strtolower(trim($user->sec_question1)) === $question &&
            strtolower(trim($user->sec_answer1)) === $answer;

        $match2 = strtolower(trim($user->sec_question2)) === $question &&
            strtolower(trim($user->sec_answer2)) === $answer;

        if (!$match1 && !$match2) {
            return response()->json(['message' => 'Incorrect security question or answer.'], 401);
        }

        $token = Str::random(64);
        $user->update([
            'reset_token' => $token,
            'reset_token_created_at' => now(),
            'pin_reset_verified_at' => null,
        ]);

        $resetLink = env('FRONTEND_URL', 'http://localhost:5173') . "/internet-banking/reset-pin?token={$token}";

        // ✅ Use Blade template for sending the reset email
        Mail::send('emails.reset-pin', ['url' => $resetLink], function ($msg) use ($request) {
            $msg->to($request->email)
                ->subject('Reset Your Internet Banking PIN')
                ->from(env('MAIL_FROM_ADDRESS'), 'URBank');
        });

        return response()->json(['message' => 'Reset email sent successfully.']);
    }

    // RESET INTERNET BANKING PIN
    // ... Validate token & new pin, save & mark pin_reset_verified_at
    public function resetInternetBankingPin(Request $request)
    {
        $request->validate([
            'new_pin' => 'required|digits:6',
            'token' => 'required|string',
        ]);

        $user = InternetBanking::where('reset_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        if (Carbon::parse($user->reset_token_created_at)->diffInMinutes(now()) > 60) {
            return response()->json(['message' => 'Token expired.'], 400);
        }

        $user->update([
            'login_pin' => Hash::make($request->new_pin),
            'reset_token' => null,
            'reset_token_created_at' => null,
            'pin_reset_verified_at' => now(),
        ]);

        return response()->json(['message' => 'PIN reset successful.']);
    }
}
