```blade
@extends('admin.layouts.app')

@section('title', 'Draw Management')
@section('page-title', 'Draw Management')

@section('content')

<style>
    .draw-page {
        --draw-primary: #0f766e;
        --draw-dark: #102a43;
        --draw-soft: #f5f8fb;
        --draw-line: #e2e8f0;
        --draw-muted: #64748b;
        --draw-gold: #d49a22;
        --draw-green: #16a34a;
        --draw-red: #dc2626;
    }

    .draw-page * {
        box-sizing: border-box;
    }

    .draw-hero {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        padding: 24px;
        color: #fff;
        background:
            radial-gradient(circle at 90% 10%, rgba(255,255,255,.13), transparent 28%),
            linear-gradient(135deg, #0b2537, #0f766e 65%, #c28a19);
        box-shadow: 0 18px 45px rgba(15,23,42,.12);
    }

    .draw-hero::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        right: -70px;
        bottom: -90px;
        border-radius: 50%;
        border: 25px solid rgba(255,255,255,.06);
    }

    .draw-hero-content {
        position: relative;
        z-index: 2;
    }

    .draw-hero h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
    }

    .draw-hero p {
        margin: 6px 0 0;
        color: #d8fffa;
        font-size: 12px;
        max-width: 650px;
    }

    .hero-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .hero-btn {
        border: 1px solid rgba(255,255,255,.28);
        background: rgba(255,255,255,.12);
        color: #fff !important;
        border-radius: 10px;
        padding: 9px 13px;
        font-size: 11px;
        font-weight: 800;
    }

    .hero-btn:hover {
        background: rgba(255,255,255,.2);
    }

    .filter-card {
        margin-top: 18px;
        padding: 15px;
        background: #fff;
        border: 1px solid var(--draw-line);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }

    .type-tabs {
        display: flex;
        gap: 7px;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .type-tabs a {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 13px;
        border: 1px solid var(--draw-line);
        border-radius: 10px;
        color: #475569;
        background: #fff;
        font-size: 11px;
        font-weight: 800;
    }

    .type-tabs a:hover {
        border-color: #b7dcd7;
        color: var(--draw-primary);
    }

    .type-tabs a.active {
        background: var(--draw-primary);
        color: #fff;
        border-color: var(--draw-primary);
        box-shadow: 0 6px 15px rgba(15,118,110,.18);
    }

    .stats-grid {
        margin-top: 18px;
    }

    .stat-card {
        height: 100%;
        padding: 17px;
        border: 1px solid var(--draw-line);
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 8px 25px rgba(15,23,42,.045);
    }

    .stat-icon {
        width: 39px;
        height: 39px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #ecfdf5;
        color: var(--draw-primary);
        margin-bottom: 11px;
    }

    .stat-number {
        font-size: 24px;
        line-height: 1;
        font-weight: 900;
    }

    .stat-label {
        margin-top: 5px;
        color: var(--draw-muted);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .draw-list-card {
        margin-top: 18px;
        overflow: hidden;
        border: 1px solid var(--draw-line);
        border-radius: 17px;
        background: #fff;
        box-shadow: 0 12px 35px rgba(15,23,42,.06);
    }

    .draw-list-header {
        padding: 17px 20px;
        border-bottom: 1px solid var(--draw-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .draw-list-title {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
    }

    .draw-list-subtitle {
        margin: 3px 0 0;
        color: var(--draw-muted);
        font-size: 11px;
    }

    .draw-table {
        margin: 0;
    }

    .draw-table thead th {
        background: #f8fafc;
        border-top: 0;
        border-bottom: 1px solid var(--draw-line);
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .draw-table td {
        vertical-align: middle;
        border-color: #edf1f5;
        font-size: 12px;
    }

    .draw-number {
        color: #0f172a;
        font-weight: 900;
    }

    .draw-type {
        font-weight: 800;
    }

    .draw-date {
        font-weight: 700;
    }

    .draw-time {
        display: block;
        margin-top: 2px;
        color: #94a3b8;
        font-size: 10px;
    }

    .draw-count {
        font-weight: 900;
    }

    .mini-stats {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .mini-stat {
        padding: 4px 7px;
        border-radius: 7px;
        background: #f1f5f9;
        color: #475569;
        font-size: 9px;
        font-weight: 800;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-scheduled {
        color: #075985;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
    }

    .status-progress {
        color: #92400e;
        background: #fff7ed;
        border: 1px solid #fed7aa;
    }

    .status-completed {
        color: #166534;
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
    }

    .status-cancelled {
        color: #be123c;
        background: #fff1f2;
        border: 1px solid #fecdd3;
    }

    .open-draw-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 9px;
        padding: 8px 11px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #0f766e;
        font-size: 10px;
        font-weight: 900;
    }

    .open-draw-btn:hover {
        color: #fff;
        background: #0f766e;
        border-color: #0f766e;
    }

    .guide-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
    }

    .guide-modal .modal-header {
        border: 0;
        color: #fff;
        background: linear-gradient(135deg, #0b2537, #0f766e);
    }

    .guide-step {
        display: flex;
        gap: 12px;
        padding: 13px;
        border: 1px solid #e2e8f0;
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
        display: block;
        font-size: 12px;
    }

    .guide-step p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 10px;
        line-height: 1.6;
    }

    .guide-lang {
        padding: 12px;
        border-radius: 12px;
        background: #f8fafc;
        margin-top: 12px;
    }

    .guide-lang strong {
        font-size: 11px;
    }

    .guide-lang p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 10px;
        line-height: 1.7;
    }

    .pagination {
        margin: 0;
    }

    @media(max-width:767px) {
        .draw-hero {
            padding: 19px;
        }

        .draw-hero h2 {
            font-size: 20px;
        }

        .hero-actions {
            margin-top: 15px;
        }

        .hero-btn {
            flex: 1;
            text-align: center;
        }

        .draw-list-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>


<div class="draw-page">

    {{-- HERO --}}
    <div class="draw-hero">

        <div class="draw-hero-content">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <h2>
                        <i class="fas fa-dice mr-2"></i>
                        Draw Control Room
                    </h2>

                    <p>
                        Manage Daily, Weekly, Monthly, Festival and other
                        ticket draws from one simple control panel.
                        See exactly which draw is due and manage winners easily.
                    </p>

                </div>

                <div class="col-lg-4">

                    <div class="hero-actions justify-content-lg-end">

                        <button
                            type="button"
                            class="hero-btn"
                            data-toggle="modal"
                            data-target="#drawGuideModal"
                        >
                            <i class="fas fa-question-circle mr-1"></i>
                            How Draw Works
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- FILTER --}}
    <div class="filter-card">

        <div class="type-tabs">

            <a
                href="{{ route('admin.draws.index') }}"
                class="{{ request('frequency') ? '' : 'active' }}"
            >
                <i class="fas fa-layer-group"></i>
                All Draws
            </a>

            @foreach(['daily','weekly','monthly','festival'] as $freq)

                <a
                    href="{{ route('admin.draws.index', ['frequency' => $freq]) }}"
                    class="{{ request('frequency') === $freq ? 'active' : '' }}"
                >
                    @if($freq === 'daily')
                        <i class="fas fa-sun"></i>
                    @elseif($freq === 'weekly')
                        <i class="fas fa-calendar-week"></i>
                    @elseif($freq === 'monthly')
                        <i class="fas fa-calendar-alt"></i>
                    @else
                        <i class="fas fa-star"></i>
                    @endif

                    {{ ucfirst($freq) }}
                </a>

            @endforeach

        </div>


        <form
            method="GET"
            action="{{ route('admin.draws.index') }}"
            class="row mt-3"
        >

            @if(request('frequency'))
                <input
                    type="hidden"
                    name="frequency"
                    value="{{ request('frequency') }}"
                >
            @endif

            <div class="col-md-3 mb-2">

                <label class="small font-weight-bold">
                    Draw Date
                </label>

                <input
                    type="date"
                    name="date"
                    value="{{ request('date') }}"
                    class="form-control"
                >

            </div>


            <div class="col-md-3 mb-2">

                <label class="small font-weight-bold">
                    Draw Status
                </label>

                <select
                    name="status"
                    class="form-control"
                >

                    <option value="">
                        All Status
                    </option>

                    @foreach([
                        'scheduled',
                        'in_progress',
                        'completed',
                        'cancelled'
                    ] as $status)

                        <option
                            value="{{ $status }}"
                            @selected(request('status') === $status)
                        >
                            {{ ucfirst(str_replace('_',' ', $status)) }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="col-md-3 mb-2 d-flex align-items-end">

                <button class="btn btn-primary btn-block">
                    <i class="fas fa-filter mr-1"></i>
                    Apply Filter
                </button>

            </div>


            <div class="col-md-3 mb-2 d-flex align-items-end">

                <a
                    href="{{ route('admin.draws.index') }}"
                    class="btn btn-light btn-block"
                >
                    <i class="fas fa-redo mr-1"></i>
                    Reset
                </a>

            </div>

        </form>

    </div>


    {{-- STATS --}}
    <div class="row stats-grid">

        @foreach(['daily','weekly','monthly','festival'] as $freq)

            <div class="col-md-3 mb-3">

                <div class="stat-card">

                    <div class="stat-icon">

                        @if($freq === 'daily')
                            <i class="fas fa-sun"></i>
                        @elseif($freq === 'weekly')
                            <i class="fas fa-calendar-week"></i>
                        @elseif($freq === 'monthly')
                            <i class="fas fa-calendar-alt"></i>
                        @else
                            <i class="fas fa-star"></i>
                        @endif

                    </div>

                    <div class="stat-number">
                        {{ $tabStats[$freq]['draws'] ?? 0 }}
                    </div>

                    <div class="stat-label">
                        {{ ucfirst($freq) }} Draws
                    </div>

                    <div class="mini-stats mt-2">

                        <span class="mini-stat">
                            {{ $tabStats[$freq]['customers'] ?? 0 }}
                            Customers
                        </span>

                        <span class="mini-stat">
                            {{ $tabStats[$freq]['tickets'] ?? 0 }}
                            Tickets
                        </span>

                        <span class="mini-stat">
                            {{ $tabStats[$freq]['today'] ?? 0 }}
                            Today
                        </span>

                    </div>

                </div>

            </div>

        @endforeach

    </div>


    {{-- DRAW LIST --}}
    <div class="draw-list-card">

        <div class="draw-list-header">

            <div>

                <h3 class="draw-list-title">
                    Available Draws
                </h3>

                <div class="draw-list-subtitle">
                    Select a draw to manage customers, positions and winners.
                </div>

            </div>

            <span class="badge badge-light">
                {{ $draws->total() }} Total
            </span>

        </div>


        <div class="table-responsive">

            <table class="table draw-table datatable">

                <thead>

                    <tr>

                        <th>Draw</th>
                        <th>Ticket Type</th>
                        <th>Frequency</th>
                        <th>Draw Schedule</th>
                        <th>Status</th>
                        <th>Customers</th>
                        <th>Tickets</th>
                        <th>Winners</th>
                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                @foreach($draws as $draw)

                    @php
                        $status = strtolower($draw->status ?? 'scheduled');

                        $statusClass = match($status) {
                            'scheduled'   => 'status-scheduled',
                            'in_progress' => 'status-progress',
                            'completed'   => 'status-completed',
                            'cancelled'   => 'status-cancelled',
                            default       => 'status-scheduled'
                        };
                    @endphp

                    <tr>

                        {{-- 1. DRAW --}}
                        <td>
                            <div class="draw-number">
                                {{ $draw->draw_number }}
                            </div>

                            <small class="text-muted">
                                #{{ $draw->id }}
                            </small>
                        </td>


                        {{-- 2. TICKET TYPE --}}
                        <td>
                            <div class="draw-type">
                                {{ $draw->ticketType->name ?? 'Ticket' }}
                            </div>
                        </td>


                        {{-- 3. FREQUENCY --}}
                        <td>
                            <span class="badge badge-light">
                                {{ ucfirst($draw->ticketType->frequency ?? 'other') }}
                            </span>
                        </td>


                        {{-- 4. DATE / TIME --}}
                        <td>

                            <div class="draw-date">
                                {{ optional($draw->draw_date)->format('d M Y') }}
                            </div>

                            <span class="draw-time">
                                <i class="far fa-clock mr-1"></i>
                                {{ $draw->draw_time ?: '23:59' }}
                            </span>

                        </td>


                        {{-- 5. STATUS --}}
                        <td>

                            <span class="status-badge {{ $statusClass }}">

                                @if($status === 'completed')

                                    <i class="fas fa-check-circle"></i>

                                @elseif($status === 'in_progress')

                                    <i class="fas fa-spinner"></i>

                                @elseif($status === 'cancelled')

                                    <i class="fas fa-times-circle"></i>

                                @else

                                    <i class="fas fa-clock"></i>

                                @endif

                                {{ ucfirst(str_replace('_', ' ', $status)) }}

                            </span>

                        </td>


                        {{-- 6. CUSTOMERS --}}
                        <td>
                            <div class="draw-count">
                                {{ $draw->total_customers ?? 0 }}
                            </div>
                        </td>


                        {{-- 7. TICKETS --}}
                        <td>
                            <div class="draw-count">
                                {{ $draw->total_tickets ?? 0 }}
                            </div>
                        </td>


                        {{-- 8. WINNERS --}}
                        <td>
                            <div class="draw-count">
                                {{ $draw->winners->count() }}
                            </div>
                        </td>


                        {{-- 9. ACTION --}}
                        <td>

                            <a
                                href="{{ route('admin.draws.show', $draw) }}"
                                class="open-draw-btn"
                            >
                                <i class="fas fa-sliders-h"></i>
                                Manage Draw
                            </a>

                        </td>

                    </tr>

                @endforeach

                </tbody>
                


            </table>
            @if($draws->count() === 0)

            <div class="text-center py-5 text-muted">

                <div style="
                    width:55px;
                    height:55px;
                    margin:0 auto 12px;
                    border-radius:16px;
                    background:#f1f5f9;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    color:#94a3b8;
                ">
                    <i class="fas fa-dice-d20 fa-lg"></i>
                </div>

                <strong class="d-block text-dark">
                    No draws found
                </strong>

                <small>
                    Try changing the date, frequency or status filter.
                </small>

            </div>

        @endif
        </div>


        @if($draws->hasPages())

            <div class="p-3 border-top">

                {{ $draws->withQueryString()->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     GUIDE MODAL
========================================================= --}}

<div
    class="modal fade guide-modal"
    id="drawGuideModal"
    tabindex="-1"
    role="dialog"
>

    <div
        class="modal-dialog modal-lg modal-dialog-centered"
        role="document"
    >

        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-book-open mr-2"></i>
                        Draw Management Guide
                    </h5>

                    <small>
                        Simple step-by-step instructions
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
                            Select the correct Draw
                        </strong>

                        <p>
                            First identify whether the draw is Daily,
                            Weekly, Monthly, Festival or another ticket type.
                            Open the draw that is scheduled for today.
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">2</div>

                    <div>

                        <strong>
                            Check all participating customers
                        </strong>

                        <p>
                            The draw screen will show all customers who purchased
                            tickets for this particular draw. If one customer
                            purchased 5 tickets, the customer is still treated
                            as one winner candidate.
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">3</div>

                    <div>

                        <strong>
                            Select Winner Distribution
                        </strong>

                        <p>
                            You can distribute everyone into 1st position,
                            or distribute customers across positions 1 to 10.
                            The system automatically calculates the winning
                            amount from the selected position.
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">4</div>

                    <div>

                        <strong>
                            Review & Adjust
                        </strong>

                        <p>
                            After automatic distribution, you can change any
                            customer's position manually. The winning amount
                            updates according to the new position.
                        </p>

                    </div>

                </div>


                <div class="guide-step">

                    <div class="guide-number">5</div>

                    <div>

                        <strong>
                            Save & Complete Draw
                        </strong>

                        <p>
                            Once all customers and positions look correct,
                            save the winners and complete the draw.
                            Completed draws should not be changed casually.
                        </p>

                    </div>

                </div>


                <div class="guide-lang">

                    <strong>
                        🇬🇧 English
                    </strong>

                    <p>
                        Choose a draw → review participating customers →
                        distribute positions → adjust individual positions
                        if required → review prize amounts → save winners →
                        complete the draw.
                    </p>

                </div>


                <div class="guide-lang">

                    <strong>
                        🇮🇳 हिन्दी
                    </strong>

                    <p>
                        पहले सही Draw चुनें → सभी participating customers देखें →
                        1 से 10 तक position distribute करें → जरूरत होने पर
                        किसी customer की position बदलें → prize amount check करें →
                        winners save करें → उसके बाद draw complete करें।
                    </p>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-primary"
                    data-dismiss="modal"
                >
                    Got it
                </button>

            </div>

        </div>

    </div>

</div>

@endsection
```
