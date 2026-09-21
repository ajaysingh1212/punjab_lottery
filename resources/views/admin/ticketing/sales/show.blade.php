@extends('admin.layouts.app')
@section('title','Sale Details')
@section('page-title',$sale->sale_number)
@section('content')
<div class="card"><div class="card-header"><h3>{{ $sale->customer->full_name }} - Rs {{ number_format($sale->total_amount,2) }}</h3></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Ticket</th><th>Type</th><th>Draw</th><th>Status</th></tr></thead><tbody>@foreach($sale->tickets as $ticket)<tr><td>{{ $ticket->ticket_number }}</td><td>{{ $ticket->ticketType->name }}</td><td>{{ $ticket->draw->draw_number }}</td><td>{{ ucfirst($ticket->status) }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
