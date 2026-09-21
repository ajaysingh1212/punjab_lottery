<?php

namespace App\Services\Ticketing;

use App\Models\Ticketing\Draw;
use App\Models\Ticketing\DrawWinner;
use App\Models\Ticketing\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WinnerSelectionService
{
    public function __construct(private AuditService $audit) {}

    public function assign(Draw $draw, Ticket $ticket, int $position): DrawWinner
    {
        if ($draw->status === 'completed') {
            throw ValidationException::withMessages(['draw' => 'Completed draws cannot be rerun or changed.']);
        }

        if ($ticket->draw_id !== $draw->id || $ticket->status !== 'active') {
            throw ValidationException::withMessages(['ticket_id' => 'The selected ticket is not eligible for this draw.']);
        }

        if ($draw->winners()->where('customer_id', $ticket->customer_id)->exists()) {
            throw ValidationException::withMessages(['ticket_id' => 'This customer already has a winning ticket in this draw.']);
        }

        $amount = $draw->prize_snapshot[$position] ?? null;
        if (!$amount || $position < 1 || $position > 10) {
            throw ValidationException::withMessages(['prize_position' => 'Select a configured prize position.']);
        }

        $winner = DrawWinner::create([
            'admin_id' => $draw->admin_id,
            'draw_id' => $draw->id,
            'ticket_id' => $ticket->id,
            'customer_id' => $ticket->customer_id,
            'prize_position' => $position,
            'winning_amount' => $amount,
        ]);
        $ticket->update(['status' => 'winner']);
        $this->audit->record('draw_winner.assigned', $winner, [], $winner->toArray());

        return $winner;
    }

    public function autoAssign(Draw $draw, array $positions, ?array $ticketIds = null): Collection
    {
        if ($draw->status === 'completed') {
            throw ValidationException::withMessages(['draw' => 'Completed draws cannot be rerun or changed.']);
        }

        $created = collect();
        $positions = collect($positions)->map(fn ($position) => (int) $position)->filter()->unique()->values();

        foreach ($positions as $position) {
            if ($draw->winners()->where('prize_position', $position)->exists()) {
                throw ValidationException::withMessages(['prize_position' => "Prize position {$position} is already assigned."]);
            }

            $excludedCustomers = $draw->winners()->pluck('customer_id')->all();
            $query = Ticket::where('draw_id', $draw->id)
                ->where('status', 'active')
                ->when($ticketIds, fn ($q) => $q->whereIn('id', $ticketIds))
                ->when($excludedCustomers, fn ($q) => $q->whereNotIn('customer_id', $excludedCustomers))
                ->inRandomOrder();

            $ticket = $query->first();
            if (!$ticket) {
                throw ValidationException::withMessages(['ticket_id' => 'Not enough eligible unique customers for selected prize positions.']);
            }

            $created->push($this->assign($draw->fresh(), $ticket, $position));
        }

        return $created;
    }

    public function complete(Draw $draw): void
    {
        $draw->update(['status' => 'completed', 'completed_by' => auth()->id(), 'completed_at' => now()]);
        $this->audit->record('draw.completed', $draw, [], $draw->toArray());
    }
}
