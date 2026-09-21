<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'password',
        'phone', 'avatar', 'cover_photo', 'bio', 'designation', 'department',
        'date_of_birth', 'gender', 'address', 'city', 'state', 'country', 'postal_code',
        'facebook', 'twitter', 'linkedin', 'instagram', 'github', 'website',
        'created_by', 'is_active', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function lotteryWinners(): HasMany
    {
        return $this->hasMany(\App\Models\Ticketing\DrawWinner::class, 'admin_id');
    }

    // Helper Methods
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && $this->avatar !== 'default-avatar.png') {
            return Storage::url('avatars/' . $this->avatar);
        }
        return asset('images/default-avatar.png');
    }

    public function getCoverPhotoUrlAttribute(): string
    {
        if ($this->cover_photo && $this->cover_photo !== 'default-cover.jpg') {
            return Storage::url('covers/' . $this->cover_photo);
        }
        return asset('images/default-cover.jpg');
    }

    public function getPrimaryRoleAttribute()
    {
        return $this->roles->first();
    }

    public function getPrimaryRoleBadgeAttribute(): string
    {
        $role = $this->primaryRole;
        if (!$role) return '<span class="badge badge-secondary">No Role</span>';
        $color = $role->color ?? '#6c757d';
        return '<span class="badge" style="background-color:' . $color . '">' . $role->name . '</span>';
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->address, $this->city, $this->state, $this->country, $this->postal_code]);
        return implode(', ', $parts);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getUnreadNotificationsCountAttribute(): int
    {
        return \DB::table('app_notifications')->where('user_id', $this->id)->whereNull('read_at')->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
