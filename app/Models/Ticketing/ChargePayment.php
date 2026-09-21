<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargePayment extends Model
{
    protected $fillable = [
        'admin_id', 'charge_id', 'customer_id', 'bank_account_id', 'utr_number',
        'payment_screenshot', 'status', 'admin_notes', 'verified_at', 'verified_by',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function charge(): BelongsTo { return $this->belongsTo(Charge::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
