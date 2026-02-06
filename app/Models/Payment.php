<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'user_id',
        'gateway',
        'transaction_id',
        'amount',
        'service_charge',
        'total_amount',
        'status',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gateway_response' => 'encrypted',
    ];

    /**
     * Calculate total amount including service charge
     * 
     * @param float $consultationFee The consultation fee amount
     * @return float The total amount (consultation fee + 50 TK service charge)
     */
    public function calculateTotal(float $consultationFee): float
    {
        return $consultationFee + 50.00;
    }

    /**
     * Get the appointment associated with this payment
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the user who made this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
