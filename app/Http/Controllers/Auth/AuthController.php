<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) return redirect()->route('admin.dashboard');
        return view('auth.login');
    }
    public function showLogin()
    {
        SiteSetting::ensureDefaults();
        return view('auth.login', [
            'landing' => [
                'kicker' => SiteSetting::get('landing_kicker'),
                'title' => SiteSetting::get('landing_title'),
                'subtitle' => SiteSetting::get('landing_subtitle'),
                'bumper_title' => SiteSetting::get('landing_bumper_title'),
                'bumper_prize' => SiteSetting::get('landing_bumper_prize'),
                'bumper_date' => SiteSetting::get('landing_bumper_date'),
                'result_text' => SiteSetting::get('landing_result_text'),
                'footer_text' => SiteSetting::get('landing_footer_text'),
                'whatsapp' => SiteSetting::get('customer_support_whatsapp'),
                'call' => SiteSetting::get('customer_support_call'),
                'address' => SiteSetting::get('customer_support_address'),
                'logo' => SiteSetting::get('customer_dashboard_logo') ?: SiteSetting::get('site_logo'),
            ],
            'ticketTypes' => Schema::hasTable('ticket_types') ? TicketType::with('prizes')->where('is_active', true)->latest()->take(12)->get() : collect(),
        ]);
    }
    public function login(Request $request)
    {
        // dd($request->all());
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email',$credentials['email'])->first();

        if (!$user) {
            $this->logLogin($request, null, 'failed');
            return back()->withErrors(['email' => 'No account found with this email.'])->withInput();
        }

        if (!$user->is_active) {
            $this->logLogin($request, $user, 'failed');
            return back()->withErrors(['email' => 'Your account is deactivated. Contact administrator.'])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            $this->logLogin($request, $user, 'success');
            return redirect()->intended(route('dashboard'));
        }

        $this->logLogin($request, $user, 'failed');
        return back()->withErrors(['password' => 'Incorrect password.'])->withInput();
    }

    public function logout(Request $request)
    {
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'action' => 'auth.logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Logged out successfully.');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users|alpha_dash',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'] ?? str($data['email'])->before('@')->slug().random_int(1000, 9999),
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (\App\Models\Role::where('name', 'customer')->exists()) {
            $user->assignRole('customer');
        }

        Auth::login($user);
        $this->logLogin($request, $user, 'success');

        return redirect()->route('dashboard')->with('success', 'Welcome! Account created successfully.');
    }

    private function logLogin(Request $request, ?User $user, string $status): void
    {
        DB::table('login_activities')->insert([
            'user_id' => $user?->id,
            'email' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $status,
            'session_id' => $request->session()->getId(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
