@extends('admin.layouts.app')
@section('title','Ticketing Dashboard')
@section('page-title','Ticketing Dashboard')
@section('content')
<form class="mb-3 d-flex" method="GET"><select name="period" class="form-control" style="max-width:220px" onchange="this.form.submit()">@foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $key=>$label)<option value="{{ $key }}" @selected($period===$key)>{{ $label }}</option>@endforeach</select></form>
<div class="row">
@foreach([
    ['Customers',$customerCount,'fa-users','primary'],
    ['Tickets Sold',$ticketCount,'fa-ticket','success'],
    ['Sales Total','Rs '.number_format($salesTotal,2),'fa-chart-line','info'],
    ['Pending Withdrawals',$pendingWithdrawals,'fa-money-bill-transfer','warning'],
] as $card)
<div class="col-md-3 col-sm-6"><div class="info-box"><span class="info-box-icon bg-{{ $card[3] }}"><i class="fas {{ $card[2] }}"></i></span><div class="info-box-content"><span class="info-box-text">{{ $card[0] }}</span><span class="info-box-number">{{ $card[1] }}</span></div></div></div>
@endforeach
</div>
<div class="card"><div class="card-header"><h3>Draw Workload</h3></div><div class="card-body"><div class="row">@foreach(['daily','weekly','monthly','festival'] as $freq)<div class="col-md-3"><div class="border rounded p-3 mb-2"><strong>{{ ucfirst($freq) }}</strong><div class="h4 mb-0">{{ ($drawBreakdown[$freq] ?? collect())->sum('total_tickets') }}</div><small>tickets in selected period</small></div></div>@endforeach</div></div></div>
<div class="card"><div class="card-header"><h3>Upcoming Draws</h3></div><div class="card-body table-responsive"><table class="table datatable"><thead><tr><th>Draw</th><th>Type</th><th>Date</th><th>Status</th><th>Tickets</th></tr></thead><tbody>@foreach($upcomingDraws as $draw)<tr><td><a href="{{ route('admin.draws.show',$draw) }}">{{ $draw->draw_number }}</a></td><td>{{ $draw->ticketType->name }}</td><td>{{ $draw->draw_date->format('d M Y') }}</td><td><span class="badge badge-info">{{ ucfirst($draw->status) }}</span></td><td>{{ $draw->total_tickets }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
