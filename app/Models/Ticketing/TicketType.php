<?php

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'name', 'frequency', 'ticket_price', 'description', 'rules', 'image',
        'festival_date', 'festival_name', 'is_active',
    ];

    protected function casts(): array
    {
        return ['ticket_price' => 'decimal:2', 'rules' => 'array', 'festival_date' => 'date', 'is_active' => 'boolean'];
    }

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function prizes(): HasMany { return $this->hasMany(DrawPrize::class); }
    public function draws(): HasMany { return $this->hasMany(Draw::class); }

    public function scopeForCurrentAdmin($query)
    {
        return auth()->user()?->isSuperAdmin() ? $query : $query->where('admin_id', auth()->id());
    }
}
