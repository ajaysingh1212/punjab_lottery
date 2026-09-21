@extends('admin.layouts.app')

@section('title', 'Withdrawal Details')
@section('page-title', 'Withdrawal Details')

@section('breadcrumbs')

    <li class="breadcrumb-item">
        <a href="{{ route('admin.withdrawals.index') }}">
            Withdrawals
        </a>
    </li>

    <li class="breadcrumb-item active">
        {{ $withdrawal->withdrawal_number }}
    </li>

@endsection

@section('content')

@php

    $statusClass = match($withdrawal->status) {
        'pending'    => 'badge-warning',
        'processing' => 'badge-info',
        'approved'   => 'badge-primary',
        'rejected'   => 'badge-danger',
        'paid'       => 'badge-success',
        'completed'  => 'badge-success',
        default      => 'badge-secondary',
    };

    /*
     * Banking fields can exist either directly on withdrawal
     * or inside customer/user data depending on your database design.
     *
     * We automatically detect commonly used banking field names.
     */

    $withdrawalAttributes = $withdrawal->getAttributes();

    $bankingKeywords = [
        'bank',
        'account',
        'ifsc',
        'upi',
        'branch',
        'holder',
        'beneficiary',
        'pan',
    ];

    $bankingFields = [];

    foreach ($withdrawalAttributes as $key => $value) {

        $lowerKey = strtolower($key);

        foreach ($bankingKeywords as $keyword) {

            if (
                str_contains($lowerKey, $keyword) &&
                !in_array($key, [
                    'id',
                    'admin_id',
                    'customer_id',
                    'winner_id',
                ])
            ) {
                $bankingFields[$key] = $value;
                break;
            }

        }

    }

@endphp


{{-- =========================================================
     TOP HEADER
========================================================= --}}

<div class="card mb-4">

    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-md-7">

                <h4 class="mb-1">
                    Withdrawal #{{ $withdrawal->withdrawal_number }}
                </h4>

                <div class="text-muted">
                    Requested on
                    {{ $withdrawal->created_at?->format('d M Y, h:i A') }}
                </div>

            </div>

            <div class="col-md-5 text-md-right mt-3 mt-md-0">

                <span class="badge {{ $statusClass }}"
                      style="font-size:14px; padding:8px 14px;">

                    {{ ucfirst($withdrawal->status) }}

                </span>

                <a href="{{ route('admin.withdrawals.index') }}"
                   class="btn btn-secondary ml-2">

                    <i class="fas fa-arrow-left"></i>
                    Back

                </a>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     CUSTOMER + WITHDRAWAL
========================================================= --}}

