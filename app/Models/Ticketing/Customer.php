<?php

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'user_id', 'customer_code', 'full_name', 'mobile', 'email', 'address',
        'state', 'district', 'city', 'pincode', 'social_links', 'profile_photo', 'cover_photo', 'status',
    ];

    protected function casts(): array
    {
        return ['social_links' => 'array'];
    }

    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function sales(): HasMany { return $this->hasMany(TicketSale::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }
    public function winners(): HasMany { return $this->hasMany(DrawWinner::class); }
    public function charges(): HasMany { return $this->hasMany(Charge::class); }
    public function withdrawals(): HasMany { return $this->hasMany(Withdrawal::class); }

    public function scopeForCurrentAdmin($query)
    {
        return auth()->user()?->isSuperAdmin() ? $query : $query->where('admin_id', auth()->id());
    }
}
