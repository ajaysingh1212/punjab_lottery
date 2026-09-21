<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\Customer;
use App\Models\Ticketing\TicketSale;
use App\Models\Ticketing\TicketType;
use App\Services\Ticketing\TicketSaleService;
use Illuminate\Http\Request;

class TicketSaleController extends Controller
{
    public function index()
    {
        return view('admin.ticketing.sales.index', ['sales' => TicketSale::forCurrentAdmin()->with('customer', 'items.ticketType', 'tickets')->latest()->paginate(20)]);
    }

    public function create()
    {
        return view('admin.ticketing.sales.form', [
            'customers' => Customer::forCurrentAdmin()->where('status', 'active')->orderBy('full_name')->get(),
            'types' => TicketType::forCurrentAdmin()->with('prizes')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, TicketSaleService $service)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_status' => 'required|in:pending,processing,complete,failed,rejected',
            'utr_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'items.*.quantity' => 'required|integer|min:1|max:500',
            'items.*.draw_date' => 'nullable|date',
        ]);
        $customer = Customer::forCurrentAdmin()->findOrFail($data['customer_id']);
        $sale = $service->create($customer, $data['items'], $data);

        return redirect()->route('admin.ticket-sales.show', $sale)->with('success', 'Sale created and tickets generated.');
    }

    public function show(TicketSale $ticketSale)
    {
        $this->guard($ticketSale);
        return view('admin.ticketing.sales.show', ['sale' => $ticketSale->load('customer', 'items.ticketType', 'items.draw', 'tickets')]);
    }

    public function edit(TicketSale $ticketSale)
    {
        $this->guard($ticketSale);
        return view('admin.ticketing.sales.form', [
            'sale' => $ticketSale->load('items.ticketType', 'items.draw'),
            'customers' => Customer::forCurrentAdmin()->where('status', 'active')->orderBy('full_name')->get(),
            'types' => TicketType::forCurrentAdmin()->with('prizes')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, TicketSale $ticketSale)
    {
        $this->guard($ticketSale);
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_status' => 'required|in:pending,processing,complete,failed,rejected',
            'utr_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        Customer::forCurrentAdmin()->findOrFail($data['customer_id']);
        $ticketSale->update($data);

        return redirect()->route('admin.ticket-sales.show', $ticketSale)->with('success', 'Sale updated.');
    }

    public function destroy(TicketSale $ticketSale)
    {
        $this->guard($ticketSale);
        $ticketSale->delete();
        return redirect()->route('admin.ticket-sales.index')->with('success', 'Sale deleted.');
    }

    private function guard(TicketSale $sale): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (int) $sale->admin_id === (int) auth()->id(), 403);
    }
}
