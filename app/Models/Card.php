<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'card_type',
        'card_number',
        'card_number_hash',
        'first4',
        'last6',
        'cvv',
        'card_pin',
        'expiry_date',
    ];

    protected $hidden = [
        'card_number',
        'cvv',
        'card_pin',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
