<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalDocument extends Model
{
    protected $fillable = ['withdrawal_id', 'document_type', 'path'];

    public function withdrawal(): BelongsTo { return $this->belongsTo(Withdrawal::class); }
}
