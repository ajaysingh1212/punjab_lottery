<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\Draw;
use App\Models\Ticketing\Ticket;
use App\Services\Ticketing\WinnerSelectionService;
use Illuminate\Http\Request;

class DrawController extends Controller
{
    public function index(Request $request)
    {
        $draws = Draw::forCurrentAdmin()->with('ticketType', 'winners')
            ->when($request->frequency, fn ($q, $frequency) => $q->whereHas('ticketType', fn ($type) => $type->where('frequency', $frequency)))
            ->when($request->date, fn ($q, $date) => $q->whereDate('draw_date', $date))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('draw_date')
            ->paginate(20);

        $tabStats = collect(['daily', 'weekly', 'monthly', 'festival'])->mapWithKeys(function ($frequency) {
            $query = Draw::forCurrentAdmin()->whereHas('ticketType', fn ($type) => $type->where('frequency', $frequency));
            return [$frequency => [
                'draws' => (clone $query)->count(),
                'customers' => (clone $query)->sum('total_customers'),
                'tickets' => (clone $query)->sum('total_tickets'),
                'today' => (clone $query)->whereDate('draw_date', today())->count(),
            ]];
        });

        return view('admin.ticketing.draws.index', compact('draws', 'tabStats'));
    }

    public function show(Draw $draw)
    {
        $this->guard($draw);
        $draw->load('ticketType', 'tickets.customer', 'winners.ticket', 'winners.customer');
        $customerRows = $draw->tickets->groupBy('customer_id')->map(fn ($tickets) => [
            'customer' => $tickets->first()->customer,
            'tickets' => $tickets,
            'active_count' => $tickets->where('status', 'active')->count(),
        ]);

        return view('admin.ticketing.draws.show', compact('draw', 'customerRows'));
    }

    public function assign(Request $request, Draw $draw, WinnerSelectionService $service)
    {
        $this->guard($draw);
        $data = $request->validate([
            'mode' => 'required|in:manual,auto',
            'ticket_id' => 'required_if:mode,manual|nullable|exists:tickets,id',
            'ticket_ids' => 'nullable|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'prize_position' => 'required_if:mode,manual|nullable|integer|min:1|max:10',
            'positions' => 'required_if:mode,auto|array',
            'positions.*' => 'integer|min:1|max:10',
        ]);

        if ($data['mode'] === 'auto') {
            $service->autoAssign($draw, $data['positions'] ?? [], $data['ticket_ids'] ?? null);
            return back()->with('success', 'Winners auto-selected.');
        }

        $ticket = Ticket::forCurrentAdmin()->findOrFail($data['ticket_id']);
        $service->assign($draw, $ticket, (int) $data['prize_position']);
        return back()->with('success', 'Winner assigned.');
    }

    public function complete(Draw $draw, WinnerSelectionService $service)
    {
        $this->guard($draw);
        $service->complete($draw);
        return back()->with('success', 'Draw completed.');
    }

    private function guard(Draw $draw): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (int) $draw->admin_id === (int) auth()->id(), 403);
    }
}
