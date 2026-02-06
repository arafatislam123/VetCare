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
}
