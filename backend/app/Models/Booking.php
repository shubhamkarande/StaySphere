<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'guest_id',
        'check_in',
        'check_out',
        'guests',
        'nights',
        'subtotal',
        'cleaning_fee',
        'service_fee',
        'total_amount',
        'status',
        'payment_intent_id',
        'payment_status',
        'special_requests',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'subtotal' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    // Relationships
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function host()
    {
        return $this->hasOneThrough(User::class, Listing::class, 'id', 'id', 'listing_id', 'host_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('check_in', '>', now())
                    ->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCurrent($query)
    {
        return $query->where('check_in', '<=', now())
                    ->where('check_out', '>', now())
                    ->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePast($query)
    {
        return $query->where('check_out', '<=', now())
                    ->where('status', self::STATUS_CONFIRMED);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown',
        };
    }

    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_PAID => 'Paid',
            self::PAYMENT_STATUS_FAILED => 'Failed',
            self::PAYMENT_STATUS_REFUNDED => 'Refunded',
            default => 'Unknown',
        };
    }

    public function getCanCancelAttribute()
    {
        return $this->status === self::STATUS_CONFIRMED && 
               $this->check_in > now()->addDays(1);
    }

    public function getCanReviewAttribute()
    {
        return $this->status === self::STATUS_COMPLETED && 
               !$this->review()->exists();
    }

    // Methods
    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // Handle refund logic here if needed
        return $this;
    }

    public function confirm()
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'payment_status' => self::PAYMENT_STATUS_PAID,
        ]);

        return $this;
    }

    public function complete()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);

        return $this;
    }
}