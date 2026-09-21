<?php

namespace App\Services\Ticketing;

use App\Models\Ticketing\DrawWinner;
use App\Models\Ticketing\Withdrawal;

class WithdrawalService
{
    public function __construct(private AuditService $audit) {}

    public function request(DrawWinner $winner, array $bank): Withdrawal
    {
        $chargeTotal = $winner->customer->charges()->whereIn('status', ['pending', 'processing'])->sum('amount');
        $withdrawal = Withdrawal::create($bank + [
            'admin_id' => $winner->admin_id,
            'customer_id' => $winner->customer_id,
            'draw_winner_id' => $winner->id,
            'withdrawal_number' => 'WDR-'.now()->format('Ymd').'-'.random_int(10000, 99999),
            'winning_amount' => $winner->winning_amount,
            'charge_total' => $chargeTotal,
            'net_amount' => $winner->winning_amount - $chargeTotal,
            'status' => 'pending',
        ]);
        $this->audit->record('withdrawal.requested', $withdrawal, [], $withdrawal->toArray());

        return $withdrawal;
    }
}
