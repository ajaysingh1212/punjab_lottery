<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = Withdrawal::with('customer', 'winner.ticket')
            ->when(!auth()->user()?->isSuperAdmin(), fn ($q) => $q->where('admin_id', auth()->id()))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);
        return view('admin.ticketing.withdrawals.index', compact('withdrawals'));
    }

    public function show(Withdrawal $withdrawal)
    {
        $this->guard($withdrawal);

        $withdrawal->load([
            'customer.user',
            'winner.ticket.ticketType',
            'winner.draw.ticketType',
            'documents',
        ]);

        return view(
            'admin.ticketing.withdrawals.show',
            compact('withdrawal')
        );
    }

    public function update(Request $request, Withdrawal $withdrawal)
    {
        $this->guard($withdrawal);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'approved', 'rejected', 'paid', 'completed'])],
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
            'payment_utr' => 'nullable|string|max:120',
        ]);
        $withdrawal->update($data);
        return back()->with('success', 'Withdrawal updated.');
    }

    private function guard(Withdrawal $withdrawal): void
    {
        abort_unless((int) $withdrawal->admin_id === (int) auth()->id() || auth()->user()?->isSuperAdmin(), 403);
    }
}
