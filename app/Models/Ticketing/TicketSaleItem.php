<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketSaleItem extends Model
{
    protected $fillable = ['ticket_sale_id', 'ticket_type_id', 'draw_id', 'quantity', 'unit_price', 'line_total'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];
    }

    public function sale(): BelongsTo { return $this->belongsTo(TicketSale::class, 'ticket_sale_id'); }
    public function ticketType(): BelongsTo { return $this->belongsTo(TicketType::class); }
    public function draw(): BelongsTo { return $this->belongsTo(Draw::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }
}
