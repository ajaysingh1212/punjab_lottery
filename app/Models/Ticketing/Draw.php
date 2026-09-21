<?php

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Draw extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'ticket_type_id', 'draw_number', 'draw_date', 'draw_time', 'status',
        'total_tickets', 'total_customers', 'completed_by', 'completed_at', 'prize_snapshot',
    ];

    protected function casts(): array
    {
        return ['draw_date' => 'date', 'completed_at' => 'datetime', 'prize_snapshot' => 'array'];
    }

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function ticketType(): BelongsTo { return $this->belongsTo(TicketType::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }
    public function winners(): HasMany { return $this->hasMany(DrawWinner::class); }

    public function scopeForCurrentAdmin($query)
    {
        return auth()->user()?->isSuperAdmin() ? $query : $query->where('admin_id', auth()->id());
    }
}
