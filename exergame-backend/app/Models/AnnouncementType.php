<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'color',
        'icon',
        'is_active',
    ];

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }
}
