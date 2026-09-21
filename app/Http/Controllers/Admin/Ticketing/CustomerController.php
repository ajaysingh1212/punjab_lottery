<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::forCurrentAdmin()
            ->when($request->search, fn ($q, $search) => $q->where(fn ($qq) => $qq->where('full_name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(20);

        return view('admin.ticketing.customers.index', compact('customers'));
    }

    public function create() { return view('admin.ticketing.customers.form', ['customer' => new Customer()]); }

    public function store(Request $request)
    {
        $customer = Customer::create($this->data($request) + ['admin_id' => auth()->id(), 'customer_code' => 'CUST'.random_int(100000, 999999)]);
        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer created.');
    }

    public function show(Customer $customer)
    {
        $this->guard($customer);
        return view('admin.ticketing.customers.show', ['customer' => $customer->load('sales.items.ticketType', 'tickets.ticketType', 'tickets.draw', 'winners.ticket', 'charges', 'withdrawals')]);
    }

    public function edit(Customer $customer)
    {
        $this->guard($customer);
        return view('admin.ticketing.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->guard($customer);
        $customer->update($this->data($request));
        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $this->guard($customer);
        $customer->update(['status' => 'inactive']);
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deactivated.');
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'full_name' => 'required|string|max:160',
            'mobile' => 'required|string|max:30',
            'email' => 'nullable|email|max:160',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,blocked',
        ]);
    }

    private function guard(Customer $customer): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (int) $customer->admin_id === (int) auth()->id(), 403);
    }
}
