<?php

namespace App\Http\Controllers\Admin\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.ticketing.security.index', [
            'blockedIps' => DB::table('blocked_ips')->latest()->paginate(15, ['*'], 'ips'),
            'devices' => DB::table('trusted_devices')
                ->when(!auth()->user()?->isSuperAdmin(), fn ($q) => $q->where('admin_id', auth()->id()))
                ->latest()
                ->paginate(15, ['*'], 'devices'),
            'loginActivities' => DB::table('login_activities')->latest()->take(50)->get(),
            'logs' => AuditLog::latest()->take(50)->get(),
        ]);
    }

    public function blockIp(Request $request)
    {
        $data = $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::table('blocked_ips')->updateOrInsert(
            ['ip_address' => $data['ip_address']],
            ['blocked_by' => auth()->id(), 'reason' => $data['reason'] ?? null, 'blocked_at' => now(), 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('sessions')->where('ip_address', $data['ip_address'])->delete();

        return back()->with('success', 'IP blocked and active sessions invalidated.');
    }

    public function unblockIp(string $ip)
    {
        DB::table('blocked_ips')->where('ip_address', $ip)->delete();
        return back()->with('success', 'IP unblocked.');
    }

    public function trustCurrentDevice(Request $request)
    {
        $token = Str::random(80);
        DB::table('trusted_devices')->insert([
            'admin_id' => auth()->id(),
            'device_name' => $request->input('device_name', gethostname() ?: 'Admin device'),
            'device_type' => $request->input('device_type', 'browser'),
            'token_hash' => hash('sha256', $token),
            'browser' => substr((string) $request->userAgent(), 0, 190),
            'platform' => $request->ip(),
            'status' => 'trusted',
            'last_used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->withCookie(Cookie::make('trusted_device_token', $token, 60 * 24 * 365, null, null, false, true))->with('success', 'Current device trusted.');
    }

    public function revokeDevice(int $device)
    {
        DB::table('trusted_devices')
            ->where('id', $device)
            ->when(!auth()->user()?->isSuperAdmin(), fn ($q) => $q->where('admin_id', auth()->id()))
            ->update(['status' => 'revoked', 'updated_at' => now()]);

        return back()->with('success', 'Device revoked.');
    }
}
