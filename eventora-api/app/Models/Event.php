<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];

    //
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}

