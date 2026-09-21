@extends('admin.layouts.app')
@section('title','Draws')
@section('page-title','Draws')
@section('content')
<style>.draw-card{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.draw-card .card-header{background:#102a43;color:#fff;border-radius:8px 8px 0 0}.nav-tabs{border:0;gap:8px}.nav-tabs .nav-link{border:1px solid #e2e8f0;border-radius:8px;font-weight:800;color:#334155}.nav-tabs .nav-link.active{background:#0f766e;color:#fff;border-color:#0f766e}.stat{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px}.stat strong{font-size:24px}.btn{border-radius:8px;font-weight:800}</style>
<div class="card draw-card">
    <div class="card-header d-flex justify-content-between align-items-center"><h3 class="mb-0"><i class="fas fa-dice mr-2"></i>Draw Control Room</h3><span>Daily, weekly, monthly, and festival pools</span></div>
    <div class="card-body">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><a class="nav-link {{ request('frequency') ? '' : 'active' }}" href="{{ route('admin.draws.index') }}">All</a></li>
            @foreach(['daily','weekly','monthly','festival'] as $freq)
                <li class="nav-item"><a class="nav-link {{ request('frequency')===$freq ? 'active' : '' }}" href="{{ route('admin.draws.index',['frequency'=>$freq]) }}">{{ ucfirst($freq) }}</a></li>
            @endforeach
        </ul>
        <div class="row mb-3">
            @foreach(['daily','weekly','monthly','festival'] as $freq)
                <div class="col-md-3 mb-2"><div class="stat"><span class="text-muted">{{ ucfirst($freq) }}</span><br><strong>{{ $tabStats[$freq]['customers'] }}</strong> customers<br><small>{{ $tabStats[$freq]['tickets'] }} tickets sold | {{ $tabStats[$freq]['today'] }} today draws</small></div></div>
            @endforeach
        </div>
        <form class="row mb-3">
            <input type="hidden" name="frequency" value="{{ request('frequency') }}">
            <div class="col-md-3"><input type="date" name="date" value="{{ request('date') }}" class="form-control"></div>
            <div class="col-md-3"><select name="status" class="form-control"><option value="">All Status</option>@foreach(['scheduled','in_progress','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="col-md-3"><button class="btn btn-primary"><i class="fas fa-filter mr-1"></i>Filter</button></div>
        </form>
        <div class="table-responsive"><table class="table datatable"><thead><tr><th>Draw</th><th>Type</th><th>Frequency</th><th>Date</th><th>Status</th><th>Customers</th><th>Tickets</th><th>Winners</th><th></th></tr></thead><tbody>
            @foreach($draws as $draw)<tr><td>{{ $draw->draw_number }}</td><td>{{ $draw->ticketType->name }}</td><td>{{ ucfirst($draw->ticketType->frequency) }}</td><td>{{ $draw->draw_date->format('d M Y') }}</td><td><span class="badge badge-info">{{ ucfirst($draw->status) }}</span></td><td>{{ $draw->total_customers }}</td><td>{{ $draw->total_tickets }}</td><td>{{ $draw->winners->count() }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.draws.show',$draw) }}"><i class="fas fa-play mr-1"></i>Open Draw</a></td></tr>@endforeach
        </tbody></table>{{ $draws->links() }}</div>
    </div>
</div>
@endsection
