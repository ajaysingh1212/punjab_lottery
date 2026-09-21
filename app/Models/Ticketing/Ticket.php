<?php

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'customer_id', 'ticket_sale_id', 'ticket_sale_item_id',
        'ticket_type_id', 'draw_id', 'ticket_number', 'price', 'status', 'purchased_at',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'purchased_at' => 'datetime'];
    }

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function sale(): BelongsTo { return $this->belongsTo(TicketSale::class, 'ticket_sale_id'); }
    public function saleItem(): BelongsTo { return $this->belongsTo(TicketSaleItem::class, 'ticket_sale_item_id'); }
    public function ticketType(): BelongsTo { return $this->belongsTo(TicketType::class); }
    public function draw(): BelongsTo { return $this->belongsTo(Draw::class); }
    public function winner(): HasOne { return $this->hasOne(DrawWinner::class); }

    public function scopeForCurrentAdmin($query)
    {
        return auth()->user()?->isSuperAdmin() ? $query : $query->where('admin_id', auth()->id());
    }
}
