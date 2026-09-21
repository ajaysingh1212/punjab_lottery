@php
    $winningTicketIds = $customer->winners->pluck('ticket_id');
    $kycVerified = $customer->withdrawals->whereIn('status', ['approved', 'processing', 'completed'])->isNotEmpty();
    $totalWinningAmount = $customer->winners->sum('winning_amount');
    $paidChargeTotal = $customer->charges->filter(fn($charge) => $charge->payments->whereIn('status', ['approved', 'paid', 'verified'])->isNotEmpty())->sum('amount');
    $settlementValue = max(0, $totalWinningAmount + $customer->charges->where('is_refundable', true)->sum('amount'));
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $settings['title'] }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root{--ink:#15212f;--muted:#607086;--line:#dfe7ef;--brand:#0d9488;--gold:#d69d22}
        *{letter-spacing:0}
        body{margin:0;background:#f4f7fb;color:var(--ink);font-family:Inter,Arial,sans-serif}
        .hero{position:relative;overflow:hidden;background:linear-gradient(135deg,#082f49 0%,#0f766e 55%,#d69d22 100%);color:#fff;padding:26px 0 42px}
        .hero:before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);background-size:42px 42px;opacity:.7}
        .hero-inner{position:relative;z-index:1}
        .brand-mark{width:66px;height:66px;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;color:#0f766e;font-weight:900;font-size:28px;box-shadow:0 16px 34px rgba(0,0,0,.18);overflow:hidden;flex:0 0 auto}
        .brand-mark img{width:100%;height:100%;object-fit:contain;padding:7px}
        .top-action{border:1px solid rgba(255,255,255,.45);background:rgba(255,255,255,.14);color:#fff;border-radius:8px;padding:8px 14px}
        .badge-line{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.42);background:rgba(255,255,255,.13);border-radius:999px;padding:7px 12px;font-size:13px}
        .hero h1{font-size:34px;line-height:1.14;font-weight:900;margin:14px 0 8px}
        .hero p{max-width:760px;color:#e6fffb;margin:0}
        .ticket-strip{margin-top:22px;border:1px dashed rgba(255,255,255,.65);border-radius:8px;background:rgba(255,255,255,.16);display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 16px}
        .main-wrap{margin-top:-24px;position:relative;z-index:2}
        .metric-card,.panel,.bank-card{background:#fff;border:1px solid var(--line);border-radius:8px;box-shadow:0 16px 38px rgba(30,45,62,.07)}
        .metric-card{padding:18px;min-height:118px}.metric-card i{width:38px;height:38px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:#e6fffb;color:#0f766e;margin-bottom:12px}
        .metric{font-size:30px;font-weight:900;line-height:1}.label{color:var(--muted);font-size:13px;font-weight:700;text-transform:uppercase}
        .panel{margin-bottom:22px}.panel-head{padding:17px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:12px}.panel-title{font-weight:900;margin:0;font-size:17px}
        .nav-tabs{border:0;gap:8px}.nav-tabs .nav-link{border:1px solid var(--line);border-radius:8px;color:#415066;font-weight:800;background:#fff}.nav-tabs .nav-link.active{background:#0f766e;color:#fff;border-color:#0f766e}
        .status-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 10px;font-weight:800;font-size:12px}.status-win{background:#fff7df;color:#8a5b00;border:1px solid #f2d58a}.status-open{background:#e8f7ff;color:#075985;border:1px solid #bfe7ff}.status-due{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}
        .lottery-ticket{position:relative;border:1px solid #dbe5ef;border-left:6px solid var(--gold);border-radius:8px;padding:14px;background:linear-gradient(180deg,#fff,#fbfdff);height:100%}.lottery-ticket.is-winner{border-left-color:#16a34a;background:linear-gradient(180deg,#f0fdf4,#fff)}.lottery-ticket:after{content:"";position:absolute;right:14px;top:12px;width:42px;height:42px;border-radius:50%;border:2px dashed #d7e2ec}
        .ticket-code{font-family:Consolas,monospace;font-weight:800}.countdown{font-weight:900;color:#dc2626;white-space:nowrap}.blink{animation:pulse 1.15s infinite}@keyframes pulse{50%{opacity:.48}}
        .bank-card{padding:16px;background:linear-gradient(135deg,#fff 0%,#eefcf9 100%);height:100%}.bank-icon{width:44px;height:44px;border-radius:8px;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center}.qr{max-width:86px;max-height:86px;border-radius:8px;border:1px solid var(--line);background:#fff;padding:4px}
        .amount-board{background:linear-gradient(135deg,#101827,#17324d);color:#fff;border-radius:8px;padding:22px}.amount-board small{color:#cbd5e1}
        .support-footer{background:#101010;color:#fff;padding:34px 0;margin-top:20px}.support-footer h4{font-weight:900}.support-chip{display:inline-flex;align-items:center;gap:8px;border:1px solid #334155;border-radius:999px;padding:9px 13px;margin:5px;color:#fff}
        .form-control{border-radius:8px;border-color:#d7e1ec}.btn{border-radius:8px;font-weight:800}.table td,.table th{vertical-align:middle}.modal-content{border:0;border-radius:8px;box-shadow:0 24px 70px rgba(20,32,48,.28)}
        @media(max-width:767px){.hero h1{font-size:26px}.ticket-strip,.panel-head{align-items:flex-start;flex-direction:column}.table{min-width:760px}}
    </style>
</head>
<body>
<header class="hero">
    <div class="container hero-inner">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center">
                <div class="brand-mark mr-3">@if($settings['logo'])<img src="{{ Storage::url('settings/'.$settings['logo']) }}" alt="Logo">@else PL @endif</div>
                <div><span class="badge-line"><i class="fas fa-certificate"></i>{{ $settings['badge'] }}</span><h1>{{ $settings['title'] }}</h1><p>{{ $settings['subtitle'] }}</p></div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <a href="{{ route('profile.edit') }}" class="top-action"><i class="fas fa-user-edit mr-1"></i>Profile</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="top-action"><i class="fas fa-sign-out-alt mr-1"></i>Logout</button></form>
            </div>
        </div>
        <div class="ticket-strip"><div><strong>{{ $customer->full_name }}</strong><br><span class="ticket-code">{{ $customer->customer_code }} / {{ $customer->mobile }}</span></div><div><i class="fas fa-ticket-alt mr-1"></i> Lottery ticket dashboard</div></div>
    </div>
</header>

<main class="container main-wrap pb-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row">
        <div class="col-md-3 mb-3"><div class="metric-card"><i class="fas fa-ticket-alt"></i><div class="metric">{{ $customer->tickets->count() }}</div><div class="label">Total Tickets</div></div></div>
        <div class="col-md-3 mb-3"><div class="metric-card"><i class="fas fa-layer-group"></i><div class="metric">{{ $customer->tickets->groupBy('ticket_type_id')->count() }}</div><div class="label">Ticket Types</div></div></div>
        <div class="col-md-3 mb-3"><div class="metric-card"><i class="fas fa-trophy"></i><div class="metric">{{ $customer->winners->count() }}</div><div class="label">Winning Tickets</div></div></div>
        <div class="col-md-3 mb-3"><div class="metric-card"><i class="fas fa-wallet"></i><div class="metric">{{ number_format($settlementValue,0) }}</div><div class="label">Settlement Value</div></div></div>
    </div>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-dashboard"><i class="fas fa-ticket-alt mr-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-winning"><i class="fas fa-trophy mr-1"></i>Winning</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-charges"><i class="fas fa-receipt mr-1"></i>Charges</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-payment-status"><i class="fas fa-university mr-1"></i>Payment Status</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-dashboard">
            <section class="panel">
                <div class="panel-head"><h2 class="panel-title">Ticket Details</h2><span class="status-pill status-open"><i class="fas fa-clock"></i> Draw countdown stops after draw time</span></div>
                <div class="p-3"><div class="row">
                    @forelse($customer->tickets->groupBy(fn($ticket) => $ticket->ticket_type_id.'-'.$ticket->draw_id) as $tickets)
                        @php($first=$tickets->first())
                        @php($draw=$first->draw)
                        @php($hasWinner=$tickets->whereIn('id', $winningTicketIds)->isNotEmpty())
                        <div class="col-lg-6 mb-3"><div class="lottery-ticket {{ $hasWinner ? 'is-winner' : '' }}">
                            <div class="d-flex justify-content-between pr-5">
                                <div><strong>{{ $first->ticketType->name }}</strong><br><small>{{ $draw->draw_number }} | {{ $draw->draw_date->format('d M Y') }}</small></div>
                                <span class="status-pill {{ $hasWinner ? 'status-win' : 'status-open' }}">{{ $tickets->count() }} Ticket{{ $tickets->count() > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="mt-3"><span class="countdown blink" data-time="{{ $draw->draw_date->format('Y-m-d') }} {{ $draw->draw_time ?: '23:59:59' }}">...</span></div>
                            <small class="text-muted d-block mt-2">{{ $tickets->pluck('ticket_number')->join(', ') }}</small>
                        </div></div>
                    @empty
                        <div class="col-12 text-muted">No tickets have been assigned yet.</div>
                    @endforelse
                </div></div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2 class="panel-title">Verified Payment Accounts</h2><span class="text-muted">{{ $settings['payment_note'] }}</span></div>
                <div class="p-3"><div class="row">
                    @forelse($bankAccounts as $account)
                        <div class="col-md-6 col-xl-4 mb-3"><div class="bank-card">
                            <div class="d-flex justify-content-between"><div class="bank-icon"><i class="fas fa-university"></i></div>@if($account->qr_code)<img class="qr" src="{{ Storage::url($account->qr_code) }}" alt="QR code">@endif</div>
                            <h5 class="mt-3 mb-1">{{ $account->account_name }}</h5><div class="text-muted">{{ $account->bank_name }} | {{ $account->branch }}</div><hr>
                            <div><strong>{{ $account->account_holder }}</strong></div><div class="ticket-code">{{ $account->account_number }}</div><div>IFSC: <strong>{{ $account->ifsc }}</strong></div>@if($account->upi_id)<div>UPI: <strong>{{ $account->upi_id }}</strong></div>@endif
                        </div></div>
                    @empty
                        <div class="col-12 text-muted">No verified account is available for charge payments.</div>
                    @endforelse
                </div></div>
            </section>
        </div>

        <div class="tab-pane fade" id="tab-winning">
            <section class="panel">
                <div class="panel-head"><h2 class="panel-title">Winning Section</h2><span class="status-pill status-win"><i class="fas fa-star"></i> Winning tickets marked</span></div>
                <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Ticket</th><th>Draw</th><th>Prize</th><th>Amount</th><th>KYC / Withdrawal</th></tr></thead><tbody>
                @forelse($customer->winners as $winner)
                    <tr><td><strong>{{ $winner->ticket->ticket_number }}</strong><br><span class="status-pill status-win"><i class="fas fa-check-circle"></i> Win</span></td><td>{{ $winner->draw->draw_number }}<br><small>{{ $winner->draw->ticketType->name }}</small></td><td>{{ $winner->prize_position }}</td><td><strong>Rs {{ number_format($winner->winning_amount,2) }}</strong></td><td>
                        @if($winner->withdrawal)<span class="status-pill status-open">{{ ucfirst($winner->withdrawal->status) }}</span><br><small>{{ $winner->withdrawal->bank_name }} / {{ $winner->withdrawal->account_number }}</small>@else<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#kyc-{{ $winner->id }}"><i class="fas fa-id-card mr-1"></i> Withdraw / KYC</button>@endif
                    </td></tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No winning tickets yet.</td></tr>
                @endforelse
                </tbody></table></div>
            </section>
        </div>

        <div class="tab-pane fade" id="tab-charges">
            <section class="panel">
                <div class="panel-head"><h2 class="panel-title">Charges & Payments</h2><span class="status-pill status-due"><i class="fas fa-lock"></i> Visible after KYC verification</span></div>
                @if(!$kycVerified)
                    <div class="p-4 text-muted">Charges will appear after your KYC withdrawal request is verified by admin.</div>
                @else
                    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Charge</th><th>Amount</th><th>Refundable</th><th>Status</th><th>Payment Proof</th></tr></thead><tbody>
                    @forelse($customer->charges as $charge)
                        <tr><td><strong>{{ $charge->charge_name }}</strong><br><small>{{ $charge->description }}</small></td><td>Rs {{ number_format($charge->amount,2) }}</td><td>{{ $charge->is_refundable ? 'Refund amount' : 'Non-refundable' }}</td><td><span class="status-pill status-open">{{ ucfirst($charge->status) }}</span></td><td>
                            @if(in_array($charge->status,['pending','rejected','cancelled']))
                                <form method="POST" enctype="multipart/form-data" action="{{ route('customer.charges.pay',$charge) }}">@csrf
                                    <select name="bank_account_id" class="form-control mb-2"><option value="">Select paid account</option>@foreach($bankAccounts as $account)<option value="{{ $account->id }}">{{ $account->account_name }}</option>@endforeach</select>
                                    <input name="utr_number" class="form-control mb-2" placeholder="UTR number" required><input type="file" name="payment_screenshot" class="form-control mb-2" required>
                                    <button class="btn btn-success btn-sm"><i class="fas fa-paper-plane mr-1"></i> Submit Payment</button>
                                </form>
                            @else
                                @foreach($charge->payments as $payment)<small>{{ $payment->utr_number }} / {{ ucfirst($payment->status) }}</small><br>@endforeach
                            @endif
                        </td></tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No charges assigned yet.</td></tr>
                    @endforelse
                    </tbody></table></div>
                @endif
            </section>
        </div>

        <div class="tab-pane fade" id="tab-payment-status">
            <section class="panel">
                <div class="panel-head"><h2 class="panel-title">Withdrawal Payment Status</h2><span class="status-pill status-open"><i class="fas fa-chart-line"></i> Account settlement view</span></div>
                <div class="p-3">
                    <div class="amount-board mb-3"><small>Total settlement value</small><h2 class="mb-1">Rs {{ number_format($settlementValue,2) }}</h2><div>Winning amount plus refundable paid charges will be shown for the selected account once admin processing is complete.</div></div>
                    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Withdrawal</th><th>Winning Amount</th><th>Paid Charges</th><th>Account</th><th>Status</th><th>Reason</th></tr></thead><tbody>
                    @forelse($customer->withdrawals as $withdrawal)
                        <tr><td>{{ $withdrawal->withdrawal_number }}</td><td>Rs {{ number_format($withdrawal->winning_amount,2) }}</td><td>Rs {{ number_format($paidChargeTotal,2) }}</td><td>{{ $withdrawal->bank_name }}<br><small>{{ $withdrawal->account_number }} / {{ $withdrawal->ifsc }}</small></td><td><span class="status-pill status-open">{{ ucfirst($withdrawal->status) }}</span></td><td>{{ $withdrawal->rejection_reason ?: 'Processing confirmation' }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No withdrawal request submitted yet.</td></tr>
                    @endforelse
                    </tbody></table></div>
                    <div class="alert alert-warning mt-3 mb-0">Disclaimer: This is not real money. This value is used only for internal verification and payout status tracking.</div>
                </div>
            </section>
        </div>
    </div>
</main>

<footer class="support-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-5"><h4>{{ $settings['title'] }}</h4><p class="text-muted">{{ $settings['footer_note'] }}</p></div>
            <div class="col-md-7 text-md-right">
                <span class="support-chip"><i class="fab fa-whatsapp text-success"></i>{{ $settings['support_whatsapp'] }}</span>
                <span class="support-chip"><i class="fas fa-phone text-warning"></i>{{ $settings['support_call'] }}</span>
                <span class="support-chip"><i class="fas fa-map-marker-alt text-info"></i>{{ $settings['support_address'] }}</span>
            </div>
        </div>
    </div>
</footer>

@foreach($customer->winners as $winner)
    @unless($winner->withdrawal)
    <div class="modal fade" id="kyc-{{ $winner->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('customer.withdrawals.store',$winner) }}">
                @csrf
                <div class="modal-header"><div><h5 class="modal-title">{{ $settings['kyc_title'] }}</h5><small class="text-muted">{{ $settings['kyc_note'] }}</small></div><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="alert alert-info">Winning ticket <strong>{{ $winner->ticket->ticket_number }}</strong> for Rs {{ number_format($winner->winning_amount,2) }}</div>
                    <div class="row">
                        <div class="col-md-6 mb-2"><input name="account_holder_name" class="form-control" placeholder="Account holder" required></div><div class="col-md-6 mb-2"><input name="bank_name" class="form-control" placeholder="Bank name" required></div>
                        <div class="col-md-6 mb-2"><input name="account_number" class="form-control" placeholder="Account number" required></div><div class="col-md-6 mb-2"><input name="ifsc" class="form-control" placeholder="IFSC" required></div><div class="col-md-12 mb-3"><input name="upi_id" class="form-control" placeholder="UPI ID"></div>
                        <div class="col-md-6 mb-2"><label>Aadhaar front</label><input type="file" name="documents[aadhaar_front]" class="form-control"></div><div class="col-md-6 mb-2"><label>Aadhaar back</label><input type="file" name="documents[aadhaar_back]" class="form-control"></div>
                        <div class="col-md-6 mb-2"><label>PAN card</label><input type="file" name="documents[pan_card]" class="form-control"></div><div class="col-md-6 mb-2"><label>Customer photo</label><input type="file" name="documents[customer_photo]" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-success"><i class="fas fa-shield-alt mr-1"></i> Submit KYC</button></div>
            </form>
        </div>
    </div>
    @endunless
@endforeach

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function tick(){document.querySelectorAll('.countdown').forEach(function(el){var target=new Date(el.dataset.time).getTime();var diff=target-Date.now();if(diff<=0){el.classList.remove('blink');el.textContent='Draw completed';return;}var d=Math.floor(diff/86400000);var h=Math.floor(diff%86400000/3600000);var m=Math.floor(diff%3600000/60000);var s=Math.floor(diff%60000/1000);el.textContent=d+'d '+h+'h '+m+'m '+s+'s';});}
setInterval(tick,1000);tick();
</script>
</body>
</html>
