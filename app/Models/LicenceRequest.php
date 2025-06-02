<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenceRequest extends Model
{
    use HasFactory;

    // Types de licences possibles
    const TYPE_BASIC = 'basic';
    const TYPE_PROFESSIONAL = 'professional';
    const TYPE_ENTERPRISE = 'enterprise';

    protected $fillable = [
        'company_name', 
        'company_email', 
        'company_phone',
        'company_address', 
        'type',
        'description',
        'status',
        'requested_at',
        'validated_at',
        'rejected_at',
        'rejection_reason',
        'validated_by',
        'price',
        'duration_months',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'price' => 'decimal:2'
    ];

    // Statuts possibles pour une demande
    const STATUS_PENDING = 'pending';
    const STATUS_VALIDATED = 'validated';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function licence()
    {
        return $this->hasOne(Licence::class);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isValidated()
    {
        return $this->status === self::STATUS_VALIDATED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
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