<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Charge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'customer_id', 'ticket_id', 'bank_account_id', 'charge_name', 'amount',
        'description', 'is_refundable', 'status', 'attachment', 'due_date', 'admin_notes',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'is_refundable' => 'boolean', 'due_date' => 'date'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
    public function payments(): HasMany { return $this->hasMany(ChargePayment::class); }
}
