<?php

namespace App\Services;

class PayrollCalculationService
{
    // Constants per Indian labor law
    const PF_RATE          = 0.12;  // 12% of basic (employee)
    const ESI_RATE         = 0.0075; // 0.75% of gross (employee) if gross <= 21000
    const ESI_THRESHOLD    = 21000;
    const OVERTIME_RATE    = 2.0;   // 2x hourly rate

    public function calculate(array $employee, int $presentDays, int $workingDays, float $overtimeHours = 0, float $bonus = 0): array
    {
        $basic    = (float) $employee['basic_salary'];
        $hra      = (float) $employee['hra'];
        $allow    = (float) $employee['allowances'];

        // Pro-rate if absent days
        $ratio = $workingDays > 0 ? $presentDays / $workingDays : 1;

        $proBasic = round($basic * $ratio, 2);
        $proHra   = round($hra * $ratio, 2);
        $proAllow = round($allow * $ratio, 2);

        // Overtime
        $hourlyRate  = $basic / (26 * 8); // 26 working days, 8 hrs
        $overtimePay = round($hourlyRate * $overtimeHours * self::OVERTIME_RATE, 2);

        $grossSalary = $proBasic + $proHra + $proAllow + $overtimePay + $bonus;

        // Deductions
        $pfDeduction  = round($proBasic * self::PF_RATE, 2);
        $esiDeduction = $grossSalary <= self::ESI_THRESHOLD ? round($grossSalary * self::ESI_RATE, 2) : 0;
        $taxDeduction = $this->calculateTDS($grossSalary * 12); // annualised

        $totalDeductions = $pfDeduction + $esiDeduction + $taxDeduction;
        $netSalary       = round($grossSalary - $totalDeductions, 2);

        return [
            'basic_pay'        => $proBasic,
            'hra'              => $proHra,
            'allowances'       => $proAllow,
            'bonus'            => $bonus,
            'overtime_pay'     => $overtimePay,
            'gross_salary'     => round($grossSalary, 2),
            'pf_deduction'     => $pfDeduction,
            'esi_deduction'    => $esiDeduction,
            'tax_deduction'    => $taxDeduction,
            'other_deductions' => 0,
            'total_deductions' => round($totalDeductions, 2),
            'net_salary'       => $netSalary,
            'working_days'     => $workingDays,
            'present_days'     => $presentDays,
            'absent_days'      => $workingDays - $presentDays,
            'overtime_hours'   => $overtimeHours,
        ];
    }

    /**
     * Simple slab-based TDS (monthly amount from annual estimate)
     */
    private function calculateTDS(float $annualSalary): float
    {
        if ($annualSalary <= 250000) {
            $annualTax = 0;
        } elseif ($annualSalary <= 500000) {
            $annualTax = ($annualSalary - 250000) * 0.05;
        } elseif ($annualSalary <= 1000000) {
            $annualTax = 12500 + ($annualSalary - 500000) * 0.20;
        } else {
            $annualTax = 112500 + ($annualSalary - 1000000) * 0.30;
        }

        return round($annualTax / 12, 2);
    }
}
