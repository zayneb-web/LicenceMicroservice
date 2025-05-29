<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licence extends Model
{
    use HasFactory;

    // Types de licences possibles
    const TYPE_BASIC = 'basic';
    const TYPE_PROFESSIONAL = 'professional';
    const TYPE_ENTERPRISE = 'enterprise';

    protected $fillable = [
        'mongo_company_id', 
        'licence_request_id',
        'type',
        'status',
        'start_date',
        'end_date',
        'price',
        'description',
        'stripe_checkout_id',
        'stripe_payment_intent_id',
        'license_key',
        'requested_at',
        'validated_at',
        'activated_at'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'requested_at' => 'datetime',
        'validated_at' => 'datetime',
        'activated_at' => 'datetime',
        'price' => 'decimal:2'
    ];

    // Statuts possibles pour une licence
    const STATUS_PENDING = 'pending';
    const STATUS_VALIDATED = 'validated';
    const STATUS_PAID = 'paid';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    public function licenceRequest()
    {
        return $this->belongsTo(LicenceRequest::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function generateLicenseKey()
    {
        return strtoupper(uniqid('LIC-') . bin2hex(random_bytes(4)));
    }

    public function getTypeLabel()
    {
        return match($this->type) {
            self::TYPE_BASIC => 'Basic',
            self::TYPE_PROFESSIONAL => 'Professional',
            self::TYPE_ENTERPRISE => 'Enterprise',
            default => 'Unknown'
        };
    }
}
