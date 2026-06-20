<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'role',
        'avatar_url',
        'wallet_balance',
        'avg_rating',
        'is_banned',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password'       => 'hashed', // Fix: Merged single array to prevent deletion
        'is_banned'      => 'boolean',
        'wallet_balance' => 'decimal:2',
        'avg_rating'     => 'decimal:2',
    ];

    /**
     * Accessor for full name.
     * Modern safe syntax that handles missing attributes gracefully.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''))
        );
    }

    // ── Relationships ────────────────────────────────────────────────
    
    public function driverProfile(): HasOne
    { 
        return $this->hasOne(DriverProfile::class); 
    }

    public function documents(): HasMany
    { 
        return $this->hasMany(DriverDocument::class); 
    }

    public function ridesAsRider(): HasMany
    { 
        return $this->hasMany(Ride::class, 'rider_id'); 
    }

    public function ridesAsDriver(): HasMany
    { 
        return $this->hasMany(Ride::class, 'driver_id'); 
    }

    public function bids(): HasMany
    { 
        return $this->hasMany(RideBid::class, 'driver_id'); 
    }

    public function chatMessages(): HasMany
    { 
        return $this->hasMany(ChatMessage::class, 'sender_id'); 
    }

    public function ratingsGiven(): HasMany
    { 
        return $this->hasMany(Rating::class, 'rater_id'); 
    }

    public function ratingsReceived(): HasMany
    { 
        return $this->hasMany(Rating::class, 'ratee_id'); 
    }

    public function sosAlerts(): HasMany
    { 
        return $this->hasMany(SosAlert::class); 
    }

    public function walletTransactions(): HasMany
    { 
        return $this->hasMany(WalletTransaction::class); 
    }

    public function trustedContacts(): HasMany
    { 
        return $this->hasMany(TrustedContact::class); 
    }

    public function savedLocations(): HasMany
    { 
        return $this->hasMany(SavedLocation::class); 
    }

    public function appNotifications(): HasMany
    { 
        return $this->hasMany(AppNotification::class); 
    }

    public function supportTickets(): HasMany
    { 
        return $this->hasMany(SupportTicket::class); 
    }

    public function complaints(): HasMany
    { 
        return $this->hasMany(Complaint::class, 'complainant_id'); 
    }

    public function deliveries(): HasMany
    { 
        return $this->hasMany(DeliveryOrder::class, 'sender_id'); 
    }
}