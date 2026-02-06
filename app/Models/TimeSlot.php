<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'start_time',
        'end_time',
        'is_available',
        'is_blocked',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_available' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Check if the time slot is available for booking.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->is_available && !$this->is_blocked;
    }

    /**
     * Book the time slot for an appointment.
     *
     * @param Appointment $appointment
     * @return void
     */
    public function book(Appointment $appointment): void
    {
        $this->is_available = false;
        $this->save();
    }

    /**
     * Release the time slot, making it available again.
     *
     * @return void
     */
    public function release(): void
    {
        $this->is_available = true;
        $this->save();
    }
}