<div class="row">

    {{-- CUSTOMER DETAILS --}}
    <div class="col-lg-6">

        <div class="card h-100">

            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user mr-2"></i>
                    Customer Details
                </h5>
            </div>

            <div class="card-body">

                @if($withdrawal->customer)

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <small class="text-muted">
                                Full Name
                            </small>

                            <div class="font-weight-bold">
                                {{ $withdrawal->customer->full_name ?? 'N/A' }}
                            </div>

                        </div>


                        @if($withdrawal->customer->user?->email)

                            <div class="col-md-6 mb-3">

                                <small class="text-muted">
                                    Email
                                </small>

                                <div>
                                    {{ $withdrawal->customer->user->email }}
                                </div>

                            </div>

                        @endif


                        @if($withdrawal->customer->mobile ?? null)

                            <div class="col-md-6 mb-3">

                                <small class="text-muted">
                                    Mobile
                                </small>

                                <div>
                                    {{ $withdrawal->customer->mobile }}
                                </div>

                            </div>

                        @endif


                        @if($withdrawal->customer->phone ?? null)

                            <div class="col-md-6 mb-3">

                                <small class="text-muted">
                                    Phone
                                </small>

                                <div>
                                    {{ $withdrawal->customer->phone }}
                                </div>

                            </div>

                        @endif


                        @if($withdrawal->customer->address ?? null)

                            <div class="col-md-12 mb-3">

                                <small class="text-muted">
                                    Address
                                </small>

                                <div>
                                    {{ $withdrawal->customer->address }}
                                </div>

                            </div>

                        @endif

                    </div>

                @else

                    <div class="alert alert-warning mb-0">
                        Customer information is not available.
                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- WITHDRAWAL DETAILS --}}
    <div class="col-lg-6 mt-4 mt-lg-0">

        <div class="card h-100">

            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-wallet mr-2"></i>
                    Withdrawal Details
                </h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <small class="text-muted">
                            Withdrawal Number
                        </small>

                        <div class="font-weight-bold">
                            {{ $withdrawal->withdrawal_number }}
                        </div>

                    </div>


                    <div class="col-md-6 mb-3">

                        <small class="text-muted">
                            Status
                        </small>

                        <div>
                            <span class="badge {{ $statusClass }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </div>

                    </div>


                    <div class="col-md-6 mb-3">

                        <small class="text-muted">
                            Winning Amount
                        </small>

                        <div class="font-weight-bold text-success">
                            ₹{{ number_format((float)$withdrawal->winning_amount, 2) }}
                        </div>

                    </div>


                    <div class="col-md-6 mb-3">

                        <small class="text-muted">
                            Net Amount
                        </small>

                        <div class="font-weight-bold">
                            ₹{{ number_format((float)$withdrawal->net_amount, 2) }}
                        </div>

                    </div>


                    @if($withdrawal->payment_utr)

                        <div class="col-md-12 mb-3">

                            <small class="text-muted">
                                Payment UTR
                            </small>

                            <div class="font-weight-bold">
                                {{ $withdrawal->payment_utr }}
                            </div>

                        </div>

                    @endif


                    @if($withdrawal->rejection_reason)

                        <div class="col-md-12">

                            <div class="alert alert-danger mb-0">

                                <strong>
                                    Rejection Reason:
                                </strong>

                                <div class="mt-1">
                                    {{ $withdrawal->rejection_reason }}
                                </div>

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     BANKING DETAILS
========================================================= --}}

<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-university mr-2"></i>
            Customer Banking Details
        </h5>

    </div>

    <div class="card-body">

        @if(count($bankingFields))

            <div class="row">

                @foreach($bankingFields as $key => $value)

                    @if($value !== null && $value !== '')

                        <div class="col-md-4 mb-4">

                            <small class="text-muted d-block">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </small>

                            <div class="font-weight-bold">

                                @if(
                                    str_contains(strtolower($key), 'account') &&
                                    strlen((string)$value) > 4
                                )

                                    {{ substr((string)$value, 0, 2) }}
                                    ****
                                    {{ substr((string)$value, -4) }}

                                @else

                                    {{ $value }}

                                @endif

                            </div>

                        </div>

                    @endif

                @endforeach

            </div>

        @else

            <div class="alert alert-info mb-0">

                <i class="fas fa-info-circle mr-1"></i>

                Banking details are not stored directly on this withdrawal record.

                @if($withdrawal->customer)
                    Please check the customer profile/banking section.
                @endif

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     WINNING / TICKET / DRAW DETAILS
========================================================= --}}

@if($withdrawal->winner)

<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-ticket-alt mr-2"></i>
            Winning / Ticket Details
        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            @if($withdrawal->winner->ticket)

                <div class="col-md-4 mb-3">

                    <small class="text-muted">
                        Ticket
                    </small>

                    <div class="font-weight-bold">
                        {{ $withdrawal->winner->ticket->ticket_number ?? $withdrawal->winner->ticket->id }}
                    </div>

                </div>

                @if($withdrawal->winner->ticket->ticketType)

                    <div class="col-md-4 mb-3">

                        <small class="text-muted">
                            Ticket Type
                        </small>

                        <div>
                            {{ $withdrawal->winner->ticket->ticketType->name ?? 'N/A' }}
                        </div>

                    </div>

                @endif

            @endif


            @if($withdrawal->winner->draw)

                <div class="col-md-4 mb-3">

                    <small class="text-muted">
                        Draw
                    </small>

                    <div>
                        {{ $withdrawal->winner->draw->name
                            ?? $withdrawal->winner->draw->id }}
                    </div>

                </div>

                @if($withdrawal->winner->draw->draw_date ?? null)

                    <div class="col-md-4 mb-3">

                        <small class="text-muted">
                            Draw Date
                        </small>

                        <div>
                            {{ \Carbon\Carbon::parse($withdrawal->winner->draw->draw_date)->format('d M Y') }}
                        </div>

                    </div>

                @endif

            @endif

        </div>

    </div>

