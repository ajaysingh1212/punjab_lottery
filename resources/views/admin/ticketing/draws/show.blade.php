@extends('admin.layouts.app')
@section('title','Draw')
@section('page-title',$draw->draw_number)
@section('content')
<div class="row">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('admin.draws.winners.store',$draw) }}" class="card">
            @csrf
            <input type="hidden" name="mode" value="auto">
            <div class="card-header"><h3>{{ ucfirst($draw->ticketType->frequency) }} Draw Pool</h3></div>
            <div class="card-body table-responsive">
                <div class="mb-3">
                    @foreach($draw->prize_snapshot ?? [] as $position => $amount)
                        <label class="mr-3"><input type="checkbox" name="positions[]" value="{{ $position }}"> {{ $position }} - Rs {{ number_format($amount,2) }}</label>
                    @endforeach
                </div>
                <table class="table datatable">
                    <thead><tr><th><input type="checkbox" onclick="$('[name=\'ticket_ids[]\']').prop('checked',this.checked)"></th><th>Customer</th><th>Phone</th><th>Tickets</th><th>Active</th></tr></thead>
                    <tbody>
                    @foreach($customerRows as $row)
                        <tr>
                            <td>@foreach($row['tickets']->where('status','active') as $ticket)<input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}"> @endforeach</td>
                            <td>{{ $row['customer']->full_name }}<br><small>{{ $row['customer']->customer_code }}</small></td>
                            <td>{{ $row['customer']->mobile }}</td>
                            <td>{{ $row['tickets']->pluck('ticket_number')->join(', ') }}</td>
                            <td>{{ $row['active_count'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <small>Leave tickets unchecked to draw from all eligible tickets. Same customer cannot win twice in this draw.</small>
            </div>
            <div class="card-footer"><button class="btn btn-success"><i class="fas fa-dice"></i> Auto Draw Selected Positions</button></div>
        </form>
    </div>
    <div class="col-lg-5">
        <form method="POST" action="{{ route('admin.draws.winners.store',$draw) }}" class="card">
            @csrf
            <input type="hidden" name="mode" value="manual">
            <div class="card-header"><h3>Manual Winner</h3></div>
            <div class="card-body">
                <label>Active Ticket</label>
                <select name="ticket_id" class="form-control">
                    @foreach($draw->tickets->where('status','active') as $ticket)
                        <option value="{{ $ticket->id }}">{{ $ticket->ticket_number }} - {{ $ticket->customer->full_name }}</option>
                    @endforeach
                </select>
                <label class="mt-3">Prize Position</label>
                <input type="number" name="prize_position" min="1" max="10" class="form-control">
            </div>
            <div class="card-footer"><button class="btn btn-primary">Assign Manual Winner</button></div>
        </form>
        <div class="card">
            <div class="card-header"><h3>Winners</h3></div>
            <div class="card-body">
                @forelse($draw->winners->sortBy('prize_position') as $winner)
                    <p>{{ $winner->prize_position }}. {{ $winner->customer->full_name }} - {{ $winner->ticket->ticket_number }} - Rs {{ number_format($winner->winning_amount,2) }}</p>
                @empty
                    <p class="text-muted">No winners assigned yet.</p>
                @endforelse
                <form method="POST" action="{{ route('admin.draws.complete',$draw) }}">@csrf<button class="btn btn-success btn-sm">Complete Draw</button></form>
            </div>
        </div>
    </div>
</div>
@endsection
