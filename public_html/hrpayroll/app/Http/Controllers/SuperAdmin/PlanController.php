<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('superadmin.plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price_monthly'  => 'required|numeric|min:0',
            'price_yearly'   => 'required|numeric|min:0',
            'employee_limit' => 'required|integer|min:1',
            'features'       => 'nullable|array',
            'sort_order'     => 'required|integer',
            'is_active'      => 'boolean',
        ]);

        $data = $request->all();
        $data['slug'] = \Illuminate\Support\Str::slug($request->name);
        $data['is_active'] = $request->has('is_active');

        Plan::create($data);

        return redirect()->route('superadmin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('superadmin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price_monthly'  => 'required|numeric|min:0',
            'price_yearly'   => 'required|numeric|min:0',
            'employee_limit' => 'required|integer|min:1',
            'features'       => 'nullable|array',
            'sort_order'     => 'required|integer',
        ]);

        $data = $request->all();
        $data['slug'] = \Illuminate\Support\Str::slug($request->name);
        $data['is_active'] = $request->has('is_active');

        $plan->update($data);

        return redirect()->route('superadmin.plans.index')->with('success', 'Plan updated successfully.');
    }
}
