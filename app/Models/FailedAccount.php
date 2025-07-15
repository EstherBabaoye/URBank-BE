<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'surname',
        'email',
        'bvn',
        'phone',
        'passport_photo',
        'utility_bill',
        'uploaded_id_file',
        'rejection_reason',
        'rejected_at',
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
    ];
}
