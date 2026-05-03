<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'province',
        'city',
        'barangay',
        'address_line1',
        'address_line2',
        'description',
        'late_fee_type',
        'late_fee_value',
    ];

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->barangay,
            $this->city,
            $this->province,
        ]);

        return implode(', ', $parts);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    public function calculateLateFee(float $monthlyRent): float
    {
        if ((float) $this->late_fee_value <= 0) {
            return 0.0;
        }

        if ($this->late_fee_type === 'percentage') {
            return round(((float) $this->late_fee_value / 100) * $monthlyRent, 2);
        }

        return (float) $this->late_fee_value;
    }
}
