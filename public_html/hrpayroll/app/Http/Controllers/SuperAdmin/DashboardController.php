<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_companies'   => Company::count(),
            'active_companies'  => Company::where('status', 'active')->count(),
            'total_revenue'     => Payment::where('status', 'success')->sum('amount'),
            'total_subscriptions'=> Subscription::where('status', 'active')->count(),
        ];

        $recentCompanies = Company::with('plan')->latest()->take(5)->get();
        $recentPayments  = Payment::with(['company', 'subscription.plan'])->latest()->take(5)->get();

        // Revenue chart
        $revenueChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenueChart[] = [
                'month' => $date->format('M Y'),
                'total' => Payment::where('status', 'success')
                            ->whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year)
                            ->sum('amount'),
            ];
        }

        return view('superadmin.dashboard', compact('stats', 'recentCompanies', 'recentPayments', 'revenueChart'));
    }

    public function companies(Request $request)
    {
        $companies = Company::with('plan')->latest()->paginate(20);
        return view('superadmin.companies.index', compact('companies'));
    }

    public function toggleCompanyStatus(Company $company)
    {
        $company->update([
            'status' => $company->status === 'active' ? 'suspended' : 'active'
        ]);
        return back()->with('success', 'Company status updated.');
    }
}
