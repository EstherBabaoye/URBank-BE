<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'account_type',
        'first_name',
        'middle_name',
        'surname',
        'email',
        'phone',
        'bvn',
        'house_number',
        'street_name',
        'city',
        'residential_lga',
        'residential_state',
        'id_type',
        'id_number',
        'id_issue_date',
        'id_expiry_date',
        'passport_photo',
        'utility_bill',
        'uploaded_id_file',
        'card_number_masked',
        'card_expiry',
        'account_created_at',
    ];

    protected $casts = [
        'id_issue_date' => 'date',
        'id_expiry_date' => 'date',
        'account_created_at' => 'datetime',
    ];
}
