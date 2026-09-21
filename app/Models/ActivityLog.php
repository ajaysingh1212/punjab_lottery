<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id', 'action', 'auditable_type', 'auditable_id',
        'before_values', 'after_values', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['before_values' => 'array', 'after_values' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, string $description, $model = null, array $properties = []): void
    {
        $user = auth()->user();
        static::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'after_values' => ['description' => $description, 'properties' => $properties],
        ]);
    }

    public function getActionBadgeAttribute(): string
    {
        $colors = [
            'created' => 'success', 'updated' => 'info',
            'deleted' => 'danger', 'login' => 'primary',
            'logout' => 'secondary', 'restored' => 'warning',
        ];
        $color = $colors[$this->action] ?? 'dark';
        return '<span class="badge badge-' . $color . '">' . ucfirst($this->action) . '</span>';
    }
}