</div>

@endif


{{-- =========================================================
     DOCUMENTS
========================================================= --}}

<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-file-alt mr-2"></i>
            Customer Documents
        </h5>

    </div>

    <div class="card-body">

        @if($withdrawal->documents && $withdrawal->documents->count())

            <div class="row">

                @foreach($withdrawal->documents as $document)

                    @php

                        $documentPath =
                            $document->file_path
                            ?? $document->path
                            ?? $document->document_path
                            ?? $document->file
                            ?? null;

                        $documentName =
                            $document->document_name
                            ?? $document->name
                            ?? $document->type
                            ?? 'Document';

                    @endphp

                    <div class="col-md-6 col-lg-4 mb-3">

                        <div class="border rounded p-3 h-100">

                            <div class="d-flex align-items-center mb-3">

                                <div class="mr-3">

                                    <i class="fas fa-file fa-2x text-primary"></i>

                                </div>

                                <div>

                                    <strong>
                                        {{ ucwords(str_replace('_', ' ', $documentName)) }}
                                    </strong>

                                    @if($document->created_at)
                                        <small class="d-block text-muted">
                                            Uploaded
                                            {{ $document->created_at->format('d M Y, h:i A') }}
                                        </small>
                                    @endif

                                </div>

                            </div>


                            @if($documentPath)

                                <a href="{{ asset('storage/' . ltrim($documentPath, '/')) }}"
                                   target="_blank"
                                   class="btn btn-primary btn-sm">

                                    <i class="fas fa-eye"></i>
                                    View Document

                                </a>

                                <a href="{{ asset('storage/' . ltrim($documentPath, '/')) }}"
                                   target="_blank"
                                   class="btn btn-outline-secondary btn-sm">

                                    <i class="fas fa-external-link-alt"></i>

                                </a>

                            @else

                                <span class="badge badge-secondary">
                                    File not available
                                </span>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="text-center py-4">

                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>

                <h6>No Documents Uploaded</h6>

                <p class="text-muted mb-0">
                    Customer has not uploaded any documents for this withdrawal.
                </p>

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     UPDATE WITHDRAWAL
========================================================= --}}

<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-sync-alt mr-2"></i>
            Update Withdrawal
        </h5>

    </div>

    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.withdrawals.update', $withdrawal) }}">

            @csrf
            @method('PATCH')

            <div class="row">

                <div class="col-md-4">

                    <label>
                        Status
                    </label>

                    <select name="status"
                            class="form-control">

                        @foreach([
                            'pending',
                            'processing',
                            'approved',
                            'rejected',
                            'paid',
                            'completed'
                        ] as $status)

                            <option value="{{ $status }}"
                                @selected($withdrawal->status === $status)>

                                {{ ucfirst($status) }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-md-4">

                    <label>
                        Payment UTR
                    </label>

                    <input type="text"
                           name="payment_utr"
                           class="form-control"
                           value="{{ old('payment_utr', $withdrawal->payment_utr) }}"
                           placeholder="Enter payment UTR">

                </div>


                <div class="col-md-4">

                    <label>
                        Rejection Reason
                    </label>

                    <input type="text"
                           name="rejection_reason"
                           class="form-control"
                           value="{{ old('rejection_reason', $withdrawal->rejection_reason) }}"
                           placeholder="Required when rejected">

                </div>

            </div>


            <div class="mt-4">

                <button type="submit"
                        class="btn btn-primary">

                    <i class="fas fa-save"></i>
                    Update Withdrawal

                </button>

            </div>

        </form>

    </div>

</div>

@endsection
