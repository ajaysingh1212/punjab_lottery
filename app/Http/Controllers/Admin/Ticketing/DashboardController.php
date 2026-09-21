<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\Charge;
use App\Models\Ticketing\Customer;
use App\Models\Ticketing\Draw;
use App\Models\Ticketing\Ticket;
use App\Models\Ticketing\TicketSale;
use App\Models\Ticketing\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $period = $request->input('period', 'today');
        [$from, $to] = $this->periodRange($period);

        $sales = TicketSale::forCurrentAdmin()->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()]);
        $tickets = Ticket::forCurrentAdmin()->whereBetween('purchased_at', [$from->startOfDay(), $to->endOfDay()]);

        return view('admin.ticketing.dashboard.index', [
            'customerCount' => Customer::forCurrentAdmin()->count(),
            'ticketCount' => (clone $tickets)->count(),
            'salesTotal' => (clone $sales)->sum('total_amount'),
            'period' => $period,
            'drawBreakdown' => Draw::forCurrentAdmin()
                ->with('ticketType')
                ->whereBetween('draw_date', [$from->toDateString(), $to->toDateString()])
                ->get()
                ->groupBy(fn (Draw $draw) => $draw->ticketType->frequency),
            'upcomingDraws' => Draw::forCurrentAdmin()->where('status', 'scheduled')->orderBy('draw_date')->take(8)->get(),
            'pendingCharges' => Charge::where('admin_id', auth()->id())->where('status', 'pending')->sum('amount'),
            'pendingWithdrawals' => Withdrawal::where('admin_id', auth()->id())->whereIn('status', ['pending', 'processing'])->count(),
        ]);
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [Carbon::today(), Carbon::today()],
        };
    }
}
