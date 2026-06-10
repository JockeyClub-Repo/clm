<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'previous_contract_id',
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

    protected $appends = [
        'status_label',
        'days_remaining',
        'renewal_date'
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

    public function files()
    {
        return $this->hasMany(ContractFile::class);
    }

    public function notificationChannels()
    {
        return $this->hasMany(ContractNotificationChannel::class);
    }

    public function notifications()
    {
        return $this->hasMany(ContractNotification::class);
    }

    public function getStatusLabelAttribute()
    {
        $today = Carbon::today();

        $endDate = Carbon::parse($this->end_date);

        $renewalDate = $endDate
            ->copy()
            ->subDays($this->renewal_notice_days);

        $daysToEnd = $today->diffInDays($endDate, false);

        if ($today->gt($endDate)) {
            return 'Vencido';
        }

        if ($today->isSameDay($endDate)) {
            return 'Vence Hoy';
        }

        if ($daysToEnd <= 7) {
            return 'Próximo a Vencer';
        }

        if ($today->gte($renewalDate)) {
            return 'Renovación Pendiente';
        }

        $daysToRenewal = $today->diffInDays($renewalDate, false);

        if ($daysToRenewal <= 7) {
            return 'Próximo a Renovar';
        }

        return 'Vigente';
    }

    public function getDaysRemainingAttribute()
    {
        return Carbon::today()->diffInDays($this->end_date, false);
    }

    public function getRenewalDateAttribute()
    {
        return Carbon::parse($this->end_date)
            ->subDays($this->renewal_notice_days)
            ->format('d/m/Y');
    }

    public function previousContract()
    {
        return $this->belongsTo(Contract::class, 'previous_contract_id');
    }

    public function renewedContracts()
    {
        return $this->hasMany(
            Contract::class,
            'previous_contract_id'
        );
    }
}