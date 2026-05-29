<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'description',
        'amount',
        'currency',
        'start_date',
        'end_date',
        'renewal_notice_days',
        'auto_renewal',
        'messaging_enabled',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renewal' => 'boolean',
        'messaging_enabled' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notificationChannels()
    {
        return $this->hasMany(ContractNotificationChannel::class);
    }

    public function notifications()
    {
        return $this->hasMany(ContractNotification::class);
    }
}