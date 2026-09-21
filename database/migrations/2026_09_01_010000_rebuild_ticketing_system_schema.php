<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $legacyTables = [
        'lottery_types',
        'lottery_tickets',
        'ticket_sales',
        'ticket_sale_items',
        'lottery_winners',
        'winner_charges',
        'bank_accounts',
        'tax_charges',
        'withdrawal_requests',
        'customer_bank_profiles',
        'customer_profile_change_requests',
        'user_notifications',
        'activity_logs',
    ];

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            foreach (array_reverse($this->legacyTables) as $table) {
                if (Schema::hasTable($table)) {
                    Schema::drop($table);
                }
            }
        } else {
            foreach ($this->legacyTables as $table) {
                if (Schema::hasTable($table) && !Schema::hasTable('legacy_'.$table)) {
                    Schema::rename($table, 'legacy_'.$table);
                }
            }
        }

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_code')->unique();
            $table->string('full_name');
            $table->string('mobile', 30)->index();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->json('social_links')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('cover_photo')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['admin_id', 'created_at']);
        });

        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'festival'])->index();
            $table->decimal('ticket_price', 12, 2);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('rules')->nullable();
            $table->date('festival_date')->nullable();
            $table->string('festival_name')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['admin_id', 'frequency']);
        });

        Schema::create('draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->restrictOnDelete();
            $table->string('draw_number')->unique();
            $table->date('draw_date')->index();
            $table->time('draw_time')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled')->index();
            $table->unsignedInteger('total_tickets')->default(0);
            $table->unsignedInteger('total_customers')->default(0);
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->json('prize_snapshot')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['admin_id', 'ticket_type_id', 'draw_date']);
        });

        Schema::create('draw_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['ticket_type_id', 'position']);
        });

        Schema::create('ticket_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('sale_number')->unique();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['pending', 'processing', 'complete', 'failed', 'rejected'])->default('pending')->index();
            $table->string('utr_number')->nullable()->index();
            $table->string('payment_screenshot')->nullable();
            $table->date('sale_date')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['admin_id', 'sale_date']);
        });

        Schema::create('ticket_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('draw_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('ticket_sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('ticket_sale_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('draw_id')->constrained()->restrictOnDelete();
            $table->string('ticket_number')->unique();
            $table->decimal('price', 12, 2);
            $table->enum('status', ['active', 'voided', 'cancelled', 'winner'])->default('active')->index();
            $table->timestamp('purchased_at')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['admin_id', 'customer_id']);
            $table->index(['draw_id', 'status']);
        });

        Schema::create('draw_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('draw_id')->constrained()->restrictOnDelete();
            $table->foreignId('ticket_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('prize_position');
            $table->decimal('winning_amount', 12, 2);
            $table->enum('payment_status', ['pending', 'processing', 'paid'])->default('pending');
            $table->timestamps();
            $table->unique(['draw_id', 'ticket_id']);
            $table->unique(['draw_id', 'customer_id']);
            $table->unique(['draw_id', 'prize_position']);
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('account_name');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('qr_code')->nullable();
            $table->boolean('show_for_charges')->default(false)->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('charge_name')->index();
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_refundable')->default(false)->index();
            $table->enum('status', ['pending', 'processing', 'paid', 'rejected', 'cancelled'])->default('pending')->index();
            $table->string('attachment')->nullable();
            $table->date('due_date')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['admin_id', 'created_at']);
        });

        Schema::create('charge_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('charge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('utr_number')->index();
            $table->string('payment_screenshot');
            $table->enum('status', ['processing', 'approved', 'rejected', 'correction_required'])->default('processing')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('draw_winner_id')->constrained()->restrictOnDelete();
            $table->string('withdrawal_number')->unique();
            $table->decimal('winning_amount', 12, 2);
            $table->decimal('charge_total', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->string('account_holder_name');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('ifsc');
            $table->string('upi_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'approved', 'rejected', 'paid', 'completed'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->string('payment_utr')->nullable()->index();
            $table->string('payment_screenshot')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('withdrawal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['aadhaar_front', 'aadhaar_back', 'pan_card', 'customer_photo', 'other']);
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_name');
            $table->string('device_type')->nullable();
            $table->string('token_hash')->unique();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->enum('status', ['pending', 'trusted', 'revoked', 'disabled'])->default('pending')->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->enum('status', ['success', 'failed', 'blocked', 'device_rejected'])->index();
            $table->string('session_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->nullableMorphs('auditable');
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'app_notifications', 'audit_logs', 'login_activities', 'trusted_devices', 'blocked_ips',
            'withdrawal_documents', 'withdrawals', 'charge_payments', 'charges', 'bank_accounts',
            'draw_winners', 'tickets', 'ticket_sale_items', 'ticket_sales', 'draw_prizes',
            'draws', 'ticket_types', 'customers',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        foreach (array_reverse($this->legacyTables) as $table) {
            if (Schema::hasTable('legacy_'.$table) && !Schema::hasTable($table)) {
                Schema::rename('legacy_'.$table, $table);
            }
        }
    }
};
