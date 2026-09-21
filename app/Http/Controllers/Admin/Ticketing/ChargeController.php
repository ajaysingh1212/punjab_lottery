<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\BankAccount;
use App\Models\Ticketing\Charge;
use App\Models\Ticketing\ChargePayment;
use App\Models\Ticketing\Customer;
use App\Models\Ticketing\DrawWinner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChargeController extends Controller
{
    public function index(Request $request)
    {
        $charges = Charge::with('customer', 'ticket')
            ->when(!auth()->user()?->isSuperAdmin(), fn ($q) => $q->where('admin_id', auth()->id()))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->date_filter && $request->date_filter !== 'all', fn ($q) => $this->dateFilter($q, $request))
            ->when($request->search, fn ($q, $search) => $q->where(fn ($qq) => $qq
                ->where('charge_name', 'like', "%{$search}%")
                ->orWhereHas('customer', fn ($c) => $c->where('full_name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"))
                ->orWhereHas('ticket', fn ($t) => $t->where('ticket_number', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(20);

        $payments = ChargePayment::with('charge', 'customer')->latest()->take(20)->get();
        return view('admin.ticketing.charges.index', compact('charges', 'payments'));
    }

    public function create()
    {
        return view('admin.ticketing.charges.form', [
            'charge' => new Charge(),
            'customers' => Customer::forCurrentAdmin()->orderBy('full_name')->get(),
            'winners' => DrawWinner::with('customer', 'ticket')->where('admin_id', auth()->id())->latest()->take(100)->get(),
            'accounts' => BankAccount::where('admin_id', auth()->id())->where('status', 'active')->get(),
        ]);
    }

    public function show(Charge $charge)
    {
        $this->guard($charge);
        return view('admin.ticketing.charges.show', ['charge' => $charge->load('customer.tickets.ticketType', 'ticket.draw', 'payments')]);
    }

    public function edit(Charge $charge)
    {
        $this->guard($charge);
        return view('admin.ticketing.charges.form', [
            'charge' => $charge,
            'customers' => Customer::forCurrentAdmin()->orderBy('full_name')->get(),
            'winners' => DrawWinner::with('customer', 'ticket')->where('admin_id', auth()->id())->latest()->take(100)->get(),
            'accounts' => BankAccount::where('admin_id', auth()->id())->where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request);
        foreach ($data['customer_ids'] as $customerId) {
            Charge::create(collect($data)->except('customer_ids', 'draw_id')->all() + ['admin_id' => auth()->id(), 'customer_id' => $customerId]);
        }
        return redirect()->route('admin.charges.index')->with('success', 'Charge created.');
    }

    public function update(Request $request, Charge $charge)
    {
        $this->guard($charge);
        $data = $this->data($request, false);
        $charge->update(collect($data)->except('customer_ids', 'draw_id')->all() + ['customer_id' => $data['customer_ids'][0]]);
        return redirect()->route('admin.charges.show', $charge)->with('success', 'Charge updated.');
    }

    public function destroy(Charge $charge)
    {
        $this->guard($charge);
        $charge->delete();
        return redirect()->route('admin.charges.index')->with('success', 'Charge deleted.');
    }

    public function verifyPayment(Request $request, ChargePayment $payment)
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (int) $payment->admin_id === (int) auth()->id(), 403);
        $data = $request->validate([
            'status' => 'required|in:approved,rejected,correction_required',
            'admin_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($payment, $data) {
            $payment->update($data + ['verified_at' => now(), 'verified_by' => auth()->id()]);
            if ($data['status'] === 'approved') {
                $payment->charge->update(['status' => 'paid']);
            }
        });

        return back()->with('success', 'Payment verification updated.');
    }

    private function data(Request $request, bool $many = true): array
    {
        return $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customers,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'charge_name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'is_refundable' => 'nullable|boolean',
            'status' => 'required|in:pending,processing,paid,rejected,cancelled',
            'due_date' => 'nullable|date',
            'admin_notes' => 'nullable|string',
        ]) + ['is_refundable' => $request->boolean('is_refundable')];
    }

    private function guard(Charge $charge): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (int) $charge->admin_id === (int) auth()->id(), 403);
    }

    private function dateFilter($query, Request $request)
    {
        return match ($request->date_filter) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            'last_week' => $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]),
            'custom' => $query->whereBetween('created_at', [$request->from_date ?: today(), $request->to_date ?: today()]),
            default => $query,
        };
    }
}
