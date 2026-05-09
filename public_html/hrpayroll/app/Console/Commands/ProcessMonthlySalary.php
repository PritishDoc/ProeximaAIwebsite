<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Services\PayrollCalculationService;
use App\Services\PdfService;

class ProcessMonthlySalary extends Command
{
    protected $signature = 'payroll:process-monthly';
    protected $description = 'Automatically process monthly salary for active companies on the 1st of every month (for the previous month)';

    public function handle(PayrollCalculationService $calculator, PdfService $pdfService)
    {
        $this->info('Starting monthly salary processing...');

        $month = now()->subMonth()->month;
        $year  = now()->subMonth()->year;

        $companies = Company::where('status', 'active')->get();

        foreach ($companies as $company) {
            $this->info("Processing payroll for company: {$company->name}");
            $employees = Employee::forCompany($company->id)->active()->get();

            foreach ($employees as $employee) {
                // Skip if already processed
                if (Payroll::forCompany($company->id)->forMonth($month, $year)->where('employee_id', $employee->id)->exists()) {
                    continue;
                }

                $workingDays = $this->getWorkingDays($month, $year);
                $presentDays = Attendance::forCompany($company->id)
                    ->where('employee_id', $employee->id)
                    ->forMonth($month, $year)
                    ->whereIn('status', ['present', 'half_day'])
                    ->count();

                $overtimeHours = Attendance::forCompany($company->id)
                    ->where('employee_id', $employee->id)
                    ->forMonth($month, $year)
                    ->sum('overtime_hours');

                $calc = $calculator->calculate(
                    $employee->toArray(),
                    $presentDays ?: $workingDays, // if no attendance marked, assume full month
                    $workingDays,
                    (float) $overtimeHours,
                    0 // no bonus in auto run
                );

                $payroll = Payroll::create(array_merge($calc, [
                    'company_id'  => $company->id,
                    'employee_id' => $employee->id,
                    'month'       => $month,
                    'year'        => $year,
                    'leave_days'  => Attendance::forCompany($company->id)->where('employee_id', $employee->id)->forMonth($month, $year)->where('status', 'leave')->count(),
                    'status'      => 'processed',
                ]));

                $pdfService->generatePayslip($payroll);
            }
        }

        $this->info('Monthly salary processing completed.');
    }

    private function getWorkingDays(int $month, int $year): int
    {
        $start = \Carbon\Carbon::create($year, $month, 1);
        $end   = $start->copy()->endOfMonth();
        $days  = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) $days++;
            $start->addDay();
        }
        return $days;
    }
}
