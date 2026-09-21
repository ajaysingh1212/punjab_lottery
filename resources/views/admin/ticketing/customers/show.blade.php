@extends('admin.layouts.app')
@section('title','Customer Detail')
@section('page-title',$customer->full_name)
@section('content')
<style>.profile-card,.ledger-card{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.profile-card{background:#102a43;color:#fff}.stat-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px}.btn{border-radius:8px;font-weight:800}.table td,.table th{vertical-align:middle}</style>
<div class="row">
    <div class="col-md-4"><div class="card profile-card"><div class="card-body"><h4>{{ $customer->full_name }}</h4><p class="mb-1">{{ $customer->customer_code }}</p><p>{{ $customer->mobile }}<br>{{ $customer->email }}</p><span class="badge badge-light">{{ ucfirst($customer->status) }}</span><hr><a class="btn btn-light btn-sm" href="{{ route('admin.customers.edit',$customer) }}"><i class="fas fa-edit mr-1"></i>Edit Customer</a></div></div></div>
    <div class="col-md-8"><div class="card ledger-card"><div class="card-header d-flex"><h3>Financial Summary</h3><a href="{{ route('admin.charges.create',['customer_id'=>$customer->id]) }}" class="btn btn-primary btn-sm ml-auto"><i class="fas fa-plus mr-1"></i>Add Next Charge</a></div><div class="card-body row">
        <div class="col-md-3 mb-2"><div class="stat-box">Tickets<br><strong>{{ $customer->tickets->count() }}</strong></div></div>
        <div class="col-md-3 mb-2"><div class="stat-box">Winnings<br><strong>Rs {{ number_format($customer->winners->sum('winning_amount'),2) }}</strong></div></div>
        <div class="col-md-3 mb-2"><div class="stat-box">Charges<br><strong>Rs {{ number_format($customer->charges->sum('amount'),2) }}</strong></div></div>
        <div class="col-md-3 mb-2"><div class="stat-box">Paid Charges<br><strong>Rs {{ number_format($customer->charges->where('status','paid')->sum('amount'),2) }}</strong></div></div>
    </div></div></div>
</div>
<div class="card ledger-card"><div class="card-header"><h3>Ticket Journey</h3></div><div class="card-body table-responsive"><table class="table datatable"><thead><tr><th>Ticket</th><th>Type</th><th>Draw</th><th>Status</th></tr></thead><tbody>@foreach($customer->tickets as $ticket)<tr><td>{{ $ticket->ticket_number }}</td><td>{{ $ticket->ticketType->name }}</td><td>{{ $ticket->draw->draw_number }}</td><td><span class="badge badge-{{ $ticket->status === 'winner' ? 'success' : 'info' }}">{{ ucfirst($ticket->status) }}</span></td></tr>@endforeach</tbody></table></div></div>
<div class="card ledger-card"><div class="card-header"><h3>Charge Statement</h3></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Charge</th><th>Amount</th><th>Refundable</th><th>Status</th><th>Due</th><th></th></tr></thead><tbody>@forelse($customer->charges as $charge)<tr><td>{{ $charge->charge_name }}<br><small>{{ $charge->description }}</small></td><td>Rs {{ number_format($charge->amount,2) }}</td><td>{{ $charge->is_refundable ? 'Yes' : 'No' }}</td><td><span class="badge badge-info">{{ ucfirst($charge->status) }}</span></td><td>{{ optional($charge->due_date)->format('d M Y') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.charges.show',$charge) }}">View</a></td></tr>@empty<tr><td colspan="6" class="text-muted">No charges assigned yet.</td></tr>@endforelse</tbody></table></div></div>
@endsection
