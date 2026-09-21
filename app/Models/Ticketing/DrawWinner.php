<?php

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DrawWinner extends Model
{
    protected $fillable = ['admin_id', 'draw_id', 'ticket_id', 'customer_id', 'prize_position', 'winning_amount', 'payment_status'];

    protected function casts(): array
    {
        return ['winning_amount' => 'decimal:2'];
    }

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function draw(): BelongsTo { return $this->belongsTo(Draw::class); }
    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function withdrawal(): HasOne { return $this->hasOne(Withdrawal::class); }
}
