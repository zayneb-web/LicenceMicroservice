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

    const PRICE_BASIC = 50;
    const PRICE_PROFESSIONAL = 100;
    const PRICE_ENTERPRISE = 150;

    protected $fillable = [
        'type',
        'status',
        'price',
        'description',
        'stripe_checkout_id',
        'stripe_payment_intent_id',
        'activated_at',
        'mongo_company_id',
        'verification_code'
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'price' => 'decimal:2'
    ];

    // Statuts possibles pour une licence
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING_VERIFICATION = 'pending_verification';

    public function payments()
    {
        return $this->hasMany(Payment::class);
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

    // 📆 Vérifie si elle est expirée
    public function isExpired()
    {
        return now()->greaterThan($this->end_date);
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE && !$this->isExpired();
    }

    public static function getPriceForType($type)
    {
        return match($type) {
            self::TYPE_BASIC => self::PRICE_BASIC,
            self::TYPE_PROFESSIONAL => self::PRICE_PROFESSIONAL,
            self::TYPE_ENTERPRISE => self::PRICE_ENTERPRISE,
            default => 0
        };
    }
}
