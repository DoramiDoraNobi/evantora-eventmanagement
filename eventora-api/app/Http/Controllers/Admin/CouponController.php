<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $organization = app('current_organization');
        $coupons = Coupon::where('organization_id', $organization->id)
            ->with('event')
            ->latest()
            ->paginate(15);
            
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $organization = app('current_organization');
        $events = $organization->events()->select('id', 'title')->get();
        return view('admin.coupons.create', compact('events'));
    }

    public function store(Request $request)
    {
        $organization = app('current_organization');

        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('coupons')->where(function ($query) use ($organization) {
                    return $query->where('organization_id', $organization->id);
                })
            ],
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'event_id' => [
                'nullable',
                Rule::exists('events', 'id')->where(function ($query) use ($organization) {
                    $query->where('organization_id', $organization->id);
                })
            ],
            'max_uses' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean'
        ]);

        $validated['organization_id'] = $organization->id;
        $validated['code'] = strtoupper($validated['code']);

        Coupon::create($validated);

        return redirect()->route('coupons.index')->with('status', 'Coupon created successfully.');
    }

    public function edit(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);
        $organization = app('current_organization');
        $events = $organization->events()->select('id', 'title')->get();
        return view('admin.coupons.edit', compact('coupon', 'events'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);
        $organization = app('current_organization');

        $validated = $request->validate([
            'code' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('coupons')->where(function ($query) use ($organization) {
                    return $query->where('organization_id', $organization->id);
                })->ignore($coupon->id)
            ],
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'event_id' => [
                'nullable',
                Rule::exists('events', 'id')->where(function ($query) use ($organization) {
                    $query->where('organization_id', $organization->id);
                })
            ],
            'max_uses' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean'
        ]);

        $validated['code'] = strtoupper($validated['code']);
        if (!$request->has('is_active')) {
            $validated['is_active'] = false;
        }

        $coupon->update($validated);

        return redirect()->route('coupons.index')->with('status', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);
        $coupon->delete();
        return redirect()->route('coupons.index')->with('status', 'Coupon deleted successfully.');
    }

    protected function authorizeCoupon(Coupon $coupon)
    {
        if ($coupon->organization_id !== app('current_organization')->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
