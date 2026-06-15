<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function checkinLogs()
    {
        return $this->hasMany(CheckinLog::class);
    }
}

