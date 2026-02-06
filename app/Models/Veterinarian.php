<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Veterinarian extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'license_number',
        'experience_years',
        'bio',
        'consultation_fee',
        'profile_image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'experience_years' => 'integer',
        'consultation_fee' => 'decimal:2',
    ];

    /**
     * Get the user that owns the veterinarian profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specializations for the veterinarian.
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'veterinarian_specialization');
    }

    /**
     * Get the time slots for the veterinarian.
     */
    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    /**
     * Get the appointments for the veterinarian.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get available time slots for the veterinarian within a date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function availableSlots(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->timeSlots()
            ->where('is_available', true)
            ->where('is_blocked', false)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Calculate the average rating for the veterinarian.
     *
     * @return float
     */
    public function averageRating(): float
    {
        // For now, return 0.0 as ratings functionality will be implemented later
        // This can be extended when a ratings/reviews system is added
        return 0.0;
    }
}
