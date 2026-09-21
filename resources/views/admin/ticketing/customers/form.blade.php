@extends('admin.layouts.app')
@section('title',$customer->exists ? 'Edit Customer' : 'Add Customer')
@section('page-title',$customer->exists ? 'Edit Customer' : 'Add Customer')
@section('content')
<style>.form-shell{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.form-hero{background:#102a43;color:#fff;border-radius:8px 8px 0 0;padding:20px 24px}.form-hero h3{font-weight:900;margin:0}.form-control{border-radius:8px}.section-title{font-weight:900;color:#102a43;margin:8px 0 14px}.btn{border-radius:8px;font-weight:800}</style>
<form method="POST" action="{{ $customer->exists ? route('admin.customers.update',$customer) : route('admin.customers.store') }}" class="card form-shell">
    @csrf @if($customer->exists) @method('PUT') @endif
    <div class="form-hero d-flex justify-content-between align-items-center"><div><h3><i class="fas fa-user-check mr-2"></i>Customer Profile</h3><small>Identity, contact, and address details for ticket sales.</small></div><button class="btn btn-light"><i class="fas fa-save mr-1"></i>Save Customer</button></div>
    <div class="card-body">
        <div class="section-title">Basic Details</div>
        <div class="row">
            <div class="col-md-4 form-group"><label>Full Name</label><input class="form-control" name="full_name" value="{{ old('full_name',$customer->full_name) }}" required></div>
            <div class="col-md-4 form-group"><label>Mobile</label><input class="form-control" name="mobile" value="{{ old('mobile',$customer->mobile) }}" required></div>
            <div class="col-md-4 form-group"><label>Email</label><input type="email" class="form-control" name="email" value="{{ old('email',$customer->email) }}"></div>
            <div class="col-md-4 form-group"><label>Status</label><select class="form-control" name="status">@foreach(['active','inactive','blocked'] as $status)<option value="{{ $status }}" @selected(old('status',$customer->status ?: 'active')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        </div>
        <div class="section-title">Address</div>
        <div class="row">
            @foreach(['state'=>'State','district'=>'District','city'=>'City','pincode'=>'Pincode'] as $field=>$label)<div class="col-md-3 form-group"><label>{{ $label }}</label><input class="form-control" name="{{ $field }}" value="{{ old($field,$customer->$field) }}"></div>@endforeach
            <div class="col-md-12 form-group"><label>Full Address</label><textarea class="form-control" name="address" rows="4">{{ old('address',$customer->address) }}</textarea></div>
        </div>
    </div>
</form>
@endsection
