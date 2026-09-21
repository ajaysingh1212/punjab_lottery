<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequireTrustedDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $request->cookie('trusted_device_token');

        if (!$user || !$user->hasAnyRole(['super-admin', 'admin'])) {
            return $next($request);
        }

        $trusted = $token && DB::table('trusted_devices')
            ->where('admin_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('status', 'trusted')
            ->exists();

        if (!$trusted) {
            DB::table('login_activities')->insert([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'device_rejected',
                'session_id' => $request->session()->getId(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            abort(403, 'This admin device is not trusted.');
        }

        DB::table('trusted_devices')->where('token_hash', hash('sha256', $token))->update(['last_used_at' => now()]);

        return $next($request);
    }
}
