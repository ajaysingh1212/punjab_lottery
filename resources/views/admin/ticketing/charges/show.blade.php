@extends('admin.layouts.app')
@section('title','Charge Statement')
@section('page-title','Charge Statement')
@section('content')
<style>.statement{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.statement-head{background:#102a43;color:#fff;border-radius:8px 8px 0 0;padding:20px 24px}.statement-head h3{font-weight:900;margin:0}.ledger{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px}.btn{border-radius:8px;font-weight:800}</style>
<div class="card statement">
    <div class="statement-head d-flex justify-content-between align-items-center"><div><h3><i class="fas fa-file-invoice mr-2"></i>{{ $charge->charge_name }}</h3><small>{{ $charge->customer->full_name }} - {{ $charge->customer->mobile }}</small></div><a class="btn btn-light" href="{{ route('admin.charges.edit',$charge) }}"><i class="fas fa-edit mr-1"></i>Edit</a></div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3"><div class="ledger">Charge Amount<br><strong>Rs {{ number_format($charge->amount,2) }}</strong></div></div>
            <div class="col-md-3"><div class="ledger">Status<br><strong>{{ ucfirst($charge->status) }}</strong></div></div>
            <div class="col-md-3"><div class="ledger">Refundable<br><strong>{{ $charge->is_refundable ? 'Yes' : 'No' }}</strong></div></div>
            <div class="col-md-3"><div class="ledger">Payments<br><strong>{{ $charge->payments->count() }}</strong></div></div>
        </div>
        <p>{{ $charge->description }}</p>
        <div class="d-flex mb-3"><a href="{{ route('admin.charges.create',['customer_id'=>$charge->customer_id]) }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Add Next Charge</a></div>
        <div class="table-responsive"><table class="table"><thead><tr><th>UTR</th><th>Status</th><th>Verified At</th><th>Admin Notes</th></tr></thead><tbody>@forelse($charge->payments as $payment)<tr><td>{{ $payment->utr_number }}</td><td>{{ ucfirst($payment->status) }}</td><td>{{ optional($payment->verified_at)->format('d M Y h:i A') }}</td><td>{{ $payment->admin_notes }}</td></tr>@empty<tr><td colspan="4" class="text-muted">No payment proof submitted.</td></tr>@endforelse</tbody></table></div>
    </div>
</div>
@endsection
