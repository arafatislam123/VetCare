<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'pet_owner_id',
        'veterinarian_id',
        'pet_id',
        'time_slot_id',
        'status', // pending, confirmed, in_progress, completed, cancelled
        'consultation_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Veterinarian::class);
    }

    public function petOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pet_owner_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pet_owner_id');
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
