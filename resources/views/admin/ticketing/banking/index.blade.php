@extends('admin.layouts.app')
@section('title','Banking')
@section('page-title','Banking')
@section('content')
<div class="card"><div class="card-header d-flex"><h3>Bank Accounts</h3><a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary btn-sm ml-auto"><i class="fas fa-plus"></i> Add Account</a></div><div class="card-body table-responsive"><table class="table datatable"><thead><tr><th>Name</th><th>Bank</th><th>UPI</th><th>QR</th><th>For Charges</th><th>Status</th><th></th></tr></thead><tbody>@foreach($accounts as $account)<tr><td>{{ $account->account_name }}</td><td>{{ $account->bank_name }}<br><small>{{ $account->account_number }}</small></td><td>{{ $account->upi_id }}</td><td>@if($account->qr_code)<img src="{{ Storage::url($account->qr_code) }}" style="height:44px;border-radius:6px">@else - @endif</td><td>{{ $account->show_for_charges ? 'Yes' : 'No' }}</td><td><span class="badge badge-info">{{ ucfirst($account->status) }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.bank-accounts.edit',$account) }}">Edit</a></td></tr>@endforeach</tbody></table>{{ $accounts->links() }}</div></div>
@endsection
