@extends('admin.layouts.app')
@section('title',$charge->exists ? 'Edit Charge' : 'Charge')
@section('page-title',$charge->exists ? 'Edit Charge' : 'Charge')
@section('content')
<style>.charge-shell{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.charge-head{background:#4c1d95;color:#fff;border-radius:8px 8px 0 0;padding:20px 24px}.charge-head h3{font-weight:900;margin:0}.quick-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;height:100%}.form-control,.btn{border-radius:8px}.btn{font-weight:800}</style>
<form method="POST" action="{{ $charge->exists ? route('admin.charges.update',$charge) : route('admin.charges.store') }}" class="card charge-shell">
    @csrf @if($charge->exists) @method('PUT') @endif
    <div class="charge-head d-flex justify-content-between align-items-center"><div><h3><i class="fas fa-receipt mr-2"></i>Charge Desk</h3><small>Apply charge to one customer, selected winners, or multiple customers from today's draw basket.</small></div><button class="btn btn-light"><i class="fas fa-save mr-1"></i>Save Charge</button></div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-7 form-group"><label>Customers</label><select name="customer_ids[]" class="form-control select2" multiple required>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(collect(old('customer_ids',$charge->exists ? [$charge->customer_id] : array_filter([request('customer_id')])))->contains($customer->id))>{{ $customer->full_name }} - {{ $customer->mobile }}</option>@endforeach</select><small>Select all winners from today's draw basket when applying a common charge.</small></div>
            <div class="col-lg-5 form-group"><label>Bank Account</label><select name="bank_account_id" class="form-control"><option value="">None</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old('bank_account_id',$charge->bank_account_id)==$account->id)>{{ $account->account_name }}</option>@endforeach</select></div>
            <div class="col-md-4 form-group"><label>Name</label><input name="charge_name" class="form-control" value="{{ old('charge_name',$charge->charge_name) }}" required></div>
            <div class="col-md-2 form-group"><label>Amount</label><input type="number" step="0.01" min="1" name="amount" class="form-control" value="{{ old('amount',$charge->amount) }}" required></div>
            <div class="col-md-3 form-group"><label>Status</label><select name="status" class="form-control">@foreach(['pending','processing','paid','rejected','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status',$charge->status ?: 'pending')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><label>Due Date</label><input type="date" name="due_date" class="form-control" value="{{ old('due_date',optional($charge->due_date)->format('Y-m-d')) }}"></div>
            <div class="col-md-1 form-group pt-4"><label><input type="checkbox" name="is_refundable" value="1" @checked(old('is_refundable',$charge->is_refundable))> Refund</label></div>
        </div>
        @if(!$charge->exists && $winners->count())
            <div class="row mb-3">@foreach($winners->take(6) as $winner)<div class="col-md-4 mb-2"><div class="quick-card"><strong>{{ $winner->customer->full_name }}</strong><br><small>{{ $winner->ticket->ticket_number }} | Rs {{ number_format($winner->winning_amount,2) }}</small></div></div>@endforeach</div>
        @endif
        <label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description',$charge->description) }}</textarea>
        <label class="mt-3">Admin Notes</label><textarea name="admin_notes" class="form-control" rows="3">{{ old('admin_notes',$charge->admin_notes) }}</textarea>
    </div>
</form>
@endsection
