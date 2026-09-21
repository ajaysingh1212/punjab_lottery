<?php

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketSale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'customer_id', 'sale_number', 'total_amount', 'payment_status',
        'utr_number', 'payment_screenshot', 'sale_date', 'notes',
    ];

    protected function casts(): array
    {
        return ['total_amount' => 'decimal:2', 'sale_date' => 'date'];
    }

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(TicketSaleItem::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }

    public function scopeForCurrentAdmin($query)
    {
        return auth()->user()?->isSuperAdmin() ? $query : $query->where('admin_id', auth()->id());
    }
}
