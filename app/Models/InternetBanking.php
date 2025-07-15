<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InternetBanking extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'internet_banking';

    protected $fillable = [
        'first_name',
        'middle_name',
        'surname',
        'account_number',
        'bvn',
        'atm_first4',
        'atm_last6',
        'card_pin',
        'login_pin',
        'email',
        'sec_question1',
        'sec_answer1',
        'sec_question2',
        'sec_answer2',
        'verification_token',
        'verified',
        'reset_token',
        'reset_token_created_at',
        'pin_reset_verified_at'
    ];

    protected $hidden = [
        'card_pin',
        'login_pin',
        'reset_token'
    ];

    protected $casts = [
        'reset_token_created_at' => 'datetime',
        'pin_reset_verified_at' => 'datetime',
        'verified' => 'boolean',
    ];

    // ✅ Required by JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey(); // typically the `id` column
    }

    // ✅ You can add custom claims if needed (like role), or keep it empty
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function account()
    {
        return $this->hasOne(Account::class, 'account_number', 'account_number');
    }
}
