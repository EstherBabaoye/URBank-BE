<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'first_name',
        'middle_name',
        'surname',
        'email',
        'phone',
        'card_type',
        'sub_card_type',
        'reason',
        'other_reason',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
