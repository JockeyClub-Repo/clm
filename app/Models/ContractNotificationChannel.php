<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractNotificationChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'channel',
        'enabled',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}