<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'title', 'first_name', 'middle_name', 'surname', 'mothers_maiden_name',
        'gender', 'dob', 'marital_status', 'nationality', 'state_of_origin',
        'lga_of_origin', 'house_number', 'street_name', 'city', 'residential_lga',
        'residential_state', 'phone', 'email', 'id_type', 'id_number', 'id_issue_date',
        'id_expiry_date', 'bvn', 'nin', 'tin', 'employment_status', 'employer_name',
        'employer_address', 'annual_income', 'nok_first_name', 'nok_middle_name',
        'nok_surname', 'nok_gender', 'nok_dob', 'nok_relationship', 'nok_phone',
        'nok_email', 'nok_address', 'account_type', 'card_type', 'electronic_banking',
        'alert_preference', 'mandate_first_name', 'mandate_middle_name', 'mandate_surname',
        'mandate_id_type', 'mandate_id_number', 'mandate_phone', 'mandate_date',
        'declaration_name', 'declaration_date', 'mandate_signature',
    'declaration_signature', 'passport_photo', 'uploaded_id_file',
        'utility_bill', 'status', 'account_number' // <- only if you're using it
    ];

    protected $casts = [
        'dob' => 'date',
        'id_issue_date' => 'date',
        'id_expiry_date' => 'date',
        'nok_dob' => 'date',
        'mandate_date' => 'date',
        'declaration_date' => 'date',
        'electronic_banking' => 'boolean',
    ];

    public function internetBanking()
    {
        return $this->hasOne(InternetBanking::class, 'email', 'email');
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'account_number', 'account_number');
    }
}
