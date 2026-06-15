<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];

    //
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}

