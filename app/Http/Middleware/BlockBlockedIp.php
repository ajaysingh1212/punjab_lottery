<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BlockBlockedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        if (DB::table('blocked_ips')->where('ip_address', $request->ip())->exists()) {
            DB::table('login_activities')->insert([
                'user_id' => optional($request->user())->id,
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'blocked',
                'session_id' => $request->session()->getId(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            abort(403, 'This IP address is blocked.');
        }

        return $next($request);
    }
}
