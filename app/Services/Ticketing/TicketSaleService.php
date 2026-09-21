<?php

namespace App\Services\Ticketing;

use App\Models\Ticketing\Customer;
use App\Models\Ticketing\TicketSale;
use App\Models\Ticketing\TicketType;
use Illuminate\Support\Facades\DB;

class TicketSaleService
{
    public function __construct(
        private DrawSchedulerService $draws,
        private TicketGenerationService $tickets,
        private AuditService $audit,
    ) {}

    public function create(Customer $customer, array $rows, array $meta = []): TicketSale
    {
        return DB::transaction(function () use ($customer, $rows, $meta) {
            $adminId = (int) $customer->admin_id;
            $types = TicketType::where('admin_id', $adminId)->whereIn('id', collect($rows)->pluck('ticket_type_id'))->get()->keyBy('id');
            $total = collect($rows)->sum(fn ($row) => $types[(int) $row['ticket_type_id']]->ticket_price * (int) $row['quantity']);
            $sale = TicketSale::create([
                'admin_id' => $adminId,
                'customer_id' => $customer->id,
                'sale_number' => $this->nextSaleNumber(),
                'total_amount' => $total,
                'payment_status' => $meta['payment_status'] ?? 'pending',
                'utr_number' => $meta['utr_number'] ?? null,
                'payment_screenshot' => $meta['payment_screenshot'] ?? null,
                'sale_date' => $meta['sale_date'] ?? now()->toDateString(),
                'notes' => $meta['notes'] ?? null,
            ]);

            foreach ($rows as $row) {
                $type = $types[(int) $row['ticket_type_id']];
                $draw = $this->draws->resolveDraw($type, $adminId, $row['draw_date'] ?? null);
                $item = $sale->items()->create([
                    'ticket_type_id' => $type->id,
                    'draw_id' => $draw->id,
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => $type->ticket_price,
                    'line_total' => $type->ticket_price * (int) $row['quantity'],
                ]);
                $this->tickets->generate($item->load('sale'));
                $draw->update([
                    'total_tickets' => $draw->tickets()->count(),
                    'total_customers' => $draw->tickets()->distinct('customer_id')->count('customer_id'),
                ]);
            }

            $this->audit->record('ticket_sale.created', $sale, [], $sale->toArray());
            return $sale->load('items.tickets');
        });
    }

    private function nextSaleNumber(): string
    {
        do {
            $number = 'SALE-'.now()->format('Ymd').'-'.random_int(10000, 99999);
        } while (TicketSale::where('sale_number', $number)->exists());

        return $number;
    }
}
