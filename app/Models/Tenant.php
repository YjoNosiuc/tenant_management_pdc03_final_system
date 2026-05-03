<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'owner_id',
        'phone_number',
        'emergency_contact_name',
        'emergency_contact_number',
        'province',
        'city',
        'barangay',
        'address_line1',
        'address_line2',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    /**
     * Current lease window: active status and today's date within start/end.
     */
    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->orderByDesc('start_date');
    }

    /**
     * All payments recorded against this tenant's leases.
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Lease::class);
    }

    public function termAgreements(): HasMany
    {
        return $this->hasMany(TenantTermAgreement::class);
    }

    public function hasAgreedToTerms(int $ownerId, int $version): bool
    {
        return $this->termAgreements()
            ->where('owner_id', $ownerId)
            ->where('version', $version)
            ->exists();
    }
}
