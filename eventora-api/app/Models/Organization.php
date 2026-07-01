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

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalEarningsAttribute()
    {
        return $this->orders()
            ->where('status', 'paid')
            ->get()
            ->sum(function ($order) {
                // earnings = total order amount minus platform service fee
                return $order->total - $order->service_fee;
            });
    }

    public function getWithdrawnAmountAttribute()
    {
        return $this->payouts()
            ->whereIn('status', ['pending', 'processing', 'paid'])
            ->sum('amount');
    }

    public function getAvailableBalanceAttribute()
    {
        return $this->total_earnings - $this->withdrawn_amount;
    }
}

