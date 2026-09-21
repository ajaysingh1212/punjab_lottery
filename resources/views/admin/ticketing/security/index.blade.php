@extends('admin.layouts.app')
@section('title','Security')
@section('page-title','Security')
@section('content')
<style>.security-card{border:0;border-radius:8px;box-shadow:0 14px 34px rgba(15,23,42,.08)}.security-head{background:#111827;color:#fff;border-radius:8px 8px 0 0;padding:18px 22px}.security-head h3{font-weight:900;margin:0}.form-control,.btn{border-radius:8px}.btn{font-weight:800}.table td,.table th{vertical-align:middle}</style>
<div class="row">
    <div class="col-lg-5">
        <form method="POST" action="{{ route('admin.security.ips.block') }}" class="card security-card">
            @csrf
            <div class="security-head"><h3><i class="fas fa-ban mr-2"></i>Block IP</h3></div>
            <div class="card-body">
                <label>IP Address</label><input name="ip_address" class="form-control mb-2" placeholder="127.0.0.1" required>
                <label>Reason</label><textarea name="reason" class="form-control mb-3" rows="3"></textarea>
                <button class="btn btn-danger"><i class="fas fa-lock mr-1"></i>Block & Logout Sessions</button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.security.devices.trust-current') }}" class="card security-card">
            @csrf
            <div class="security-head"><h3><i class="fas fa-laptop-code mr-2"></i>Trust Current Device</h3></div>
            <div class="card-body">
                <input name="device_name" class="form-control mb-2" value="{{ gethostname() ?: 'Admin device' }}">
                <input name="device_type" class="form-control mb-3" value="browser">
                <button class="btn btn-success"><i class="fas fa-shield-alt mr-1"></i>Mark Trusted</button>
            </div>
        </form>
    </div>
    <div class="col-lg-7">
        <div class="card security-card"><div class="security-head"><h3>Blocked IPs</h3></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>IP</th><th>Reason</th><th>Blocked</th><th></th></tr></thead><tbody>@forelse($blockedIps as $ip)<tr><td>{{ $ip->ip_address }}</td><td>{{ $ip->reason }}</td><td>{{ $ip->blocked_at ? \Illuminate\Support\Carbon::parse($ip->blocked_at)->format('d M Y h:i A') : '-' }}</td><td><form method="POST" action="{{ route('admin.security.ips.unblock',$ip->ip_address) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-success">Unblock</button></form></td></tr>@empty<tr><td colspan="4" class="text-muted">No blocked IPs.</td></tr>@endforelse</tbody></table>{{ $blockedIps->links() }}</div></div>
    </div>
</div>
<div class="card security-card"><div class="security-head"><h3>Trusted Devices</h3></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Device</th><th>Type</th><th>IP/Platform</th><th>Status</th><th>Last Used</th><th></th></tr></thead><tbody>@forelse($devices as $device)<tr><td>{{ $device->device_name }}</td><td>{{ $device->device_type }}</td><td>{{ $device->platform }}</td><td><span class="badge badge-info">{{ ucfirst($device->status) }}</span></td><td>{{ $device->last_used_at ? \Illuminate\Support\Carbon::parse($device->last_used_at)->format('d M Y h:i A') : '-' }}</td><td><form method="POST" action="{{ route('admin.security.devices.revoke',$device->id) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger">Revoke</button></form></td></tr>@empty<tr><td colspan="6" class="text-muted">No trusted devices.</td></tr>@endforelse</tbody></table>{{ $devices->links() }}</div></div>
<div class="row">
    <div class="col-lg-6"><div class="card security-card"><div class="security-head"><h3>Recent Login Activity</h3></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Email</th><th>IP</th><th>Status</th><th>Time</th></tr></thead><tbody>@foreach($loginActivities as $activity)<tr><td>{{ $activity->email }}</td><td>{{ $activity->ip_address }}</td><td><span class="badge badge-secondary">{{ ucfirst($activity->status) }}</span></td><td>{{ $activity->created_at ? \Illuminate\Support\Carbon::parse($activity->created_at)->format('d M Y h:i A') : '-' }}</td></tr>@endforeach</tbody></table></div></div></div>
    <div class="col-lg-6"><div class="card security-card"><div class="security-head"><h3>Audit Log</h3></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Action</th><th>IP</th><th>Time</th></tr></thead><tbody>@foreach($logs as $log)<tr><td>{{ $log->action }}</td><td>{{ $log->ip_address }}</td><td>{{ $log->created_at->format('d M Y h:i A') }}</td></tr>@endforeach</tbody></table></div></div></div>
</div>
@endsection
