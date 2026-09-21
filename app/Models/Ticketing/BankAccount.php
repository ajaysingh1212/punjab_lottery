<?php

namespace App\Models\Ticketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id', 'account_name', 'bank_name', 'account_number', 'ifsc', 'branch',
        'account_holder', 'upi_id', 'qr_code', 'show_for_charges', 'status',
    ];

    protected function casts(): array
    {
        return ['show_for_charges' => 'boolean'];
    }
}
