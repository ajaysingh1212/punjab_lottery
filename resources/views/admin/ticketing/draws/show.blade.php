```blade
@extends('admin.layouts.app')

@section('title', 'Manage Draw')
@section('page-title', $draw->draw_number)

@section('content')

<style>
    .winner-page {
        --primary: #0f766e;
        --dark: #102a43;
        --gold: #d49a22;
        --line: #e2e8f0;
        --muted: #64748b;
    }

    .draw-top {
        padding: 21px;
        border-radius: 18px;
        color: #fff;
        background:
            radial-gradient(circle at 90% 15%, rgba(255,255,255,.12), transparent 25%),
            linear-gradient(135deg, #0b2537, #0f766e 70%, #c28a19);
        box-shadow: 0 18px 45px rgba(15,23,42,.12);
    }

    .draw-top h2 {
        margin: 0;
        font-size: 23px;
        font-weight: 900;
    }

    .draw-top p {
        margin: 5px 0 0;
        color: #d8fffa;
        font-size: 11px;
    }

    .top-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        margin: 3px;
        border-radius: 999px;
        color: #fff;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        font-size: 10px;
        font-weight: 800;
    }

    .control-card {
        margin-top: 18px;
        border: 1px solid var(--line);
        border-radius: 17px;
        background: #fff;
        box-shadow: 0 12px 35px rgba(15,23,42,.06);
        overflow: hidden;
    }

    .control-header {
        padding: 17px 20px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .control-title {
        margin: 0;
        font-size: 15px;
        font-weight: 900;
    }

    .control-subtitle {
        margin: 3px 0 0;
        color: var(--muted);
        font-size: 10px;
    }

    .control-body {
        padding: 20px;
    }

    .setup-option {
        position: relative;
        display: block;
        height: 100%;
        cursor: pointer;
    }

    .setup-option input {
        position: absolute;
        opacity: 0;
    }

    .setup-box {
        height: 100%;
        padding: 15px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #fff;
        transition: .2s ease;
    }

    .setup-option input:checked + .setup-box {
        border-color: var(--primary);
        background: #f0fdfa;
        box-shadow: 0 8px 20px rgba(15,118,110,.10);
    }

    .setup-icon {
        width: 39px;
        height: 39px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #ecfdf5;
        color: var(--primary);
        margin-bottom: 9px;
    }

    .setup-box strong {
        display: block;
        font-size: 12px;
    }

    .setup-box small {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 9px;
        line-height: 1.5;
    }

    .prize-card {
        height: 100%;
        padding: 14px;
        border-radius: 13px;
        border: 1px solid #f0d995;
        background: #fffaf0;
    }

    .prize-position {
        color: #8a5b00;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .prize-amount {
        margin-top: 4px;
        color: #765000;
        font-size: 20px;
        font-weight: 900;
    }

    .distribution-summary {
        padding: 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid var(--line);
    }

    .summary-number {
        font-size: 22px;
        font-weight: 900;
    }

    .summary-label {
        color: var(--muted);
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .customer-table {
        margin: 0;
    }

    .customer-table thead th {
        background: #f8fafc;
        border-top: 0;
        color: #64748b;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .customer-table td {
        vertical-align: middle;
        border-color: #edf1f5;
        font-size: 11px;
    }

    .customer-name {
        font-weight: 900;
    }

    .customer-code {
        color: #94a3b8;
        font-family: Consolas, monospace;
        font-size: 9px;
    }

    .ticket-list {
        color: #475569;
        font-family: Consolas, monospace;
        font-size: 9px;
        max-width: 230px;
        line-height: 1.6;
    }

    .active-ticket-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 25px;
        padding: 5px 7px;
        border-radius: 7px;
        color: #075985;
        background: #e0f2fe;
        font-size: 9px;
        font-weight: 900;
    }

    .position-select {
        min-width: 125px;
        border-radius: 9px;
        border-color: #d8e1e9;
        font-size: 10px;
        font-weight: 800;
    }

    .amount-preview {
        font-size: 12px;
        font-weight: 900;
        color: #0f766e;
    }

    .winner-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 999px;
        color: #8a5b00;
        background: #fff4c7;
        border: 1px solid #eed27b;
        font-size: 9px;
        font-weight: 900;
    }

    .auto-badge {
        color: #075985;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
    }

    .manual-badge {
        color: #7c2d12;
        background: #fff7ed;
        border: 1px solid #fed7aa;
    }

    .action-bar {
        position: sticky;
        bottom: 12px;
        z-index: 20;
        margin-top: 18px;
        padding: 12px;
        border-radius: 15px;
        background: rgba(255,255,255,.94);
        border: 1px solid var(--line);
        box-shadow: 0 14px 40px rgba(15,23,42,.14);
        backdrop-filter: blur(12px);
    }

    .main-action {
        border: 0;
        border-radius: 10px;
        padding: 10px 15px;
        font-size: 10px;
        font-weight: 900;
    }

    .btn-teal {
        color: #fff;
        background: var(--primary);
    }

    .btn-teal:hover {
        color: #fff;
        background: #115e59;
    }

    .guide-step {
        display: flex;
        gap: 12px;
        padding: 13px;
        border: 1px solid var(--line);
        border-radius: 12px;
        margin-bottom: 10px;
    }

    .guide-number {
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #ccfbf1;
        color: #0f766e;
        font-weight: 900;
        font-size: 12px;
    }

    .guide-step strong {
        font-size: 12px;
    }

    .guide-step p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 10px;
        line-height: 1.6;
    }

    .mobile-scroll {
        overflow-x: auto;
    }

    @media(max-width:767px) {
        .draw-top h2 {
            font-size: 20px;
        }

        .control-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .action-bar {
            position: relative;
            bottom: auto;
        }

        .main-action {
            width: 100%;
            margin-bottom: 6px;
        }
    }
</style>


<div class="winner-page">


    {{-- =========================================================
         DRAW HEADER
    ========================================================== --}}

    <div class="draw-top">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <h2>
                    <i class="fas fa-dice mr-2"></i>
                    {{ $draw->draw_number }}
                </h2>

                <p>
                    {{ $draw->ticketType->name ?? 'Ticket Draw' }}
                    •
                    {{ ucfirst($draw->ticketType->frequency ?? 'Other') }}
                    •
                    {{ optional($draw->draw_date)->format('d M Y') }}
                    •
                    {{ $draw->draw_time ?: '23:59' }}
                </p>

                <div class="mt-2">

                    <span class="top-chip">
                        <i class="fas fa-users"></i>
                        {{ $draw->total_customers }} Customers
                    </span>

                    <span class="top-chip">
                        <i class="fas fa-ticket-alt"></i>
                        {{ $draw->total_tickets }} Tickets
                    </span>

                    <span class="top-chip">
                        <i class="fas fa-trophy"></i>
                        {{ $draw->winners->count() }} Winners
                    </span>

                    <span class="top-chip">
                        <i class="fas fa-info-circle"></i>
                        {{ ucfirst(str_replace('_',' ', $draw->status)) }}
                    </span>

                </div>

            </div>


            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">

                <button
                    type="button"
                    class="btn btn-light btn-sm"
                    data-toggle="modal"
                    data-target="#drawGuideModal"
                >
                    <i class="fas fa-question-circle mr-1"></i>
                    Draw Guide
                </button>

                <a
                    href="{{ route('admin.draws.index') }}"
                    class="btn btn-outline-light btn-sm ml-1"
                >
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back
                </a>

            </div>

        </div>

    </div>


    {{-- =========================================================
         STEP 1 : DRAW SETUP
    ========================================================== --}}

    <div class="control-card">

        <div class="control-header">

            <div>

                <h3 class="control-title">
                    <i class="fas fa-sliders-h text-primary mr-2"></i>
                    Step 1 — Choose Winner Distribution
                </h3>

                <p class="control-subtitle">
                    First decide how customers should be distributed across winning positions.
                </p>

            </div>

            <span class="badge badge-light">
                {{ $draw->tickets->where('status','active')->groupBy('customer_id')->count() }}
                Eligible Customers
            </span>

        </div>


        <div class="control-body">

            <div class="row">


                {{-- ALL FIRST --}}

                <div class="col-md-4 mb-3">

                    <label class="setup-option">

                        <input
                            type="radio"
                            name="distribution_mode"
                            value="first"
                            class="distribution-mode"
                        >

                        <div class="setup-box">

                            <div class="setup-icon">
                                <i class="fas fa-crown"></i>
                            </div>

                            <strong>
                                Everyone → 1st Position
                            </strong>

                            <small>
                                All selected customers will be assigned
                                to position 1.
                            </small>

                        </div>

                    </label>

                </div>


                {{-- 1 TO 10 --}}

                <div class="col-md-4 mb-3">

                    <label class="setup-option">

                        <input
                            type="radio"
                            name="distribution_mode"
                            value="balanced"
                            class="distribution-mode"
                            checked
                        >

                        <div class="setup-box">

                            <div class="setup-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>

                            <strong>
                                Distribute 1–10
                            </strong>

                            <small>
                                Customers are automatically divided
                                as equally as possible across positions 1 to 10.
                            </small>

                        </div>

                    </label>

                </div>


                {{-- CUSTOM --}}

                <div class="col-md-4 mb-3">

                    <label class="setup-option">

                        <input
                            type="radio"
                            name="distribution_mode"
                            value="custom"
                            class="distribution-mode"
                        >

                        <div class="setup-box">

                            <div class="setup-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>

                            <strong>
                                Custom Position
                            </strong>

                            <small>
                                Admin can manually select a position
                                for every customer.
                            </small>

                        </div>

                    </label>

                </div>

            </div>


            {{-- PRIZES --}}

            <div class="mt-2">

                <div class="small font-weight-bold mb-2">
                    Prize Amount by Position
                </div>

                <div class="row">

                    @foreach($draw->prize_snapshot ?? [] as $position => $amount)

                        <div class="col-6 col-md-3 col-lg-2 mb-2">

                            <div class="prize-card">

                                <div class="prize-position">
                                    Position {{ $position }}
                                </div>

                                <div class="prize-amount">
                                    Rs {{ number_format($amount, 2) }}
                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>


            <div class="row mt-3">

                <div class="col-md-4 mb-2">

                    <div class="distribution-summary">

                        <div class="summary-number">
                            {{ $draw->tickets->where('status','active')->groupBy('customer_id')->count() }}
                        </div>

                        <div class="summary-label">
                            Eligible Customers
                        </div>

                    </div>

                </div>


                <div class="col-md-4 mb-2">

                    <div class="distribution-summary">

                        <div class="summary-number">
                            {{ $draw->tickets->where('status','active')->count() }}
                        </div>

                        <div class="summary-label">
                            Active Tickets
                        </div>

                    </div>

                </div>


                <div class="col-md-4 mb-2">

                    <div class="distribution-summary">

                        <div class="summary-number">
                            {{ $draw->winners->count() }}
                        </div>

                        <div class="summary-label">
                            Already Assigned
                        </div>

                    </div>

                </div>

            </div>


            <div class="mt-3">

                <button
                    type="button"
                    id="autoDistributeBtn"
                    class="btn btn-teal"
                >
                    <i class="fas fa-magic mr-1"></i>
                    Auto Distribute Customers
                </button>

                <button
                    type="button"
                    id="resetPositionsBtn"
                    class="btn btn-light ml-1"
                >
                    <i class="fas fa-undo mr-1"></i>
                    Reset Positions
                </button>

            </div>

        </div>

    </div>


    {{-- =========================================================
         CUSTOMER POSITION TABLE
    ========================================================== --}}

    <form
        method="POST"
        action="{{ route('admin.draws.winners.store', $draw) }}"
        id="winnerAssignmentForm"
    >

        @csrf

        <input
            type="hidden"
            name="mode"
            value="positioned"
        >


        <div class="control-card">

            <div class="control-header">

                <div>

                    <h3 class="control-title">
                        <i class="fas fa-users text-primary mr-2"></i>
                        Step 2 — Customer Winner Positions
                    </h3>

                    <p class="control-subtitle">
                        Each customer appears only once. If a customer bought
                        multiple tickets, the system will select one eligible ticket.
                    </p>

                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    data-toggle="modal"
                    data-target="#customerGuideModal"
                >
                    <i class="fas fa-info-circle mr-1"></i>
                    How this works
                </button>

            </div>


            <div class="mobile-scroll">

                <table class="table customer-table">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Customer</th>

                            <th>Phone</th>

                            <th>Tickets</th>

                            <th>Eligible</th>

                            <th>Position</th>

                            <th>Winning Amount</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>

                    @forelse($customerRows as $index => $row)

                        @php

                            $activeTickets = $row['tickets']->where(
                                'status',
                                'active'
                            );

                            $existingWinner = $draw->winners
                                ->where(
                                    'customer_id',
                                    $row['customer']->id
                                )
                                ->first();

                            $selectedPosition = $existingWinner
                                ? $existingWinner->prize_position
                                : '';

                        @endphp


                        <tr
                            data-customer-row="{{ $row['customer']->id }}"
                        >

                            <td>

                                <span class="font-weight-bold">
                                    {{ $loop->iteration }}
                                </span>

                            </td>


                            <td>

                                <div class="customer-name">
                                    {{ $row['customer']->full_name }}
                                </div>

                                <div class="customer-code">
                                    {{ $row['customer']->customer_code }}
                                </div>

                            </td>


                            <td>

                                {{ $row['customer']->mobile }}

                            </td>


                            <td>

                                <div class="ticket-list">

                                    {{ $row['tickets']->pluck('ticket_number')->join(', ') }}

                                </div>

                            </td>


                            <td>

                                <span class="active-ticket-count">

                                    {{ $activeTickets->count() }}

                                    active

                                </span>

                            </td>


                            <td>

                                <select
                                    name="customers[{{ $row['customer']->id }}][position]"
                                    class="form-control position-select winner-position"
                                    data-customer="{{ $row['customer']->id }}"
                                >

                                    <option value="">
                                        Not Selected
                                    </option>

                                    @for($position = 1; $position <= 10; $position++)

                                        @php
                                            $prizeExists = array_key_exists(
                                                $position,
                                                $draw->prize_snapshot ?? []
                                            );
                                        @endphp

                                        @if($prizeExists)

                                            <option
                                                value="{{ $position }}"
                                                data-amount="{{ $draw->prize_snapshot[$position] }}"
                                                @selected((int)$selectedPosition === $position)
                                            >
                                                Position {{ $position }}
                                            </option>

                                        @endif

                                    @endfor

                                </select>

                            </td>


                            <td>

                                <span
                                    class="amount-preview"
                                    data-amount-preview
                                >
                                    @if($selectedPosition && isset($draw->prize_snapshot[$selectedPosition]))
                                        Rs {{ number_format($draw->prize_snapshot[$selectedPosition], 2) }}
                                    @else
                                        —
                                    @endif
                                </span>

                            </td>


                            <td>

                                @if($existingWinner)

                                    <span class="winner-badge">
                                        <i class="fas fa-trophy"></i>
                                        Winner
                                    </span>

                                @else

                                    <span class="winner-badge auto-badge">
                                        <i class="fas fa-user"></i>
                                        Eligible
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >

                                <i class="fas fa-users fa-2x mb-2"></i>

                                <div class="font-weight-bold">
                                    No eligible customers
                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- STICKY ACTION BAR --}}

        <div class="action-bar">

            <div class="row align-items-center">

                <div class="col-md-6 mb-2 mb-md-0">

                    <strong class="small">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Review all positions before saving.
                    </strong>

                    <div class="text-muted" style="font-size:9px;">
                        Position change automatically changes the winning amount.
                    </div>

                </div>


                <div class="col-md-6 text-md-right">

                    <button
                        type="button"
                        class="btn main-action btn-light"
                        data-toggle="modal"
                        data-target="#drawGuideModal"
                    >
                        <i class="fas fa-book-open mr-1"></i>
                        Guide
                    </button>

                    <button
                        type="submit"
                        class="btn main-action btn-teal"
                    >
                        <i class="fas fa-save mr-1"></i>
                        Save Winner Positions
                    </button>

                    <button
                        type="button"
                        class="btn main-action btn-success"
                        data-toggle="modal"
                        data-target="#completeDrawModal"
                    >
                        <i class="fas fa-check-double mr-1"></i>
                        Complete Draw
                    </button>

                </div>

            </div>

        </div>

    </form>


    {{-- =========================================================
         EXISTING WINNERS
    ========================================================== --}}

    <div class="control-card">

        <div class="control-header">

            <div>

                <h3 class="control-title">
                    <i class="fas fa-trophy text-warning mr-2"></i>
                    Current Winners
                </h3>

                <p class="control-subtitle">
                    Winners already assigned to this draw.
                </p>

            </div>

            <span class="badge badge-warning">
                {{ $draw->winners->count() }}
            </span>

        </div>


        <div class="control-body">

            @forelse(
                $draw->winners->sortBy('prize_position')
                as $winner
            )

                <div
                    class="d-flex align-items-center justify-content-between p-3 mb-2"
                    style="background:#fffaf0;border:1px solid #f0d995;border-radius:12px;"
                >

                    <div>

                        <strong>
                            {{ $winner->customer->full_name }}
                        </strong>

                        <div class="text-muted small">

                            {{ $winner->customer->customer_code }}

                            •

                            {{ $winner->ticket->ticket_number }}

                        </div>

                    </div>


                    <div class="text-right">

                        <span class="winner-badge">
                            <i class="fas fa-trophy"></i>
                            Position {{ $winner->prize_position }}
                        </span>

                        <div class="font-weight-bold mt-1">

                            Rs {{ number_format($winner->winning_amount, 2) }}

                        </div>

                    </div>

                </div>

            @empty

                <div class="text-center text-muted py-4">

                    <i class="fas fa-trophy fa-2x mb-2"></i>

                    <div class="font-weight-bold">
                        No winners assigned yet.
                    </div>

                </div>

            @endforelse

        </div>

    </div>

</div>


{{-- =========================================================
     GUIDE MODAL
========================================================== --}}

<div
    class="modal fade"
    id="drawGuideModal"
    tabindex="-1"
    role="dialog"
>

    <div
        class="modal-dialog modal-lg modal-dialog-centered"
        role="document"
    >

        <div class="modal-content">

            <div
                class="modal-header text-white"
                style="background:linear-gradient(135deg,#0b2537,#0f766e);"
            >

                <div>

                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-book-open mr-2"></i>
                        Draw Guide
                    </h5>

                    <small>
                        English + हिन्दी
                    </small>

                </div>

                <button
                    type="button"
                    class="close text-white"
                    data-dismiss="modal"
                >
                    <span>&times;</span>
                </button>

            </div>


            <div class="modal-body">

                <div class="guide-step">

                    <div class="guide-number">1</div>

                    <div>

                        <strong>
                            Select Draw / Draw चुनें
                        </strong>

                        <p>
                            Open the Daily, Weekly, Monthly or Festival draw
                            that is scheduled for the current date.
                        </p>

                        <p>
                            जिस Daily, Weekly, Monthly या Festival draw की
                            तारीख आ गई है, उसे open करें।
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">2</div>

                    <div>

                        <strong>
                            One Customer = One Winner / एक Customer = एक Winner
                        </strong>

                        <p>
                            If a customer purchased 5 tickets, the customer
                            is shown only once. The system can select one
                            active ticket from those tickets.
                        </p>

                        <p>
                            अगर किसी customer ने 5 tickets खरीदे हैं तो उसे
                            केवल एक बार winner बनाया जाएगा और उसके active
                            tickets में से एक ticket select होगा।
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">3</div>

                    <div>

                        <strong>
                            Choose Distribution / Distribution चुनें
                        </strong>

                        <p>
                            Use "Everyone → 1st" when all selected customers
                            should receive position 1.
                        </p>

                        <p>
                            "Distribute 1–10" चुनने पर system customers को
                            position 1 से 10 तक बराबर distribute करेगा।
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">4</div>

                    <div>

                        <strong>
                            Change Any Customer / Customer की Position बदलें
                        </strong>

                        <p>
                            You can change any customer's position manually
                            from the dropdown. The prize amount will update
                            automatically.
                        </p>

                        <p>
                            किसी भी customer की position dropdown से बदल सकते हैं।
                            Amount अपने आप selected position के अनुसार बदल जाएगा।
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">5</div>

                    <div>

                        <strong>
                            Save & Complete / Save और Complete करें
                        </strong>

                        <p>
                            Review the complete list first, save the winner
                            positions and then complete the draw.
                        </p>

                        <p>
                            पूरी list check करने के बाद winner positions save
                            करें और फिर draw complete करें।
                        </p>

                    </div>

                </div>


                <div
                    class="alert alert-warning mb-0"
                    style="font-size:10px;"
                >

                    <i class="fas fa-exclamation-triangle mr-1"></i>

                    Once a draw is completed, avoid changing winners unless
                    your business rules specifically allow it.

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     CUSTOMER GUIDE MODAL
========================================================== --}}

<div
    class="modal fade"
    id="customerGuideModal"
    tabindex="-1"
    role="dialog"
>

    <div
        class="modal-dialog modal-md modal-dialog-centered"
        role="document"
    >

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Customer Position Guide
                </h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                >
                    <span>&times;</span>
                </button>

            </div>


            <div class="modal-body">

                <p class="small">

                    <strong>Example:</strong>

                    If there are 50 customers and you select
                    <strong>Distribute 1–10</strong>, the system will try
                    to distribute them equally:

                </p>


                <div class="row">

                    @for($i = 1; $i <= 10; $i++)

                        <div class="col-6 mb-2">

                            <div
                                class="p-2"
                                style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;"
                            >

                                <strong style="font-size:10px;">
                                    Position {{ $i }}
                                </strong>

                                <div
                                    class="text-muted"
                                    style="font-size:9px;"
                                >
                                    Approximately 5 customers
                                </div>

                            </div>

                        </div>

                    @endfor

                </div>


                <hr>


                <div class="small">

                    <strong>Hindi:</strong>

                    50 customers होने पर 1 से 10 position में लगभग
                    5-5 customers जाएंगे। अगर किसी customer को
                    Position 7 से Position 2 में करना है तो केवल dropdown
                    बदलें। Winning amount Position 2 के हिसाब से automatically
                    update होगा।

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     COMPLETE DRAW CONFIRMATION
========================================================== --}}

<div
    class="modal fade"
    id="completeDrawModal"
    tabindex="-1"
    role="dialog"
>

    <div
        class="modal-dialog modal-sm modal-dialog-centered"
        role="document"
    >

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Complete Draw?
                </h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                >
                    <span>&times;</span>
                </button>

            </div>


            <div class="modal-body text-center">

                <div
                    style="
                        width:55px;
                        height:55px;
                        margin:0 auto 13px;
                        border-radius:16px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        background:#ecfdf5;
                        color:#16a34a;
                        font-size:22px;
                    "
                >
                    <i class="fas fa-check-double"></i>
                </div>


                <h5 class="font-weight-bold">
                    Are you sure?
                </h5>

                <p
                    class="text-muted"
                    style="font-size:10px;line-height:1.6;"
                >
                    Please review all customer positions and winning amounts
                    before completing this draw.
                </p>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-dismiss="modal"
                >
                    Review Again
                </button>


                <form
                    method="POST"
                    action="{{ route('admin.draws.complete', $draw) }}"
                    class="m-0"
                >

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        <i class="fas fa-check mr-1"></i>
                        Complete Draw
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Prize Data
    |--------------------------------------------------------------------------
    */

    const prizeAmounts = @json($draw->prize_snapshot ?? []);


    /*
    |--------------------------------------------------------------------------
    | Update Amount
    |--------------------------------------------------------------------------
    */

    function updatePositionAmount(select) {

        const row = select.closest('tr');

        if (!row) {
            return;
        }

        const preview = row.querySelector(
            '[data-amount-preview]'
        );

        if (!preview) {
            return;
        }

        const selectedOption =
            select.options[select.selectedIndex];

        if (
            !selectedOption ||
            !selectedOption.value
        ) {
            preview.textContent = '—';
            return;
        }

        const amount =
            selectedOption.getAttribute('data-amount');

        if (!amount) {
            preview.textContent = '—';
            return;
        }

        preview.textContent =
            'Rs ' +
            Number(amount).toLocaleString(
                'en-IN',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Position Change
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.winner-position')
        .forEach(function(select) {

            select.addEventListener(
                'change',
                function() {

                    updatePositionAmount(this);

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Auto Distribution
    |--------------------------------------------------------------------------
    |
    | Front-end preview:
    | 1st mode = everyone position 1
    | balanced = 1..10 equally
    | custom = clear selections
    |
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'autoDistributeBtn'
    ).addEventListener(
        'click',
        function() {

            const mode =
                document.querySelector(
                    '.distribution-mode:checked'
                )?.value;

            const selects =
                Array.from(
                    document.querySelectorAll(
                        '.winner-position'
                    )
                );

            if (!selects.length) {
                return;
            }


            if (mode === 'first') {

                selects.forEach(function(select) {

                    if (
                        select.querySelector(
                            'option[value="1"]'
                        )
                    ) {
                        select.value = '1';
                        updatePositionAmount(select);
                    }

                });

                return;
            }


            if (mode === 'custom') {

                selects.forEach(function(select) {

                    select.value = '';

                    updatePositionAmount(select);

                });

                return;
            }


            /*
             * Balanced distribution.
             *
             * Example:
             * 50 customers / 10 positions
             * = 5 customers per position.
             *
             * If number isn't perfectly divisible,
             * first positions receive one extra customer.
             */

            selects.forEach(function(select, index) {

                const position =
                    (index % 10) + 1;

                if (
                    select.querySelector(
                        'option[value="' + position + '"]'
                    )
                ) {

                    select.value =
                        String(position);

                    updatePositionAmount(select);

                }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Reset Positions
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'resetPositionsBtn'
    ).addEventListener(
        'click',
        function() {

            document.querySelectorAll(
                '.winner-position'
            ).forEach(function(select) {

                select.value = '';

                updatePositionAmount(select);

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Submit Confirmation
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'winnerAssignmentForm'
    ).addEventListener(
        'submit',
        function(event) {

            const selected =
                document.querySelectorAll(
                    '.winner-position'
                );

            let count = 0;

            selected.forEach(function(select) {

                if (select.value) {
                    count++;
                }

            });

            if (!count) {

                event.preventDefault();

                alert(
                    'Please assign at least one customer to a winning position.'
                );

                return false;
            }

        }
    );

</script>

@endsection
```
