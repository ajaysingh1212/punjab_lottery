<?php

namespace App\Services\Ticketing;

use App\Models\Ticketing\Ticket;
use App\Models\Ticketing\TicketSaleItem;

class TicketGenerationService
{
    public function generate(TicketSaleItem $item): void
    {
        for ($i = 0; $i < $item->quantity; $i++) {
            Ticket::create([
                'admin_id' => $item->sale->admin_id,
                'customer_id' => $item->sale->customer_id,
                'ticket_sale_id' => $item->ticket_sale_id,
                'ticket_sale_item_id' => $item->id,
                'ticket_type_id' => $item->ticket_type_id,
                'draw_id' => $item->draw_id,
                'ticket_number' => $this->nextTicketNumber(),
                'price' => $item->unit_price,
                'status' => 'active',
                'purchased_at' => now(),
            ]);
        }
    }

    private function nextTicketNumber(): string
    {
        do {
            $number = 'TKT'.now()->format('ymd').random_int(100000, 999999);
        } while (Ticket::where('ticket_number', $number)->exists());

        return $number;
    }
}
