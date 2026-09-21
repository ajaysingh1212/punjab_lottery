<?php

namespace Tests\Feature;

use App\Models\Ticketing\Charge;
use App\Models\Ticketing\Customer;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use App\Services\Ticketing\TicketSaleService;
use App\Services\Ticketing\WinnerSelectionService;
use App\Services\Ticketing\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FreshTicketingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_ticket_draw_charge_and_withdrawal_workflow_for_all_frequencies(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $this->actingAs($admin);

        foreach (['daily', 'weekly', 'monthly', 'festival'] as $frequency) {
            $customer = Customer::create([
                'admin_id' => $admin->id,
                'customer_code' => 'CUST'.random_int(100000, 999999),
                'full_name' => ucfirst($frequency).' Customer',
                'mobile' => '90000'.random_int(10000, 99999),
                'email' => $frequency.'@example.test',
                'status' => 'active',
            ]);

            $type = TicketType::create([
                'admin_id' => $admin->id,
                'name' => ucfirst($frequency).' Ticket',
                'frequency' => $frequency,
                'ticket_price' => 50,
                'festival_name' => $frequency === 'festival' ? 'Harvest' : null,
                'festival_date' => $frequency === 'festival' ? now()->addDays(10)->toDateString() : null,
                'is_active' => true,
            ]);
            $type->prizes()->createMany([
                ['position' => 1, 'amount' => 1000],
                ['position' => 2, 'amount' => 500],
            ]);

            $sale = app(TicketSaleService::class)->create($customer, [
                ['ticket_type_id' => $type->id, 'quantity' => 10],
            ], ['payment_status' => 'complete']);

            $this->assertSame('500.00', $sale->total_amount);
            $this->assertCount(10, $sale->tickets);
            $this->assertSame(10, $sale->tickets->pluck('ticket_number')->unique()->count());

            $draw = $sale->items->first()->draw;
            $tickets = $sale->tickets()->orderBy('id')->get();
            $winner = app(WinnerSelectionService::class)->assign($draw, $tickets[2], 1);

            $this->expectExceptionForDuplicateCustomerWin($draw, $tickets[3]);

            Charge::create([
                'admin_id' => $admin->id,
                'customer_id' => $customer->id,
                'charge_name' => 'Processing Charge',
                'amount' => 100,
                'is_refundable' => false,
                'status' => 'pending',
            ]);

            $withdrawal = app(WithdrawalService::class)->request($winner->load('customer'), [
                'account_holder_name' => $customer->full_name,
                'bank_name' => 'Demo Bank',
                'account_number' => '1234567890',
                'ifsc' => 'DEMO0001',
                'upi_id' => 'demo@upi',
            ]);

            $withdrawal->update(['status' => 'completed', 'payment_utr' => 'UTR'.$frequency]);

            $this->assertSame('100.00', $withdrawal->charge_total);
            $this->assertSame('900.00', $withdrawal->net_amount);
            $this->assertSame('completed', $withdrawal->fresh()->status);
        }
    }

    private function expectExceptionForDuplicateCustomerWin($draw, $ticket): void
    {
        try {
            app(WinnerSelectionService::class)->assign($draw, $ticket, 2);
            $this->fail('Duplicate customer winner was allowed.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('already has a winning ticket', $exception->getMessage());
        }
    }
}
