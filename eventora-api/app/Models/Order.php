<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    //
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

