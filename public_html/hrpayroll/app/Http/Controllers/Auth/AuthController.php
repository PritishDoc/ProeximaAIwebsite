<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\Plan;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            AuditLog::record('login', 'User', $user->id, [], [], 'User logged in');

            return match($user->role) {
                'super_admin' => redirect()->route('superadmin.dashboard'),
                'admin'       => redirect()->route('admin.dashboard'),
                'employee'    => redirect()->route('employee.dashboard'),
                default       => redirect('/'),
            };
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput($request->only('email'));
    }

    public function showRegister()
    {
        $plans = Plan::active()->get();
        return view('auth.register', compact('plans'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'company_email'=> 'required|email|unique:companies,email',
            'company_phone'=> 'nullable|string|max:15',
            'industry'     => 'nullable|string|max:50',
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email',
            'password'     => ['required', 'confirmed', Password::min(8)],
            'plan_id'      => 'required|exists:plans,id',
        ]);

        $company = Company::create([
            'name'    => $request->company_name,
            'email'   => $request->company_email,
            'phone'   => $request->company_phone,
            'industry'=> $request->industry,
            'status'  => 'pending',
            'plan_id' => $request->plan_id,
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'admin',
        ]);

        AuditLog::record('company_registered', 'Company', $company->id, [], $company->toArray(), 'New company registered');

        return redirect()->route('payment.plans', $company->id)
            ->with('success', 'Company registered! Please choose a plan and pay to activate.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        AuditLog::record('logout', 'User', $user?->id, [], [], 'User logged out');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
