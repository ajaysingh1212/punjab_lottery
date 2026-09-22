<?php

namespace App\Http\Controllers\Customer\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Ticketing\BankAccount;
use App\Models\Ticketing\Charge;
use App\Models\Ticketing\DrawWinner;
use App\Models\Ticketing\Customer;
use App\Services\Ticketing\WithdrawalService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $customer = $this->customer();
        $customer->load('admin', 'tickets.draw.ticketType', 'winners.ticket.draw.ticketType', 'winners.withdrawal', 'charges.payments', 'charges.bankAccount', 'withdrawals');
        $bankAccounts = BankAccount::where('admin_id', $customer->admin_id)->where('show_for_charges', true)->where('status', 'active')->get();
        $settings = [
            'title' => SiteSetting::get('customer_dashboard_title', 'Punjab Lottery Prize Desk'),
            'subtitle' => SiteSetting::get('customer_dashboard_subtitle', 'Track your lottery tickets, live draw countdowns, winning claims, and secure charge payments in one place.'),
            'badge' => SiteSetting::get('customer_dashboard_badge', 'Official Punjab Lottery Customer Panel'),
            'logo' => SiteSetting::get('customer_dashboard_logo') ?: SiteSetting::get('site_logo'),
            'kyc_title' => SiteSetting::get('customer_kyc_title', 'Punjab Lottery KYC Verification'),
            'kyc_note' => SiteSetting::get('customer_kyc_note', 'Bank details are required for withdrawal. Aadhaar, PAN, and photo uploads are optional but help the admin verify your claim faster.'),
            'payment_note' => SiteSetting::get('customer_payment_note', 'Use only the verified payment accounts shown here. After payment, submit UTR and screenshot for admin verification.'),
            'support_whatsapp' => SiteSetting::get('customer_support_whatsapp', $customer->admin?->phone ?: '+91 9876543210'),
            'support_call' => SiteSetting::get('customer_support_call', $customer->admin?->phone ?: '+91 9876543210'),
            'support_address' => SiteSetting::get('customer_support_address', $customer->admin?->full_address ?: 'Ludhiana, Punjab'),
            'footer_note' => SiteSetting::get('customer_footer_note', 'Independent ticket seller information and customer support desk.'),
        ];

        return view('customer.ticketing.dashboard', compact('customer', 'bankAccounts', 'settings'));
    }

    public function withdraw(Request $request, DrawWinner $winner, WithdrawalService $service)
    {
        $customer = $this->customer();
        abort_unless((int) $winner->customer_id === (int) $customer->id, 403);
        abort_if($winner->withdrawal()->exists(), 422, 'Withdrawal already requested.');

        $data = $request->validate([
            'account_holder_name' => 'required|string|max:160',
            'bank_name' => 'required|string|max:160',
            'account_number' => 'required|string|max:80',
            'ifsc' => 'required|string|max:40',
            'upi_id' => 'nullable|string|max:160',
            'documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $withdrawal = $service->request($winner->load('customer'), collect($data)->except('documents')->all());
        foreach ($request->file('documents', []) as $type => $file) {
            $withdrawal->documents()->create([
                'document_type' => in_array($type, ['aadhaar_front', 'aadhaar_back', 'pan_card', 'customer_photo', 'other'], true) ? $type : 'other',
                'path' => $file->store('withdrawal-documents', 'public'),
            ]);
        }

        return back()->with('success', 'Withdrawal request submitted.');
    }

    public function payCharge(Request $request, Charge $charge)
    {
        $customer = $this->customer();
        abort_unless((int) $charge->customer_id === (int) $customer->id, 403);

        $data = $request->validate([
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'utr_number' => 'required|string|max:120',
            'payment_screenshot' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $charge->payments()->create([
            'admin_id' => $charge->admin_id,
            'customer_id' => $customer->id,
            'bank_account_id' => $data['bank_account_id'] ?? $charge->bank_account_id,
            'utr_number' => $data['utr_number'],
            'payment_screenshot' => $request->file('payment_screenshot')->store('charge-payments', 'public'),
            'status' => 'processing',
        ]);
        $charge->update(['status' => 'processing']);

        return back()->with('success', 'Payment proof submitted.');
    }

    private function customer(): Customer
    {
        $customer = Customer::where('user_id', auth()->id())->first();
        abort_unless($customer, 403);

        return $customer;
    }
}
