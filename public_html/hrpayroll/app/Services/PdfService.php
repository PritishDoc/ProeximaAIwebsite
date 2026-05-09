<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function generatePayslip(Payroll $payroll): Payslip
    {
        $payroll->load(['employee.department', 'company', 'employee.user']);

        $pdf = Pdf::loadView('pdf.payslip', compact('payroll'))
            ->setPaper('a4', 'portrait');

        $filename = 'payslip_' . $payroll->employee_id . '_' . $payroll->year . '_' . str_pad($payroll->month, 2, '0', STR_PAD_LEFT) . '.pdf';
        $path     = 'payslips/' . $payroll->company_id . '/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        $payslip = Payslip::updateOrCreate(
            ['payroll_id' => $payroll->id],
            [
                'company_id'   => $payroll->company_id,
                'employee_id'  => $payroll->employee_id,
                'pdf_path'     => $path,
                'pdf_filename' => $filename,
                'generated_at' => now(),
            ]
        );

        return $payslip;
    }

    public function streamPayslip(Payroll $payroll): \Symfony\Component\HttpFoundation\Response
    {
        $payroll->load(['employee.department', 'company']);

        return Pdf::loadView('pdf.payslip', compact('payroll'))
            ->setPaper('a4', 'portrait')
            ->stream('payslip.pdf');
    }
}
