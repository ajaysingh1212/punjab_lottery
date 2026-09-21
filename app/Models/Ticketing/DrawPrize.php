<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawPrize extends Model
{
    protected $fillable = ['ticket_type_id', 'position', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function ticketType(): BelongsTo { return $this->belongsTo(TicketType::class); }
}
