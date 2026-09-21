<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankAccountController extends Controller
{
    public function index()
    {
        return view('admin.ticketing.banking.index', ['accounts' => BankAccount::where('admin_id', auth()->id())->latest()->paginate(20)]);
    }

    public function create() { return view('admin.ticketing.banking.form', ['account' => new BankAccount()]); }

    public function store(Request $request)
    {
        BankAccount::create($this->data($request) + ['admin_id' => auth()->id()]);
        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank account created.');
    }

    public function edit(BankAccount $bankAccount)
    {
        $this->guard($bankAccount);
        return view('admin.ticketing.banking.form', ['account' => $bankAccount]);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $this->guard($bankAccount);
        $bankAccount->update($this->data($request, $bankAccount));
        return redirect()->route('admin.bank-accounts.index')->with('success', 'Bank account updated.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $this->guard($bankAccount);
        $bankAccount->delete();
        return back()->with('success', 'Bank account removed.');
    }

    private function data(Request $request, ?BankAccount $account = null): array
    {
        $data = $request->validate([
            'account_name' => 'required|string|max:150',
            'bank_name' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:80',
            'ifsc' => 'nullable|string|max:40',
            'branch' => 'nullable|string|max:150',
            'account_holder' => 'nullable|string|max:150',
            'upi_id' => 'nullable|string|max:150',
            'qr_code' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'show_for_charges' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ]) + ['show_for_charges' => $request->boolean('show_for_charges')];

        if ($request->hasFile('qr_code')) {
            if ($account?->qr_code) {
                Storage::disk('public')->delete($account->qr_code);
            }
            $data['qr_code'] = $request->file('qr_code')->store('bank-qr-codes', 'public');
        } else {
            unset($data['qr_code']);
        }

        return $data;
    }

    private function guard(BankAccount $account): void
    {
        abort_unless(auth()->user()?->isSuperAdmin() || (int) $account->admin_id === (int) auth()->id(), 403);
    }
}
