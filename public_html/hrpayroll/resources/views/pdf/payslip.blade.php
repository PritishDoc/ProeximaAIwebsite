<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payroll->employee->full_name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #4f46e5; margin: 0 0 5px 0; }
        .payslip-title { font-size: 18px; color: #666; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .details-table, .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table td { padding: 8px; border: 1px solid #eee; }
        .details-table td.label { font-weight: bold; background: #f9f9f9; width: 20%; }
        .salary-table th { background: #4f46e5; color: #fff; padding: 10px; text-align: left; }
        .salary-table td { padding: 10px; border: 1px solid #eee; }
        .amount { text-align: right !important; }
        .total-row td { font-weight: bold; background: #f1f5f9; }
        .net-pay { font-size: 18px; font-weight: bold; color: #16a34a; }
        .footer { text-align: center; font-size: 12px; color: #888; margin-top: 50px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="company-name">{{ $payroll->company->name }}</h1>
        <h2 class="payslip-title">Payslip for {{ $payroll->month_name }} {{ $payroll->year }}</h2>
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Employee Name</td>
            <td>{{ $payroll->employee->full_name }}</td>
            <td class="label">Employee ID</td>
            <td>{{ $payroll->employee->employee_id }}</td>
        </tr>
        <tr>
            <td class="label">Designation</td>
            <td>{{ $payroll->employee->designation ?? 'N/A' }}</td>
            <td class="label">Department</td>
            <td>{{ $payroll->employee->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">PAN Number</td>
            <td>{{ $payroll->employee->pan_number ?? 'N/A' }}</td>
            <td class="label">Bank A/C</td>
            <td>{{ $payroll->employee->bank_account ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Total Days</td>
            <td>{{ $payroll->working_days }}</td>
            <td class="label">Paid Days</td>
            <td>{{ $payroll->present_days }}</td>
        </tr>
    </table>

    <table class="salary-table">
        <thead>
            <tr>
                <th width="50%">Earnings</th>
                <th width="50%">Deductions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding:0; border:none; vertical-align:top;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr><td style="border:none;">Basic Salary</td><td class="amount" style="border:none;">{{ number_format($payroll->basic_pay, 2) }}</td></tr>
                        <tr><td style="border:none;">HRA</td><td class="amount" style="border:none;">{{ number_format($payroll->hra, 2) }}</td></tr>
                        <tr><td style="border:none;">Allowances</td><td class="amount" style="border:none;">{{ number_format($payroll->allowances, 2) }}</td></tr>
                        <tr><td style="border:none;">Overtime Pay</td><td class="amount" style="border:none;">{{ number_format($payroll->overtime_pay, 2) }}</td></tr>
                        <tr><td style="border:none;">Bonus</td><td class="amount" style="border:none;">{{ number_format($payroll->bonus, 2) }}</td></tr>
                    </table>
                </td>
                <td style="padding:0; border:none; vertical-align:top; border-left:1px solid #eee;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr><td style="border:none;">PF (Employee)</td><td class="amount" style="border:none;">{{ number_format($payroll->pf_deduction, 2) }}</td></tr>
                        <tr><td style="border:none;">ESI</td><td class="amount" style="border:none;">{{ number_format($payroll->esi_deduction, 2) }}</td></tr>
                        <tr><td style="border:none;">Tax (TDS)</td><td class="amount" style="border:none;">{{ number_format($payroll->tax_deduction, 2) }}</td></tr>
                        <tr><td style="border:none;">Other Deductions</td><td class="amount" style="border:none;">{{ number_format($payroll->other_deductions, 2) }}</td></tr>
                    </table>
                </td>
            </tr>
            <tr class="total-row">
                <td style="border-right:1px solid #eee;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr><td style="border:none; padding:0;">Total Earnings</td><td class="amount" style="border:none; padding:0;">{{ number_format($payroll->gross_salary, 2) }}</td></tr>
                    </table>
                </td>
                <td>
                    <table style="width:100%; border-collapse:collapse;">
                        <tr><td style="border:none; padding:0;">Total Deductions</td><td class="amount" style="border:none; padding:0;">{{ number_format($payroll->total_deductions, 2) }}</td></tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="details-table" style="margin-top: 20px;">
        <tr>
            <td class="label" style="font-size: 16px;">Net Salary Payable</td>
            <td class="amount net-pay">INR {{ number_format($payroll->net_salary, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
