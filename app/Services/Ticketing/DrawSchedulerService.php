<?php

namespace App\Services\Ticketing;

use App\Models\Ticketing\Draw;
use App\Models\Ticketing\TicketType;

class DrawSchedulerService
{
    public function resolveDraw(TicketType $type, int $adminId, ?string $date = null): Draw
    {
        $drawDate = $date ?: match ($type->frequency) {
            'daily' => now()->toDateString(),
            'weekly' => now()->endOfWeek()->toDateString(),
            'monthly' => now()->endOfMonth()->toDateString(),
            'festival' => $type->festival_date?->toDateString() ?? now()->toDateString(),
        };

        return Draw::firstOrCreate(
            ['admin_id' => $adminId, 'ticket_type_id' => $type->id, 'draw_date' => $drawDate],
            [
                'draw_number' => $this->nextNumber($type),
                'draw_time' => $type->rules['draw_time'] ?? null,
                'status' => 'scheduled',
                'prize_snapshot' => $type->prizes()->orderBy('position')->pluck('amount', 'position')->toArray(),
            ]
        );
    }

    private function nextNumber(TicketType $type): string
    {
        do {
            $number = 'DRW-'.strtoupper(substr($type->frequency, 0, 3)).'-'.now()->format('Ymd').'-'.random_int(1000, 9999);
        } while (Draw::where('draw_number', $number)->exists());

        return $number;
    }
}
