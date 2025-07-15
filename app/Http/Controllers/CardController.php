<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CardApplication;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    public function submitApplication(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
            'firstName' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'cardType' => 'required|string',
        ]);

        // Check if a card already exists for this account
        if (Card::where('account_number', $request->account_number)->exists()) {
            return response()->json([
                'message' => 'A card has already been issued for this account.'
            ], 400);
        }

        // Also prevent duplicate applications
        if (CardApplication::where('account_number', $request->account_number)
            ->where('status', 'pending')->exists()
        ) {
            return response()->json([
                'message' => 'There is already a pending card application for this account.'
            ], 400);
        }


        $exists = Account::where('account_number', $request->accountNumber)
            ->where('first_name', $request->firstName)
            ->when($request->filled('middleName'), fn($q) => $q->where('middle_name', $request->middleName))
            ->where('surname', $request->surname)
            ->where('email', $request->email)
            ->where('phone', $request->phone)
            ->exists();

        if (!$exists) {
            return response()->json(['error' => 'Account details do not match our records.'], 400);
        }

        // ✅ Prevent duplicate applications for same account number
        $alreadyApplied = CardApplication::where('account_number', $request->accountNumber)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($alreadyApplied) {
            return response()->json(['error' => 'A card application for this account number already exists.'], 400);
        }

        Log::info('Incoming request', $request->all());

        $application = CardApplication::create([
            'account_number' => $request->accountNumber,
            'first_name' => $request->firstName,
            'middle_name' => $request->middleName,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'card_type' => $request->cardType,
            'sub_card_type' => $request->subCardType,
            'reason' => $request->reason,
            'other_reason' => $request->reason === 'Other' ? $request->otherReason : null,
            'status' => 'pending',
        ]);

        $this->sendPendingConfirmationEmail($application);

        return response()->json([
            'success' => true,
            'message' => 'Card application submitted and email sent.'
        ]);
    }


    private function sendPendingConfirmationEmail($application)
    {
        $fullName = implode(' ', array_filter([
            $application->first_name,
            $application->middle_name ?? null,
            $application->surname
        ]));

        Mail::send('emails.card-apply', [
            'fullName' => $fullName,
        ], function ($msg) use ($application) {
            $msg->to($application->email)
                ->subject("Card Application Received")
                ->from(env('MAIL_FROM_ADDRESS'), 'URBank');
        });
    }
}
