<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin bypasses tenant checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (!$user->company_id) {
            abort(403, 'No company associated with this account.');
        }

        $company = $user->company;

        if (!$company || $company->status === 'suspended') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['account' => 'Your account has been suspended. Please contact support.']);
        }

        if ($company->status === 'expired') {
            return redirect()->route('subscription.expired');
        }

        // Store current company in app context
        app()->instance('currentCompany', $company);
        view()->share('currentCompany', $company);

        return $next($request);
    }
}
