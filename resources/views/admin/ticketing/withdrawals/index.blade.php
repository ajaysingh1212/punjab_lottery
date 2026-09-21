@extends('admin.layouts.app')

@section('title', 'Withdrawals')
@section('page-title', 'Withdrawals')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Withdrawal Workflow</h3>

        <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="form-inline">
            <select name="status" class="form-control mr-2" onchange="this.form.submit()">
                <option value="">All Status</option>

                @foreach([
                    'pending',
                    'processing',
                    'approved',
                    'rejected',
                    'paid',
                    'completed'
                ] as $status)

                    <option value="{{ $status }}"
                        @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>

                @endforeach
            </select>

            @if(request('status'))
                <a href="{{ route('admin.withdrawals.index') }}"
                   class="btn btn-secondary">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($withdrawals->count())

            <div class="table-responsive">

                <table class="table table-bordered table-hover datatable">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Customer</th>
                            <th>Winning</th>
                            <th>Net Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach($withdrawals as $withdrawal)

                        <tr>

                            <td>
                                <strong>
                                    {{ $withdrawal->withdrawal_number }}
                                </strong>
                            </td>

                            <td>
                                <div>
                                    <strong>
                                        {{ $withdrawal->customer?->full_name ?? 'N/A' }}
                                    </strong>
                                </div>

                                @if($withdrawal->customer?->user?->email)
                                    <small class="text-muted">
                                        {{ $withdrawal->customer->user->email }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                ₹{{ number_format((float)$withdrawal->winning_amount, 2) }}
                            </td>

                            <td>
                                <strong>
                                    ₹{{ number_format((float)$withdrawal->net_amount, 2) }}
                                </strong>
                            </td>

                            <td>

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
                                @endphp

                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>

                            </td>

                            <td>
                                {{ $withdrawal->created_at?->format('d M Y, h:i A') }}
                            </td>

                            <td>

                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}"
                                   class="btn btn-sm btn-primary">

                                    <i class="fas fa-eye"></i>
                                    View

                                </a>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $withdrawals->withQueryString()->links() }}
            </div>

        @else

            <div class="text-center py-5">

                <i class="fas fa-money-check-alt fa-3x text-muted mb-3"></i>

                <h5>No Withdrawals Found</h5>

                <p class="text-muted mb-0">
                    There are no withdrawal requests matching your filter.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection
