<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payement extends Model
{
    use HasFactory;

    protected $fillable = [
        'licence_id',
        'amount',
        'payment_date',
        'payment_method',
        'stripe_payment_intent_id',
        'stripe_checkout_session_id',
        'status',
        'currency',
        'notes'
        
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // Statuts possibles pour un paiement
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PENDING_VERIFICATION = 'pending_verification'; 

    public function licence()
    {
        return $this->belongsTo(Licence::class);
    }

    public function isSuccessful()
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }
}
