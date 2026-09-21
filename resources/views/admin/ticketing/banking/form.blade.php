@extends('admin.layouts.app')
@section('title',$account->exists ? 'Edit Bank Account' : 'Bank Account')
@section('page-title',$account->exists ? 'Edit Bank Account' : 'Bank Account')
@section('content')
<style>.bank-form{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.bank-head{background:#0f766e;color:#fff;border-radius:8px 8px 0 0;padding:20px 24px}.bank-head h3{font-weight:900;margin:0}.qr-preview{max-width:120px;border:1px solid #e2e8f0;border-radius:8px;padding:6px}.form-control,.btn{border-radius:8px}.btn{font-weight:800}</style>
<form method="POST" enctype="multipart/form-data" action="{{ $account->exists ? route('admin.bank-accounts.update',$account) : route('admin.bank-accounts.store') }}" class="card bank-form">
    @csrf @if($account->exists) @method('PUT') @endif
    <div class="bank-head d-flex justify-content-between align-items-center"><div><h3><i class="fas fa-university mr-2"></i>Payment Account</h3><small>Accounts marked for charges will be visible on the customer dashboard.</small></div><button class="btn btn-light"><i class="fas fa-save mr-1"></i>Save Account</button></div>
    <div class="card-body"><div class="row">
        @foreach(['account_name'=>'Account Name','bank_name'=>'Bank Name','account_holder'=>'Account Holder','account_number'=>'Account Number','ifsc'=>'IFSC','branch'=>'Branch','upi_id'=>'UPI ID'] as $field=>$label)
            <div class="col-md-4 form-group"><label>{{ $label }}</label><input class="form-control" name="{{ $field }}" value="{{ old($field,$account->$field) }}" @if($field==='account_name') required @endif></div>
        @endforeach
        <div class="col-md-3 form-group"><label>Status</label><select name="status" class="form-control">@foreach(['active','inactive'] as $status)<option value="{{ $status }}" @selected(old('status',$account->status ?: 'active')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="col-md-3 form-group pt-4"><label><input type="checkbox" name="show_for_charges" value="1" @checked(old('show_for_charges',$account->show_for_charges))> Show for charges</label></div>
        <div class="col-md-6 form-group"><label>QR Code</label><input type="file" name="qr_code" class="form-control">@if($account->qr_code)<img class="qr-preview mt-2" src="{{ Storage::url($account->qr_code) }}" alt="QR code">@endif</div>
    </div></div>
</form>
@endsection
