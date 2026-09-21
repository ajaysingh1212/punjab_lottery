<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'customer_id', 'draw_winner_id', 'withdrawal_number', 'winning_amount',
        'charge_total', 'net_amount', 'account_holder_name', 'bank_name', 'account_number',
        'ifsc', 'upi_id', 'status', 'rejection_reason', 'payment_utr', 'payment_screenshot',
    ];

    protected function casts(): array
    {
        return ['winning_amount' => 'decimal:2', 'charge_total' => 'decimal:2', 'net_amount' => 'decimal:2'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function winner(): BelongsTo { return $this->belongsTo(DrawWinner::class, 'draw_winner_id'); }
    public function documents(): HasMany { return $this->hasMany(WithdrawalDocument::class); }
}
