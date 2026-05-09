<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        
        $holidays = Holiday::forCompany($this->companyId())
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();
            
        return view('admin.holidays.index', compact('holidays', 'year'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date'
        ]);

        $exists = Holiday::forCompany($this->companyId())->where('date', $request->date)->exists();
        if ($exists) {
            return back()->withErrors(['date' => 'A holiday has already been defined for this exact date.']);
        }

        $holiday = Holiday::create([
            'company_id' => $this->companyId(),
            'name' => $request->name,
            'date' => $request->date,
        ]);

        AuditLog::record('created', 'Holiday', $holiday->id, [], $holiday->toArray(), "Holiday {$holiday->name} added");

        return back()->with('success', "Holiday '{$holiday->name}' added successfully.");
    }

    public function destroy(Holiday $holiday)
    {
        if ($holiday->company_id !== $this->companyId()) {
            abort(403);
        }

        $name = $holiday->name;
        $holiday->delete();

        AuditLog::record('deleted', 'Holiday', $holiday->id, [], [], "Holiday {$name} deleted");

        return back()->with('success', "Holiday '{$name}' deleted successfully.");
    }
}
