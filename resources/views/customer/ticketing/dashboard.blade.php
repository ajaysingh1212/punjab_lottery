 
@php
    /*
    |--------------------------------------------------------------------------
    | Dashboard Calculations
    |--------------------------------------------------------------------------
    */

    $winningTicketIds = $customer->winners->pluck('ticket_id')->unique();

    $kycVerified = $customer->withdrawals
        ->whereIn('status', ['approved', 'processing', 'completed'])
        ->isNotEmpty();

    $totalWinningAmount = (float) $customer->winners->sum('winning_amount');

    $paidChargeTotal = (float) $customer->charges
        ->filter(function ($charge) {
            return $charge->payments
                ->whereIn('status', ['approved', 'paid', 'verified'])
                ->isNotEmpty();
        })
        ->sum('amount');

    $refundablePaidCharges = (float) $customer->charges
        ->where('is_refundable', true)
        ->filter(function ($charge) {
            return $charge->payments
                ->whereIn('status', ['approved', 'paid', 'verified'])
                ->isNotEmpty();
        })
        ->sum('amount');

    $settlementValue = max(
        0,
        $totalWinningAmount + $refundablePaidCharges
    );

    $completedCharges = $customer->charges->filter(function ($charge) {
        return in_array(strtolower($charge->status), [
            'completed',
            'paid',
            'verified',
            'approved'
        ]);
    });

    $pendingCharges = $customer->charges->filter(function ($charge) {
        return in_array(strtolower($charge->status), [
            'pending',
            'due',
            'unpaid'
        ]);
    });

    $activeDraws = $customer->tickets
        ->filter(function ($ticket) {
            return $ticket->draw &&
                $ticket->draw->draw_date &&
                \Carbon\Carbon::parse(
                    $ticket->draw->draw_date . ' ' .
                    ($ticket->draw->draw_time ?: '23:59:59')
                )->isFuture();
        })
        ->groupBy(function ($ticket) {
            return $ticket->ticket_type_id . '-' . $ticket->draw_id;
        })
        ->count();

    $latestWithdrawal = $customer->withdrawals
        ->sortByDesc('created_at')
        ->first();
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $settings['title'] ?? 'Customer Dashboard' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}"
    >

    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #115e59;
            --primary-light: #ccfbf1;
            --secondary: #164e63;
            --gold: #d49a22;
            --gold-light: #fff7df;
            --success: #16a34a;
            --success-light: #ecfdf3;
            --danger: #dc2626;
            --danger-light: #fff1f2;
            --warning: #d97706;
            --warning-light: #fff7ed;
            --info: #0284c7;
            --info-light: #eff6ff;

            --ink: #172033;
            --muted: #64748b;
            --soft: #f6f8fb;
            --line: #e5eaf0;
            --white: #ffffff;

            --radius: 18px;
            --shadow: 0 12px 40px rgba(15, 23, 42, .07);
            --shadow-hover: 0 18px 50px rgba(15, 23, 42, .12);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: #f4f7fb;
            color: var(--ink);
            font-family: "Inter", Arial, sans-serif;
            font-size: 14px;
        }

        a {
            text-decoration: none !important;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        /* =========================================================
           TOP HEADER
        ========================================================= */

        .dashboard-header {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 10% 20%,
                    rgba(255,255,255,.12),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 85% 0%,
                    rgba(255,255,255,.12),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #062e49 0%,
                    #0f766e 55%,
                    #c28a19 125%
                );

            color: #fff;
            padding: 28px 0 90px;
        }

        .dashboard-header::before {
            content: "";
            position: absolute;
            inset: 0;

            background-image:
                linear-gradient(
                    rgba(255,255,255,.06) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255,255,255,.06) 1px,
                    transparent 1px
                );

            background-size: 42px 42px;
            pointer-events: none;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand-left {
            display: flex;
            align-items: center;
            min-width: 0;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            flex: 0 0 64px;

            border-radius: 17px;
            background: #fff;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            box-shadow:
                0 12px 35px rgba(0,0,0,.20);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 7px;
        }

        .brand-placeholder {
            color: var(--primary);
            font-size: 22px;
            font-weight: 900;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 6px 11px;
            border-radius: 999px;

            color: #fff;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);

            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .header-title {
            margin: 10px 0 4px;
            font-size: 30px;
            font-weight: 900;
            line-height: 1.15;
        }

        .header-subtitle {
            max-width: 680px;
            margin: 0;
            color: #dffdfa;
            font-size: 13px;
            line-height: 1.6;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-btn {
            border: 1px solid rgba(255,255,255,.30);
            background: rgba(255,255,255,.12);
            color: #fff !important;

            padding: 9px 13px;
            border-radius: 11px;

            font-weight: 800;
            font-size: 12px;

            transition: .2s ease;
        }

        .header-btn:hover {
            background: rgba(255,255,255,.22);
            transform: translateY(-1px);
        }

        .customer-banner {
            margin-top: 24px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;

            padding: 17px 20px;

            border-radius: 16px;

            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.22);

            backdrop-filter: blur(10px);
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .customer-avatar {
            width: 46px;
            height: 46px;

            border-radius: 50%;

            background: #fff;
            color: var(--primary);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 18px;
            font-weight: 900;
        }

        .customer-name {
            font-size: 16px;
            font-weight: 900;
        }

        .customer-code {
            margin-top: 2px;
            color: #d5fffa;
            font-family: Consolas, monospace;
            font-size: 12px;
        }

        .header-ticket-count {
            display: flex;
            align-items: center;
            gap: 9px;

            padding: 9px 13px;
            border-radius: 12px;

            background: rgba(0,0,0,.14);
            color: #fff;

            font-size: 12px;
            font-weight: 800;
        }

        /* =========================================================
           MAIN
        ========================================================= */

        .dashboard-main {
            position: relative;
            z-index: 5;
            margin-top: -54px;
        }

        .alert {
            border-radius: 14px;
            border: 0;
            box-shadow: var(--shadow);
        }

        /* =========================================================
           WALLET HERO
        ========================================================= */

        .wallet-hero {
            position: relative;
            overflow: hidden;

            border-radius: 22px;

            padding: 25px;

            color: #fff;

            background:
                radial-gradient(
                    circle at 95% 10%,
                    rgba(255,255,255,.14),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #0b2537,
                    #0f766e
                );

            box-shadow:
                0 22px 55px rgba(15, 118, 110, .18);
        }

        .wallet-hero::after {
            content: "";
            position: absolute;

            width: 220px;
            height: 220px;

            right: -70px;
            bottom: -100px;

            border-radius: 50%;

            border: 30px solid rgba(255,255,255,.05);
        }

        .wallet-content {
            position: relative;
            z-index: 2;
        }

        .wallet-label {
            color: #b8e8e3;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .7px;
        }

        .wallet-amount {
            margin: 5px 0 2px;

            font-size: 35px;
            line-height: 1;
            font-weight: 900;
        }

        .wallet-description {
            margin: 8px 0 0;

            color: #d7fffa;
            font-size: 12px;
        }

        .wallet-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            margin-top: 16px;

            padding: 10px 15px;

            border-radius: 11px;

            background: #fff;
            color: var(--primary);

            font-size: 12px;
            font-weight: 900;

            box-shadow: 0 8px 25px rgba(0,0,0,.13);
        }

        .wallet-action:hover {
            color: var(--primary-dark);
        }

        .wallet-mini {
            padding: 13px 15px;

            border-radius: 14px;

            background: rgba(255,255,255,.09);
            border: 1px solid rgba(255,255,255,.13);
        }

        .wallet-mini-label {
            color: #b8d8d5;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .wallet-mini-value {
            margin-top: 5px;

            font-size: 18px;
            font-weight: 900;
        }

        /* =========================================================
           STATS
        ========================================================= */

        .stat-card {
            height: 100%;

            padding: 19px;

            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);

            box-shadow: var(--shadow);

            transition: .2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            width: 43px;
            height: 43px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: var(--primary-light);
            color: var(--primary);

            margin-bottom: 13px;

            font-size: 17px;
        }

        .stat-icon.gold {
            background: var(--gold-light);
            color: var(--gold);
        }

        .stat-icon.blue {
            background: var(--info-light);
            color: var(--info);
        }

        .stat-icon.green {
            background: var(--success-light);
            color: var(--success);
        }

        .stat-value {
            font-size: 27px;
            font-weight: 900;
            line-height: 1;
        }

        .stat-label {
            margin-top: 7px;

            color: var(--muted);

            font-size: 11px;
            font-weight: 800;

            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* =========================================================
           TABS
        ========================================================= */

        .dashboard-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;

            margin: 24px 0 18px;

            padding: 7px;

            background: #fff;
            border: 1px solid var(--line);

            border-radius: 16px;

            box-shadow: var(--shadow);
        }

        .dashboard-tabs .nav-link {
            border: 0;
            border-radius: 11px;

            padding: 10px 15px;

            color: #526175;
            background: transparent;

            font-size: 12px;
            font-weight: 800;

            transition: .2s ease;
        }

        .dashboard-tabs .nav-link i {
            margin-right: 6px;
        }

        .dashboard-tabs .nav-link:hover {
            background: #f3f7f8;
            color: var(--primary);
        }

        .dashboard-tabs .nav-link.active {
            color: #fff;
            background: var(--primary);

            box-shadow: 0 6px 15px rgba(15,118,110,.20);
        }

        .tab-count {
            display: inline-flex;

            min-width: 20px;
            height: 20px;

            align-items: center;
            justify-content: center;

            margin-left: 4px;

            border-radius: 999px;

            background: rgba(255,255,255,.18);
            font-size: 10px;
        }

        .nav-link:not(.active) .tab-count {
            background: #eef2f6;
            color: var(--muted);
        }

        /* =========================================================
           SECTION
        ========================================================= */

        .section-card {
            margin-bottom: 20px;

            background: #fff;
            border: 1px solid var(--line);

            border-radius: var(--radius);

            box-shadow: var(--shadow);

            overflow: hidden;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;

            padding: 18px 20px;

            border-bottom: 1px solid var(--line);
        }

        .section-title {
            margin: 0;

            font-size: 16px;
            font-weight: 900;
        }

        .section-subtitle {
            margin: 3px 0 0;

            color: var(--muted);
            font-size: 11px;
        }

        .section-body {
            padding: 20px;
        }

        /* =========================================================
           WINNER BANNER
        ========================================================= */

        .winner-banner {
            position: relative;
            overflow: hidden;

            margin-bottom: 20px;

            padding: 20px;

            border-radius: 18px;

            background:
                radial-gradient(
                    circle at 90% 20%,
                    rgba(214,157,34,.18),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fffaf0,
                    #ffffff
                );

            border: 1px solid #f1d98f;
        }

        .winner-banner::after {
            content: "★";

            position: absolute;

            right: 20px;
            top: 10px;

            font-size: 70px;

            color: rgba(214,157,34,.08);
        }

        .winner-content {
            position: relative;
            z-index: 2;

            display: flex;
            align-items: center;
            gap: 15px;
        }

        .winner-icon {
            width: 50px;
            height: 50px;

            flex: 0 0 50px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 15px;

            background: #fff0b8;
            color: #a66d00;

            font-size: 20px;
        }

        .winner-title {
            margin: 0;

            font-size: 17px;
            font-weight: 900;
            color: #6e4b00;
        }

        .winner-text {
            margin: 4px 0 0;

            color: #806b3b;
            font-size: 12px;
        }

        /* =========================================================
           TICKET CARDS
        ========================================================= */

        .ticket-card {
            position: relative;
            overflow: hidden;

            height: 100%;

            padding: 18px;

            background: #fff;

            border: 1px solid var(--line);
            border-left: 5px solid #cbd5e1;

            border-radius: 16px;

            box-shadow: 0 7px 22px rgba(15,23,42,.045);

            transition: .2s ease;
        }

        .ticket-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .ticket-card.winner {
            border-left-color: var(--gold);

            background:
                linear-gradient(
                    180deg,
                    #fffaf0 0%,
                    #ffffff 100%
                );

            border-color: #f0db9d;
        }

        .ticket-card.completed {
            border-left-color: #16a34a;
        }

        .ticket-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .ticket-type {
            font-size: 15px;
            font-weight: 900;
        }

        .ticket-draw {
            margin-top: 3px;

            color: var(--muted);
            font-size: 11px;
        }

        .ticket-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            white-space: nowrap;

            padding: 6px 9px;

            border-radius: 999px;

            font-size: 10px;
            font-weight: 900;
        }

        .ticket-status.winner {
            color: #8a5b00;
            background: #fff4c7;
            border: 1px solid #eed27b;
        }

        .ticket-status.active {
            color: #075985;
            background: #e8f7ff;
            border: 1px solid #b9e4fa;
        }

        .ticket-status.completed {
            color: #166534;
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
        }

        .ticket-number-box {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-top: 17px;

            padding: 12px;

            border-radius: 12px;

            background: #f8fafc;
            border: 1px dashed #d7e0e9;
        }

        .ticket-number-label {
            color: var(--muted);
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .ticket-number {
            margin-top: 3px;

            font-family: Consolas, monospace;

            font-size: 13px;
            font-weight: 900;
        }

        .countdown {
            color: var(--danger);

            font-size: 11px;
            font-weight: 900;

            white-space: nowrap;
        }

        .blink {
            animation: pulse 1.15s infinite;
        }

        @keyframes pulse {
            50% {
                opacity: .45;
            }
        }

        .winner-prize {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-top: 12px;

            padding-top: 12px;

            border-top: 1px solid #f0dfb0;
        }

        .winner-prize small {
            color: #806b3b;
            font-size: 10px;
            font-weight: 700;
        }

        .winner-prize strong {
            color: #8a5b00;
            font-size: 17px;
            font-weight: 900;
        }

        /* =========================================================
           WALLET CARDS
        ========================================================= */

        .wallet-stat {
            height: 100%;

            padding: 18px;

            border-radius: 16px;

            background: #fff;
            border: 1px solid var(--line);

            box-shadow: 0 7px 25px rgba(15,23,42,.05);
        }

        .wallet-stat-icon {
            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 11px;

            margin-bottom: 12px;

            background: #eefcf9;
            color: var(--primary);
        }

        .wallet-stat-label {
            color: var(--muted);

            font-size: 10px;
            font-weight: 800;

            text-transform: uppercase;
        }

        .wallet-stat-value {
            margin-top: 5px;

            font-size: 22px;
            font-weight: 900;
        }

        /* =========================================================
           WITHDRAWAL TIMELINE
        ========================================================= */

        .withdrawal-card {
            padding: 18px;

            border: 1px solid var(--line);
            border-radius: 16px;

            background: #fff;
        }

        .withdrawal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 12px;
        }

        .withdrawal-number {
            font-family: Consolas, monospace;
            font-size: 12px;
            font-weight: 900;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            padding: 6px 10px;

            border-radius: 999px;

            font-size: 10px;
            font-weight: 900;
        }

        .status-success {
            color: #166534;
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
        }

        .status-processing {
            color: #075985;
            background: #e8f7ff;
            border: 1px solid #bae6fd;
        }

        .status-pending {
            color: #92400e;
            background: #fff7ed;
            border: 1px solid #fed7aa;
        }

        .status-danger {
            color: #be123c;
            background: #fff1f2;
            border: 1px solid #fecdd3;
        }

        .withdrawal-amount {
            margin-top: 17px;

            font-size: 26px;
            font-weight: 900;
        }

        .withdrawal-meta {
            margin-top: 3px;

            color: var(--muted);
            font-size: 11px;
        }

        .withdrawal-bank {
            margin-top: 15px;

            padding: 13px;

            background: #f8fafc;

            border: 1px solid #e9eef4;
            border-radius: 12px;
        }

        .withdrawal-bank strong {
            font-size: 12px;
        }

        .withdrawal-bank small {
            color: var(--muted);
            font-size: 10px;
        }

        /* =========================================================
           PAYMENT ACCOUNT
        ========================================================= */

        .admin-bank-card {
            height: 100%;

            padding: 17px;

            border-radius: 16px;

            background:
                linear-gradient(
                    135deg,
                    #ffffff,
                    #effcf9
                );

            border: 1px solid #d9eeea;
        }

        .admin-bank-icon {
            width: 43px;
            height: 43px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background: var(--primary);
            color: #fff;
        }

        .bank-title {
            margin-top: 14px;

            font-size: 14px;
            font-weight: 900;
        }

        .bank-muted {
            color: var(--muted);
            font-size: 10px;
        }

        .bank-value {
            margin-top: 4px;

            font-size: 12px;
            font-weight: 800;
        }

        .bank-code {
            font-family: Consolas, monospace;
        }

        .bank-qr {
            width: 75px;
            height: 75px;

            object-fit: contain;

            padding: 4px;

            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
        }

        /* =========================================================
           CHARGES
        ========================================================= */

        .charge-card {
            padding: 17px;

            border: 1px solid var(--line);
            border-radius: 16px;

            background: #fff;

            box-shadow: 0 6px 20px rgba(15,23,42,.04);
        }

        .charge-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 15px;
        }

        .charge-name {
            font-size: 14px;
            font-weight: 900;
        }

        .charge-description {
            margin-top: 4px;

            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .charge-amount {
            white-space: nowrap;

            font-size: 19px;
            font-weight: 900;
        }

        .charge-bank {
            margin-top: 15px;

            padding: 13px;

            border-radius: 13px;

            background: #f7fafc;
            border: 1px solid #e6edf3;
        }

        .charge-bank-title {
            display: flex;
            align-items: center;
            gap: 7px;

            margin-bottom: 8px;

            color: var(--primary);

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;
        }

        .charge-bank-name {
            font-size: 12px;
            font-weight: 900;
        }

        .charge-bank-info {
            margin-top: 3px;

            color: var(--muted);
            font-size: 10px;
        }

        .payment-form {
            margin-top: 15px;
        }

        .payment-form .form-control {
            min-height: 42px;

            border-radius: 10px;
            border-color: #dce4ec;

            font-size: 11px;
        }

        .payment-form input[type="file"] {
            padding: 10px;
        }

        .btn-main {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            min-height: 42px;

            border: 0;
            border-radius: 10px;

            padding: 9px 15px;

            background: var(--primary);
            color: #fff;

            font-size: 11px;
            font-weight: 900;
        }

        .btn-main:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        /* =========================================================
           COMPLETED PAYMENT
        ========================================================= */

        .completed-payment {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;

            padding: 14px 15px;

            background: #f7fcf9;
            border: 1px solid #d9f2e0;

            border-radius: 13px;
        }

        .completed-left {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .completed-icon {
            width: 36px;
            height: 36px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: #dcfce7;
            color: #15803d;
        }

        .completed-name {
            font-size: 12px;
            font-weight: 900;
        }

        .completed-info {
            margin-top: 2px;

            color: var(--muted);
            font-size: 10px;
        }

        /* =========================================================
           EMPTY STATE
        ========================================================= */

        .empty-state {
            padding: 45px 20px;

            text-align: center;

            color: var(--muted);
        }

        .empty-icon {
            width: 55px;
            height: 55px;

            margin: 0 auto 13px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 17px;

            background: #f1f5f9;

            color: #94a3b8;

            font-size: 21px;
        }

        .empty-state strong {
            display: block;

            color: var(--ink);

            font-size: 13px;
        }

        .empty-state span {
            display: block;

            margin-top: 4px;

            font-size: 11px;
        }

        /* =========================================================
           MODAL
        ========================================================= */

        .modal-content {
            border: 0;
            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 30px 90px rgba(15,23,42,.25);
        }

        .modal-header {
            padding: 19px 22px;

            background:
                linear-gradient(
                    135deg,
                    #0b2537,
                    #0f766e
                );

            color: #fff;

            border: 0;
        }

        .modal-title {
            font-size: 16px;
            font-weight: 900;
        }

        .modal-note {
            margin-top: 3px;

            color: #c9f7f2;

            font-size: 10px;
        }

        .modal-header .close {
            color: #fff;
            opacity: .9;
            text-shadow: none;
        }

        .modal-body {
            padding: 22px;
        }

        .form-label-custom {
            margin-bottom: 6px;

            color: #475569;

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;
        }

        .modal .form-control {
            min-height: 43px;

            border-radius: 10px;
            border-color: #dce4ec;

            font-size: 12px;
        }

        .kyc-prize {
            padding: 15px;

            border-radius: 13px;

            background: #fff8e6;
            border: 1px solid #f1dda2;
        }

        .kyc-prize small {
            display: block;

            color: #8a6a25;
            font-size: 10px;
        }

        .kyc-prize strong {
            display: block;

            margin-top: 3px;

            color: #765000;
            font-size: 21px;
            font-weight: 900;
        }

        /* =========================================================
           FOOTER
        ========================================================= */

        .dashboard-footer {
            margin-top: 25px;

            padding: 35px 0;

            background: #0d141b;
            color: #fff;
        }

        .footer-title {
            font-size: 15px;
            font-weight: 900;
        }

        .footer-note {
            margin-top: 7px;

            color: #94a3b8;
            font-size: 11px;
            line-height: 1.7;
        }

        .support-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            margin: 4px;
            padding: 8px 11px;

            border-radius: 999px;

            border: 1px solid #293642;

            color: #dbe4eb;

            font-size: 10px;
            font-weight: 700;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 767px) {

            .dashboard-header {
                padding: 20px 0 75px;
            }

            .brand-row,
            .customer-banner {
                align-items: flex-start;
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
            }

            .header-btn {
                flex: 1;
                text-align: center;
            }

            .header-title {
                font-size: 24px;
            }

            .brand-logo {
                width: 54px;
                height: 54px;
                flex-basis: 54px;
            }

            .dashboard-main {
                margin-top: -42px;
            }

            .wallet-amount {
                font-size: 29px;
            }

            .dashboard-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .dashboard-tabs .nav-link {
                white-space: nowrap;
            }

            .section-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .winner-content {
                align-items: flex-start;
            }

            .completed-payment {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<!-- ============================================================
     HEADER
============================================================= -->

<header class="dashboard-header">

    <div class="container header-content">

        <div class="brand-row">

            <div class="brand-left">

                <div class="brand-logo mr-3">

                    @if(!empty($settings['logo']))
                        <img
                            src="{{ Storage::url('settings/'.$settings['logo']) }}"
                            alt="Logo"
                        >
                    @else
                        <span class="brand-placeholder">PL</span>
                    @endif

                </div>

                <div>

                    <span class="brand-badge">
                        <i class="fas fa-shield-alt"></i>
                        {{ $settings['badge'] ?? 'Secure Customer Portal' }}
                    </span>

                    <h1 class="header-title">
                        {{ $settings['title'] ?? 'Customer Dashboard' }}
                    </h1>

                    <p class="header-subtitle">
                        {{ $settings['subtitle'] ?? 'Manage your tickets, winnings, wallet and payment status from one secure dashboard.' }}
                    </p>

                </div>

            </div>

            <div class="header-actions">

                <a
                    href="{{ route('profile.edit') }}"
                    class="header-btn"
                >
                    <i class="fas fa-user-edit mr-1"></i>
                    Profile
                </a>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="m-0"
                >
                    @csrf

                    <button
                        type="submit"
                        class="header-btn"
                    >
                        <i class="fas fa-sign-out-alt mr-1"></i>
                        Logout
                    </button>

                </form>

            </div>

        </div>


        <div class="customer-banner">

            <div class="customer-info">

                <div class="customer-avatar">
                    {{ strtoupper(substr($customer->full_name ?? 'C', 0, 1)) }}
                </div>

                <div>

                    <div class="customer-name">
                        Welcome, {{ $customer->full_name }}
                    </div>

                    <div class="customer-code">
                        {{ $customer->customer_code }}
                        &nbsp;•&nbsp;
                        {{ $customer->mobile }}
                    </div>

                </div>

            </div>

            <div class="header-ticket-count">

                <i class="fas fa-ticket-alt"></i>

                {{ $customer->tickets->count() }}

                {{ $customer->tickets->count() == 1 ? 'Ticket' : 'Tickets' }}

            </div>

        </div>

    </div>

</header>


<!-- ============================================================
     MAIN
============================================================= -->

<main class="container dashboard-main pb-4">

    @if(session('success'))

        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger">

            <i class="fas fa-exclamation-circle mr-2"></i>

            {{ $errors->first() }}

        </div>

    @endif


    <!-- ========================================================
         WALLET HERO
    ========================================================= -->

    <div class="wallet-hero mb-4">

        <div class="wallet-content">

            <div class="row align-items-center">

                <div class="col-lg-7 mb-3 mb-lg-0">

                    <div class="wallet-label">
                        <i class="fas fa-wallet mr-1"></i>
                        Wallet / Settlement Value
                    </div>

                    <div class="wallet-amount">
                        Rs {{ number_format($settlementValue, 2) }}
                    </div>

                    <p class="wallet-description">
                        Your current winning amount plus eligible refundable
                        paid charges.
                    </p>

                    @if($customer->winners->count())

                        <a
                            href="#tab-wallet"
                            class="wallet-action"
                            data-toggle="tab"
                        >
                            <i class="fas fa-arrow-right"></i>
                            View Wallet & Withdrawals
                        </a>

                    @endif

                </div>

                <div class="col-lg-5">

                    <div class="row">

                        <div class="col-6 mb-2">

                            <div class="wallet-mini">

                                <div class="wallet-mini-label">
                                    Winning
                                </div>

                                <div class="wallet-mini-value">
                                    Rs {{ number_format($totalWinningAmount, 0) }}
                                </div>

                            </div>

                        </div>

                        <div class="col-6 mb-2">

                            <div class="wallet-mini">

                                <div class="wallet-mini-label">
                                    Refundable
                                </div>

                                <div class="wallet-mini-value">
                                    Rs {{ number_format($refundablePaidCharges, 0) }}
                                </div>

                            </div>

                        </div>

                        <div class="col-6">

                            <div class="wallet-mini">

                                <div class="wallet-mini-label">
                                    Withdrawals
                                </div>

                                <div class="wallet-mini-value">
                                    {{ $customer->withdrawals->count() }}
                                </div>

                            </div>

                        </div>

                        <div class="col-6">

                            <div class="wallet-mini">

                                <div class="wallet-mini-label">
                                    KYC
                                </div>

                                <div class="wallet-mini-value">

                                    @if($kycVerified)
                                        Verified
                                    @else
                                        Pending
                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ========================================================
         QUICK STATS
    ========================================================= -->

    <div class="row mb-1">

        <div class="col-6 col-lg-3 mb-3">

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>

                <div class="stat-value">
                    {{ $customer->tickets->count() }}
                </div>

                <div class="stat-label">
                    Total Tickets
                </div>

            </div>

        </div>


        <div class="col-6 col-lg-3 mb-3">

            <div class="stat-card">

                <div class="stat-icon gold">
                    <i class="fas fa-trophy"></i>
                </div>

                <div class="stat-value">
                    {{ $customer->winners->count() }}
                </div>

                <div class="stat-label">
                    Winning Tickets
                </div>

            </div>

        </div>


        <div class="col-6 col-lg-3 mb-3">

            <div class="stat-card">

                <div class="stat-icon blue">
                    <i class="fas fa-bolt"></i>
                </div>

                <div class="stat-value">
                    {{ $activeDraws }}
                </div>

                <div class="stat-label">
                    Active Draws
                </div>

            </div>

        </div>


        <div class="col-6 col-lg-3 mb-3">

            <div class="stat-card">

                <div class="stat-icon green">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <div class="stat-value">
                    {{ number_format($settlementValue, 0) }}
                </div>

                <div class="stat-label">
                    Settlement Value
                </div>

            </div>

        </div>

    </div>


    <!-- ========================================================
         TABS
    ========================================================= -->

    <ul class="nav dashboard-tabs" role="tablist">

        <li class="nav-item">
            <a
                class="nav-link active"
                data-toggle="tab"
                href="#tab-dashboard"
            >
                <i class="fas fa-th-large"></i>
                Overview
            </a>
        </li>

        <li class="nav-item">
            <a
                class="nav-link"
                data-toggle="tab"
                href="#tab-wallet"
            >
                <i class="fas fa-wallet"></i>
                Wallet
                @if($customer->withdrawals->count())
                    <span class="tab-count">
                        {{ $customer->withdrawals->count() }}
                    </span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a
                class="nav-link"
                data-toggle="tab"
                href="#tab-charges"
            >
                <i class="fas fa-receipt"></i>
                Charges
                @if($pendingCharges->count())
                    <span class="tab-count">
                        {{ $pendingCharges->count() }}
                    </span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a
                class="nav-link"
                data-toggle="tab"
                href="#tab-payment-status"
            >
                <i class="fas fa-history"></i>
                Payment History
            </a>
        </li>

    </ul>


    <div class="tab-content">


        <!-- ====================================================
             TAB 1 : OVERVIEW
        ===================================================== -->

        <div
            class="tab-pane fade show active"
            id="tab-dashboard"
        >

            @if($customer->winners->count())

                <div class="winner-banner">

                    <div class="winner-content">

                        <div class="winner-icon">
                            <i class="fas fa-trophy"></i>
                        </div>

                        <div>

                            <h3 class="winner-title">
                                Congratulations! You have winning ticket(s)
                            </h3>

                            <p class="winner-text">
                                Your winning tickets are highlighted below.
                                Open Wallet to view settlement and withdrawal status.
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            <!-- TICKETS -->

            <section class="section-card">

                <div class="section-header">

                    <div>

                        <h2 class="section-title">
                            <i class="fas fa-ticket-alt text-primary mr-2"></i>
                            My Tickets
                        </h2>

                        <p class="section-subtitle">
                            All tickets grouped by draw
                        </p>

                    </div>

                    <span class="status-pill status-processing">
                        <i class="fas fa-clock"></i>
                        Live draw status
                    </span>

                </div>


                <div class="section-body">

                    <div class="row">

                        @forelse(
                            $customer->tickets->groupBy(
                                fn($ticket) =>
                                $ticket->ticket_type_id . '-' . $ticket->draw_id
                            )
                            as $tickets
                        )

                            @php
                                $first = $tickets->first();
                                $draw = $first->draw;

                                $hasWinner = $tickets
                                    ->whereIn('id', $winningTicketIds)
                                    ->isNotEmpty();

                                $winningRows = $customer->winners
                                    ->whereIn('ticket_id', $tickets->pluck('id'));

                                $drawDateTime = null;

                                if ($draw && $draw->draw_date) {
                                    $drawDateTime = \Carbon\Carbon::parse(
                                        $draw->draw_date . ' ' .
                                        ($draw->draw_time ?: '23:59:59')
                                    );
                                }

                                $drawCompleted = $drawDateTime
                                    ? $drawDateTime->isPast()
                                    : false;
                            @endphp


                            <div class="col-lg-6 mb-3">

                                <div class="ticket-card
                                    {{ $hasWinner ? 'winner' : '' }}
                                    {{ $drawCompleted && !$hasWinner ? 'completed' : '' }}"
                                >

                                    <div class="ticket-top">

                                        <div>

                                            <div class="ticket-type">
                                                {{ $first->ticketType->name ?? 'Lottery Ticket' }}
                                            </div>

                                            <div class="ticket-draw">

                                                <i class="far fa-calendar-alt mr-1"></i>

                                                {{ $draw->draw_number ?? 'Draw' }}

                                                @if($draw && $draw->draw_date)
                                                    •
                                                    {{ \Carbon\Carbon::parse($draw->draw_date)->format('d M Y') }}
                                                @endif

                                            </div>

                                        </div>


                                        @if($hasWinner)

                                            <span class="ticket-status winner">
                                                <i class="fas fa-trophy"></i>
                                                WINNER
                                            </span>

                                        @elseif($drawCompleted)

                                            <span class="ticket-status completed">
                                                <i class="fas fa-check-circle"></i>
                                                DRAW COMPLETED
                                            </span>

                                        @else

                                            <span class="ticket-status active">
                                                <i class="fas fa-bolt"></i>
                                                ACTIVE
                                            </span>

                                        @endif

                                    </div>


                                    <div class="ticket-number-box">

                                        <div>

                                            <div class="ticket-number-label">
                                                Ticket Number
                                            </div>

                                            <div class="ticket-number">
                                                {{ $tickets->pluck('ticket_number')->join(', ') }}
                                            </div>

                                        </div>

                                        @if(!$drawCompleted && $drawDateTime)

                                            <span
                                                class="countdown blink"
                                                data-time="{{ $drawDateTime->format('Y-m-d H:i:s') }}"
                                            >
                                                ...
                                            </span>

                                        @else

                                            <span class="countdown">
                                                Completed
                                            </span>

                                        @endif

                                    </div>


                                    @if($hasWinner)

                                        @foreach($winningRows as $winningRow)

                                            <div class="winner-prize">

                                                <div>

                                                    <small>
                                                        <i class="fas fa-medal mr-1"></i>
                                                        {{ $winningRow->prize_position }}
                                                    </small>

                                                    <div>
                                                        <small>
                                                            Winning ticket
                                                        </small>
                                                    </div>

                                                </div>

                                                <strong>
                                                    Rs {{ number_format($winningRow->winning_amount, 2) }}
                                                </strong>

                                            </div>

                                        @endforeach

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="col-12">

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>

                                    <strong>
                                        No tickets available
                                    </strong>

                                    <span>
                                        Your tickets will appear here once assigned.
                                    </span>

                                </div>

                            </div>

                        @endforelse

                    </div>

                </div>

            </section>


            <!-- VERIFIED ADMIN ACCOUNTS -->

            <section class="section-card">

                <div class="section-header">

                    <div>

                        <h2 class="section-title">
                            <i class="fas fa-university text-primary mr-2"></i>
                            Official Payment Account
                        </h2>

                        <p class="section-subtitle">
                            Use only the payment account provided by administration
                        </p>

                    </div>

                </div>


                <div class="section-body">

                    <div class="row">

                        @forelse($bankAccounts as $account)

                            <div class="col-md-6 col-xl-4 mb-3">

                                <div class="admin-bank-card">

                                    <div class="d-flex justify-content-between align-items-start">

                                        <div class="admin-bank-icon">
                                            <i class="fas fa-university"></i>
                                        </div>

                                        @if($account->qr_code)

                                            <img
                                                class="bank-qr"
                                                src="{{ Storage::url($account->qr_code) }}"
                                                alt="Payment QR"
                                            >

                                        @endif

                                    </div>


                                    <div class="bank-title">
                                        {{ $account->account_name }}
                                    </div>

                                    <div class="bank-muted">
                                        {{ $account->bank_name }}
                                        @if($account->branch)
                                            • {{ $account->branch }}
                                        @endif
                                    </div>

                                    <hr>


                                    <div class="bank-muted">
                                        Account Holder
                                    </div>

                                    <div class="bank-value">
                                        {{ $account->account_holder }}
                                    </div>


                                    <div class="bank-muted mt-2">
                                        Account Number
                                    </div>

                                    <div class="bank-value bank-code">
                                        {{ $account->account_number }}
                                    </div>


                                    <div class="bank-muted mt-2">
                                        IFSC
                                    </div>

                                    <div class="bank-value bank-code">
                                        {{ $account->ifsc }}
                                    </div>


                                    @if($account->upi_id)

                                        <div class="bank-muted mt-2">
                                            UPI
                                        </div>

                                        <div class="bank-value">
                                            {{ $account->upi_id }}
                                        </div>

                                    @endif

                                </div>

                            </div>

                        @empty

                            <div class="col-12">

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        <i class="fas fa-university"></i>
                                    </div>

                                    <strong>
                                        Payment account unavailable
                                    </strong>

                                    <span>
                                        Please contact support.
                                    </span>

                                </div>

                            </div>

                        @endforelse

                    </div>

                </div>

            </section>

        </div>


        <!-- ====================================================
             TAB 2 : WALLET
        ===================================================== -->

        <div
            class="tab-pane fade"
            id="tab-wallet"
        >

            <section class="section-card">

                <div class="section-header">

                    <div>

                        <h2 class="section-title">
                            <i class="fas fa-wallet text-primary mr-2"></i>
                            My Wallet
                        </h2>

                        <p class="section-subtitle">
                            Winning and settlement overview
                        </p>

                    </div>

                    @if($customer->winners->count() && !$kycVerified)

                        <span class="status-pill status-pending">
                            <i class="fas fa-id-card"></i>
                            KYC Required
                        </span>

                    @elseif($kycVerified)

                        <span class="status-pill status-success">
                            <i class="fas fa-check-circle"></i>
                            KYC Verified
                        </span>

                    @endif

                </div>


                <div class="section-body">

                    <div class="row">

                        <div class="col-md-6 col-xl-3 mb-3">

                            <div class="wallet-stat">

                                <div class="wallet-stat-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>

                                <div class="wallet-stat-label">
                                    Winning Amount
                                </div>

                                <div class="wallet-stat-value">
                                    Rs {{ number_format($totalWinningAmount, 2) }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6 col-xl-3 mb-3">

                            <div class="wallet-stat">

                                <div class="wallet-stat-icon">
                                    <i class="fas fa-undo-alt"></i>
                                </div>

                                <div class="wallet-stat-label">
                                    Refundable Charges
                                </div>

                                <div class="wallet-stat-value">
                                    Rs {{ number_format($refundablePaidCharges, 2) }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6 col-xl-3 mb-3">

                            <div class="wallet-stat">

                                <div class="wallet-stat-icon">
                                    <i class="fas fa-wallet"></i>
                                </div>

                                <div class="wallet-stat-label">
                                    Settlement Value
                                </div>

                                <div class="wallet-stat-value">
                                    Rs {{ number_format($settlementValue, 2) }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6 col-xl-3 mb-3">

                            <div class="wallet-stat">

                                <div class="wallet-stat-icon">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>

                                <div class="wallet-stat-label">
                                    Withdrawals
                                </div>

                                <div class="wallet-stat-value">
                                    {{ $customer->withdrawals->count() }}
                                </div>

                            </div>

                        </div>

                    </div>


                    @if($customer->winners->count())

                        <div class="mt-2">

                            <div class="alert alert-info">

                                <i class="fas fa-info-circle mr-2"></i>

                                Your winning amount is available for withdrawal
                                after the required KYC verification and admin processing.

                            </div>

                        </div>

                    @endif

                </div>

            </section>


            <!-- WITHDRAWAL HISTORY -->

            <section class="section-card">

                <div class="section-header">

                    <div>

                        <h2 class="section-title">
                            <i class="fas fa-money-check-alt text-primary mr-2"></i>
                            Withdrawal & Payout Center
                        </h2>

                        <p class="section-subtitle">
                            Track every withdrawal request and settlement
                        </p>

                    </div>

                </div>


                <div class="section-body">

                    @forelse($customer->withdrawals->sortByDesc('created_at') as $withdrawal)

                        @php
                            $withdrawalStatus = strtolower($withdrawal->status ?? 'pending');

                            if (
                                in_array($withdrawalStatus, ['completed', 'paid', 'approved'])
                            ) {
                                $withdrawalClass = 'status-success';
                                $withdrawalIcon = 'fa-check-circle';
                            } elseif (
                                in_array($withdrawalStatus, ['processing', 'verified'])
                            ) {
                                $withdrawalClass = 'status-processing';
                                $withdrawalIcon = 'fa-spinner';
                            } elseif (
                                in_array($withdrawalStatus, ['rejected', 'cancelled'])
                            ) {
                                $withdrawalClass = 'status-danger';
                                $withdrawalIcon = 'fa-times-circle';
                            } else {
                                $withdrawalClass = 'status-pending';
                                $withdrawalIcon = 'fa-clock';
                            }
                        @endphp


                        <div class="withdrawal-card mb-3">

                            <div class="withdrawal-head">

                                <div>

                                    <div class="withdrawal-number">
                                        {{ $withdrawal->withdrawal_number }}
                                    </div>

                                    <div class="withdrawal-meta">
                                        Requested
                                        {{ optional($withdrawal->created_at)->format('d M Y, h:i A') }}
                                    </div>

                                </div>


                                <span class="status-pill {{ $withdrawalClass }}">

                                    <i class="fas {{ $withdrawalIcon }}"></i>

                                    {{ ucfirst($withdrawal->status) }}

                                </span>

                            </div>


                            <div class="row mt-2">

                                <div class="col-md-6">

                                    <div class="withdrawal-amount">
                                        Rs {{ number_format($withdrawal->winning_amount, 2) }}
                                    </div>

                                    <div class="withdrawal-meta">
                                        Winning payout amount
                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="withdrawal-bank">

                                        <strong>
                                            <i class="fas fa-university mr-1 text-primary"></i>
                                            {{ $withdrawal->bank_name }}
                                        </strong>

                                        <br>

                                        <small>
                                            {{ $withdrawal->account_number }}
                                            @if($withdrawal->ifsc)
                                                • {{ $withdrawal->ifsc }}
                                            @endif
                                        </small>

                                    </div>

                                </div>

                            </div>


                            @if($withdrawal->rejection_reason)

                                <div class="alert alert-danger mt-3 mb-0">

                                    <strong>
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Admin Note:
                                    </strong>

                                    {{ $withdrawal->rejection_reason }}

                                </div>

                            @endif

                        </div>

                    @empty

                        <div class="empty-state">

                            <div class="empty-icon">
                                <i class="fas fa-wallet"></i>
                            </div>

                            <strong>
                                No withdrawal request yet
                            </strong>

                            <span>
                                Your withdrawal history will appear here.
                            </span>

                        </div>

                    @endforelse

                </div>

            </section>

        </div>


        <!-- ====================================================
             TAB 3 : CHARGES
        ===================================================== -->

        <div
            class="tab-pane fade"
            id="tab-charges"
        >

            @if(!$kycVerified)

                <section class="section-card">

                    <div class="section-body">

                        <div class="empty-state">

                            <div class="empty-icon">
                                <i class="fas fa-lock"></i>
                            </div>

                            <strong>
                                Charges are locked
                            </strong>

                            <span>
                                Charges and payment instructions will appear
                                after your KYC withdrawal request is verified.
                            </span>

                        </div>

                    </div>

                </section>

            @else

                <!-- PENDING CHARGES -->

                <section class="section-card">

                    <div class="section-header">

                        <div>

                            <h2 class="section-title">
                                <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>
                                Pending Charges
                            </h2>

                            <p class="section-subtitle">
                                Complete the payment using the official account below
                            </p>

                        </div>

                        <span class="status-pill status-pending">
                            {{ $pendingCharges->count() }} Pending
                        </span>

                    </div>


                    <div class="section-body">

                        <div class="row">

                            @forelse($pendingCharges as $charge)

                                <div class="col-lg-6 mb-3">

                                    <div class="charge-card">

                                        <div class="charge-head">

                                            <div>

                                                <div class="charge-name">
                                                    {{ $charge->charge_name }}
                                                </div>

                                                @if($charge->description)

                                                    <div class="charge-description">
                                                        {{ $charge->description }}
                                                    </div>

                                                @endif

                                            </div>


                                            <div class="charge-amount">
                                                Rs {{ number_format($charge->amount, 2) }}
                                            </div>

                                        </div>


                                        <!-- ADMIN ACCOUNT ONLY -->
                                        <div class="charge-bank">

                                            <div class="charge-bank-title">
                                                <i class="fas fa-shield-alt"></i>
                                                Pay to official account
                                            </div>


                                            @if($bankAccounts->count())

                                                @php
                                                    /*
                                                     * Admin-selected/available payment
                                                     * account is displayed directly.
                                                     *
                                                     * Customer does NOT select bank.
                                                     */
                                                    $paymentAccount = $bankAccounts->first();
                                                @endphp


                                                <div class="charge-bank-name">
                                                    {{ $paymentAccount->account_name }}
                                                </div>

                                                <div class="charge-bank-info">

                                                    {{ $paymentAccount->bank_name }}

                                                    @if($paymentAccount->branch)
                                                        • {{ $paymentAccount->branch }}
                                                    @endif

                                                    <br>

                                                    A/C:
                                                    <strong>
                                                        {{ $paymentAccount->account_number }}
                                                    </strong>

                                                    • IFSC:
                                                    <strong>
                                                        {{ $paymentAccount->ifsc }}
                                                    </strong>

                                                    @if($paymentAccount->upi_id)

                                                        <br>

                                                        UPI:
                                                        <strong>
                                                            {{ $paymentAccount->upi_id }}
                                                        </strong>

                                                    @endif

                                                </div>

                                            @else

                                                <div class="text-danger">
                                                    Official payment account is currently unavailable.
                                                </div>

                                            @endif

                                        </div>


                                        @if($bankAccounts->count())

                                            <form
                                                method="POST"
                                                enctype="multipart/form-data"
                                                action="{{ route('customer.charges.pay', $charge) }}"
                                                class="payment-form"
                                            >

                                                @csrf

                                                <!--
                                                    IMPORTANT:
                                                    No bank_account_id select is used.
                                                    Admin's configured account is displayed
                                                    directly above.
                                                -->


                                                <label class="form-label-custom">
                                                    UTR / Transaction Number
                                                </label>

                                                <input
                                                    type="text"
                                                    name="utr_number"
                                                    class="form-control mb-2"
                                                    placeholder="Enter UTR / transaction number"
                                                    required
                                                >


                                                <label class="form-label-custom">
                                                    Payment Screenshot
                                                </label>

                                                <input
                                                    type="file"
                                                    name="payment_screenshot"
                                                    class="form-control mb-3"
                                                    accept="image/*,.pdf"
                                                    required
                                                >


                                                <button
                                                    type="submit"
                                                    class="btn-main btn-block"
                                                >
                                                    <i class="fas fa-paper-plane"></i>
                                                    Submit Payment Proof
                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </div>

                            @empty

                                <div class="col-12">

                                    <div class="empty-state">

                                        <div class="empty-icon">
                                            <i class="fas fa-check"></i>
                                        </div>

                                        <strong>
                                            No pending charges
                                        </strong>

                                        <span>
                                            You are all caught up.
                                        </span>

                                    </div>

                                </div>

                            @endforelse

                        </div>

                    </div>

                </section>


                <!-- COMPLETED PAYMENTS -->

                <section class="section-card">

                    <div class="section-header">

                        <div>

                            <h2 class="section-title">
                                <i class="fas fa-check-double text-success mr-2"></i>
                                Completed Payments
                            </h2>

                            <p class="section-subtitle">
                                Successfully submitted and verified charges
                            </p>

                        </div>

                        <span class="status-pill status-success">
                            {{ $completedCharges->count() }} Completed
                        </span>

                    </div>


                    <div class="section-body">

                        @forelse($completedCharges as $charge)

                            <div class="completed-payment mb-2">

                                <div class="completed-left">

                                    <div class="completed-icon">
                                        <i class="fas fa-check"></i>
                                    </div>

                                    <div>

                                        <div class="completed-name">
                                            {{ $charge->charge_name }}
                                        </div>

                                        <div class="completed-info">

                                            {{ $charge->is_refundable
                                                ? 'Refundable'
                                                : 'Non-refundable'
                                            }}

                                            @foreach($charge->payments as $payment)

                                                @if($payment->utr_number)

                                                    • UTR:
                                                    {{ $payment->utr_number }}

                                                @endif

                                            @endforeach

                                        </div>

                                    </div>

                                </div>


                                <div class="text-right">

                                    <div class="font-weight-bold">
                                        Rs {{ number_format($charge->amount, 2) }}
                                    </div>

                                    <span class="status-pill status-success mt-1">
                                        <i class="fas fa-check-circle"></i>
                                        {{ ucfirst($charge->status) }}
                                    </span>

                                </div>

                            </div>

                        @empty

                            <div class="empty-state py-4">

                                <div class="empty-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>

                                <strong>
                                    No completed payments
                                </strong>

                                <span>
                                    Completed payment records will appear here.
                                </span>

                            </div>

                        @endforelse

                    </div>

                </section>

            @endif

        </div>


        <!-- ====================================================
             TAB 4 : PAYMENT HISTORY
        ===================================================== -->

        <div
            class="tab-pane fade"
            id="tab-payment-status"
        >

            <section class="section-card">

                <div class="section-header">

                    <div>

                        <h2 class="section-title">
                            <i class="fas fa-history text-primary mr-2"></i>
                            Payment & Settlement History
                        </h2>

                        <p class="section-subtitle">
                            Complete account settlement overview
                        </p>

                    </div>

                </div>


                <div class="section-body">

                    <div class="wallet-hero mb-4">

                        <div class="wallet-content">

                            <div class="wallet-label">
                                Total Settlement Value
                            </div>

                            <div class="wallet-amount">
                                Rs {{ number_format($settlementValue, 2) }}
                            </div>

                            <p class="wallet-description">
                                Winning amount:
                                Rs {{ number_format($totalWinningAmount, 2) }}

                                &nbsp; + &nbsp;

                                Refundable charges:
                                Rs {{ number_format($refundablePaidCharges, 2) }}
                            </p>

                        </div>

                    </div>


                    @forelse(
                        $customer->withdrawals->sortByDesc('created_at')
                        as $withdrawal
                    )

                        @php
                            $status = strtolower($withdrawal->status ?? 'pending');

                            if (in_array($status, ['completed', 'paid', 'approved'])) {
                                $statusClass = 'status-success';
                                $statusIcon = 'fa-check-circle';
                            } elseif (in_array($status, ['processing', 'verified'])) {
                                $statusClass = 'status-processing';
                                $statusIcon = 'fa-sync-alt';
                            } elseif (in_array($status, ['rejected', 'cancelled'])) {
                                $statusClass = 'status-danger';
                                $statusIcon = 'fa-times-circle';
                            } else {
                                $statusClass = 'status-pending';
                                $statusIcon = 'fa-clock';
                            }
                        @endphp


                        <div class="withdrawal-card mb-3">

                            <div class="withdrawal-head">

                                <div>

                                    <div class="withdrawal-number">
                                        {{ $withdrawal->withdrawal_number }}
                                    </div>

                                    <div class="withdrawal-meta">
                                        {{ optional($withdrawal->created_at)->format('d M Y, h:i A') }}
                                    </div>

                                </div>

                                <span class="status-pill {{ $statusClass }}">

                                    <i class="fas {{ $statusIcon }}"></i>

                                    {{ ucfirst($withdrawal->status) }}

                                </span>

                            </div>


                            <div class="row align-items-center mt-2">

                                <div class="col-md-4">

                                    <div class="withdrawal-amount">
                                        Rs {{ number_format($withdrawal->winning_amount, 2) }}
                                    </div>

                                    <div class="withdrawal-meta">
                                        Withdrawal amount
                                    </div>

                                </div>


                                <div class="col-md-5">

                                    <div class="withdrawal-bank">

                                        <strong>
                                            {{ $withdrawal->bank_name }}
                                        </strong>

                                        <br>

                                        <small>
                                            A/C:
                                            {{ $withdrawal->account_number }}

                                            @if($withdrawal->ifsc)
                                                • IFSC:
                                                {{ $withdrawal->ifsc }}
                                            @endif
                                        </small>

                                    </div>

                                </div>


                                <div class="col-md-3 text-md-right mt-2 mt-md-0">

                                    @if($withdrawal->rejection_reason)

                                        <small class="text-danger">
                                            {{ $withdrawal->rejection_reason }}
                                        </small>

                                    @else

                                        <small class="text-muted">
                                            {{ ucfirst($withdrawal->status) }}
                                        </small>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @empty

                        <div class="empty-state">

                            <div class="empty-icon">
                                <i class="fas fa-history"></i>
                            </div>

                            <strong>
                                No payment history
                            </strong>

                            <span>
                                Your withdrawal activity will appear here.
                            </span>

                        </div>

                    @endforelse


                    <div class="alert alert-warning mt-4 mb-0">

                        <i class="fas fa-info-circle mr-2"></i>

                        <strong>Information:</strong>
                        This dashboard is used for internal verification,
                        ticket and payout status tracking.

                    </div>

                </div>

            </section>

        </div>

    </div>

</main>


<!-- ============================================================
     KYC / WITHDRAWAL MODALS
============================================================= -->

@foreach($customer->winners as $winner)

    @unless($winner->withdrawal)

        <div
            class="modal fade"
            id="kyc-{{ $winner->id }}"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >

            <div
                class="modal-dialog modal-lg modal-dialog-centered"
                role="document"
            >

                <form
                    class="modal-content"
                    method="POST"
                    enctype="multipart/form-data"
                    action="{{ route('customer.withdrawals.store', $winner) }}"
                >

                    @csrf


                    <div class="modal-header">

                        <div>

                            <h5 class="modal-title">
                                {{ $settings['kyc_title'] ?? 'KYC & Withdrawal Verification' }}
                            </h5>

                            <div class="modal-note">
                                {{ $settings['kyc_note'] ?? 'Submit your details for payout verification.' }}
                            </div>

                        </div>


                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span>&times;</span>
                        </button>

                    </div>


                    <div class="modal-body">

                        <div class="kyc-prize mb-4">

                            <small>
                                Winning Ticket
                                {{ $winner->ticket->ticket_number }}
                            </small>

                            <strong>
                                Rs {{ number_format($winner->winning_amount, 2) }}
                            </strong>

                        </div>


                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    Account Holder Name
                                </label>

                                <input
                                    type="text"
                                    name="account_holder_name"
                                    class="form-control"
                                    placeholder="Enter account holder name"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    Bank Name
                                </label>

                                <input
                                    type="text"
                                    name="bank_name"
                                    class="form-control"
                                    placeholder="Enter bank name"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    Account Number
                                </label>

                                <input
                                    type="text"
                                    name="account_number"
                                    class="form-control"
                                    placeholder="Enter account number"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    IFSC Code
                                </label>

                                <input
                                    type="text"
                                    name="ifsc"
                                    class="form-control"
                                    placeholder="Enter IFSC"
                                    required
                                >

                            </div>


                            <div class="col-md-12 mb-3">

                                <label class="form-label-custom">
                                    UPI ID
                                </label>

                                <input
                                    type="text"
                                    name="upi_id"
                                    class="form-control"
                                    placeholder="Optional UPI ID"
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    Aadhaar Front
                                </label>

                                <input
                                    type="file"
                                    name="documents[aadhaar_front]"
                                    class="form-control"
                                    accept="image/*,.pdf"
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    Aadhaar Back
                                </label>

                                <input
                                    type="file"
                                    name="documents[aadhaar_back]"
                                    class="form-control"
                                    accept="image/*,.pdf"
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    PAN Card
                                </label>

                                <input
                                    type="file"
                                    name="documents[pan_card]"
                                    class="form-control"
                                    accept="image/*,.pdf"
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label-custom">
                                    Customer Photo
                                </label>

                                <input
                                    type="file"
                                    name="documents[customer_photo]"
                                    class="form-control"
                                    accept="image/*"
                                >

                            </div>

                        </div>


                        <div class="alert alert-info mb-0">

                            <i class="fas fa-shield-alt mr-2"></i>

                            Your bank and KYC information will be submitted
                            for admin verification before payout processing.

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-light"
                            data-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn-main"
                        >
                            <i class="fas fa-paper-plane"></i>
                            Submit KYC & Withdrawal
                        </button>

                    </div>

                </form>

            </div>

        </div>

    @endunless

@endforeach


<!-- ============================================================
     FOOTER
============================================================= -->

<footer class="dashboard-footer">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-md-5 mb-3 mb-md-0">

                <div class="footer-title">
                    {{ $settings['title'] ?? 'Customer Portal' }}
                </div>

                <div class="footer-note">
                    {{ $settings['footer_note'] ?? 'For customer support and account assistance, please contact our support team.' }}
                </div>

            </div>


            <div class="col-md-7 text-md-right">

                @if(!empty($settings['support_whatsapp']))

                    <span class="support-chip">
                        <i class="fab fa-whatsapp text-success"></i>
                        {{ $settings['support_whatsapp'] }}
                    </span>

                @endif


                @if(!empty($settings['support_call']))

                    <span class="support-chip">
                        <i class="fas fa-phone text-warning"></i>
                        {{ $settings['support_call'] }}
                    </span>

                @endif


                @if(!empty($settings['support_address']))

                    <span class="support-chip">
                        <i class="fas fa-map-marker-alt text-info"></i>
                        {{ $settings['support_address'] }}
                    </span>

                @endif

            </div>

        </div>

    </div>

</footer>


<!-- ============================================================
     JAVASCRIPT
============================================================= -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


<script>

    /*
    |--------------------------------------------------------------------------
    | Draw Countdown
    |--------------------------------------------------------------------------
    */

    function updateCountdowns() {

        document.querySelectorAll('.countdown[data-time]').forEach(function (element) {

            var targetString = element.getAttribute('data-time');

            if (!targetString) {
                return;
            }

            var target = new Date(
                targetString.replace(' ', 'T')
            ).getTime();

            var now = Date.now();

            var difference = target - now;

            if (difference <= 0) {

                element.classList.remove('blink');

                element.textContent = 'Draw completed';

                return;
            }

            var days = Math.floor(
                difference / (1000 * 60 * 60 * 24)
            );

            var hours = Math.floor(
                (difference % (1000 * 60 * 60 * 24)) /
                (1000 * 60 * 60)
            );

            var minutes = Math.floor(
                (difference % (1000 * 60 * 60)) /
                (1000 * 60)
            );

            var seconds = Math.floor(
                (difference % (1000 * 60)) /
                1000
            );

            element.textContent =
                days + 'd ' +
                hours + 'h ' +
                minutes + 'm ' +
                seconds + 's';

        });

    }

    updateCountdowns();

    setInterval(updateCountdowns, 1000);


    /*
    |--------------------------------------------------------------------------
    | Open Wallet Tab
    |--------------------------------------------------------------------------
    */

    $(document).on('click', 'a[href="#tab-wallet"]', function (event) {

        event.preventDefault();

        $('.dashboard-tabs .nav-link').removeClass('active');

        $('.dashboard-tabs .nav-link[href="#tab-wallet"]')
            .addClass('active');

        $('.tab-pane').removeClass('show active');

        $('#tab-wallet').addClass('show active');

        window.scrollTo({
            top: document.querySelector('.dashboard-tabs').offsetTop - 20,
            behavior: 'smooth'
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Prevent Double Submit
    |--------------------------------------------------------------------------
    */

    $('form').on('submit', function () {

        var button = $(this).find(
            'button[type="submit"]'
        );

        if (button.length) {

            setTimeout(function () {

                button
                    .prop('disabled', true)
                    .html(
                        '<i class="fas fa-spinner fa-spin mr-1"></i> Processing...'
                    );

            }, 50);

        }

    });


    /*
    |--------------------------------------------------------------------------
    | File Name Preview
    |--------------------------------------------------------------------------
    */

    $('input[type="file"]').on('change', function () {

        var files = this.files;

        if (!files || !files.length) {
            return;
        }

        var fileName = files.length === 1
            ? files[0].name
            : files.length + ' files selected';

        $(this).attr('title', fileName);

    });

</script>

</body>
</html>

