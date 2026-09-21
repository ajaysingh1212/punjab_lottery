<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'before_values', 'after_values', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['before_values' => 'array', 'after_values' => 'array'];
    }
}
