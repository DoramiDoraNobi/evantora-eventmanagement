<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinLog extends Model
{
    protected $guarded = [];

    //
    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }
}

