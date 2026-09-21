<?php

namespace App\Services\Ticketing;

use App\Models\Ticketing\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function record(string $action, ?Model $model = null, array $before = [], array $after = [], ?Request $request = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $model ? $model::class : null,
            'auditable_id' => $model?->getKey(),
            'before_values' => $before ?: null,
            'after_values' => $after ?: null,
            'ip_address' => $request?->ip() ?? request()?->ip(),
            'user_agent' => $request?->userAgent() ?? request()?->userAgent(),
        ]);
    }
}
