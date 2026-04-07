<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEmergencyContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'relationship',
        'is_aggreed_consent',
    ];

    protected $casts = [
        'is_aggreed_consent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
