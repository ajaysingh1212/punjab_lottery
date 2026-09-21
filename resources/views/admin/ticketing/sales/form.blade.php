@php($sale = $sale ?? null)
@extends('admin.layouts.app')
@section('title',$sale ? 'Edit Sale' : 'New Sale')
@section('page-title',$sale ? 'Edit Sale' : 'New Sale')
@section('content')
<style>
.ops-card{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.ops-head{background:#0f766e;color:#fff;border-radius:8px 8px 0 0;padding:18px 22px}.ops-title{font-weight:900;margin:0}.summary-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px}.basket-total{font-size:28px;font-weight:900}.table td,.table th{vertical-align:middle}.btn{font-weight:800;border-radius:8px}.form-control{border-radius:8px}
</style>
<form id="sale-form" method="POST" action="{{ $sale ? route('admin.ticket-sales.update',$sale) : route('admin.ticket-sales.store') }}" class="card ops-card">
    @csrf @if($sale) @method('PUT') @endif
    <div class="ops-head d-flex justify-content-between align-items-center"><h3 class="ops-title"><i class="fas fa-shopping-basket mr-2"></i>{{ $sale ? 'Update Sale' : 'Ticket Sale Basket' }}</h3><button class="btn btn-light"><i class="fas fa-save mr-1"></i>{{ $sale ? 'Save Sale' : 'Confirm Sale & Generate Tickets' }}</button></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-5 form-group"><label>Customer</label><select name="customer_id" class="form-control select2" required>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id',$sale?->customer_id)==$customer->id)>{{ $customer->full_name }} - {{ $customer->mobile }}</option>@endforeach</select></div>
            <div class="col-md-3 form-group"><label>Payment Status</label><select name="payment_status" class="form-control">@foreach(['pending','processing','complete','failed','rejected'] as $status)<option value="{{ $status }}" @selected(old('payment_status',$sale?->payment_status)===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-4 form-group"><label>UTR</label><input class="form-control" name="utr_number" value="{{ old('utr_number',$sale?->utr_number) }}"></div>
        </div>

        @if(!$sale)
        <div class="summary-box mb-3">
            <div class="row align-items-end">
                <div class="col-md-4 form-group mb-md-0"><label>Ticket Type</label><select id="ticket-type" class="form-control">@foreach($types as $type)<option value="{{ $type->id }}" data-price="{{ $type->ticket_price }}" data-label="{{ $type->name }} / {{ ucfirst($type->frequency) }}">{{ $type->name }} / {{ ucfirst($type->frequency) }} / Rs {{ $type->ticket_price }}</option>@endforeach</select></div>
                <div class="col-md-2 form-group mb-md-0"><label>Quantity</label><input id="ticket-qty" type="number" min="1" max="500" value="1" class="form-control"></div>
                <div class="col-md-3 form-group mb-md-0"><label>Draw Date</label><input id="draw-date" type="date" class="form-control"></div>
                <div class="col-md-3"><button type="button" id="add-ticket" class="btn btn-success btn-block"><i class="fas fa-plus mr-1"></i>Add To Basket</button></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="basket-table"><thead><tr><th>Ticket Type</th><th>Qty</th><th>Draw Date</th><th>Line Total</th><th></th></tr></thead><tbody></tbody></table>
        </div>
        <div class="text-right mb-3"><span class="text-muted mr-2">Total Amount</span><span class="basket-total">Rs <span id="basket-total">0.00</span></span></div>
        @else
        <div class="alert alert-info">Ticket rows are locked after generation. You can update customer, payment status, UTR, and notes.</div>
        <div class="table-responsive"><table class="table"><thead><tr><th>Type</th><th>Draw</th><th>Qty</th><th>Total</th></tr></thead><tbody>@foreach($sale->items as $item)<tr><td>{{ $item->ticketType->name }}</td><td>{{ $item->draw->draw_number }}</td><td>{{ $item->quantity }}</td><td>Rs {{ number_format($item->line_total,2) }}</td></tr>@endforeach</tbody></table></div>
        @endif

        <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="3">{{ old('notes',$sale?->notes) }}</textarea></div>
    </div>
</form>
@if(!$sale)
<script>
let basketIndex=0,total=0;
document.getElementById('add-ticket').addEventListener('click',function(){
    const type=document.getElementById('ticket-type'), opt=type.options[type.selectedIndex], qty=parseInt(document.getElementById('ticket-qty').value || '1',10), date=document.getElementById('draw-date').value, price=parseFloat(opt.dataset.price), line=qty*price;
    const tr=document.createElement('tr'); tr.innerHTML=`<td>${opt.dataset.label}<input type="hidden" name="items[${basketIndex}][ticket_type_id]" value="${type.value}"></td><td>${qty}<input type="hidden" name="items[${basketIndex}][quantity]" value="${qty}"></td><td>${date || 'Auto'}<input type="hidden" name="items[${basketIndex}][draw_date]" value="${date}"></td><td data-line="${line}">Rs ${line.toFixed(2)}</td><td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>`;
    document.querySelector('#basket-table tbody').appendChild(tr); basketIndex++; total+=line; updateTotal();
});
document.addEventListener('click',function(e){if(e.target.closest('.remove-row')){const tr=e.target.closest('tr'); total-=parseFloat(tr.querySelector('[data-line]').dataset.line); tr.remove(); updateTotal();}});
document.getElementById('sale-form').addEventListener('submit',function(e){if(!document.querySelector('#basket-table tbody tr')){e.preventDefault(); alert('Please add at least one ticket to basket.');}});
function updateTotal(){document.getElementById('basket-total').textContent=Math.max(0,total).toFixed(2);}
</script>
@endif
@endsection
