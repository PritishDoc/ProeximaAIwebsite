<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px;}
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Payroll Report - {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</h2>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Basic</th>
                <th>HRA</th>
                <th>Allowance</th>
                <th>Overtime</th>
                <th>Gross</th>
                <th>PF</th>
                <th>ESI</th>
                <th>Tax</th>
                <th>Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrolls as $payroll)
            <tr>
                <td>{{ $payroll->employee->full_name }}</td>
                <td>{{ $payroll->basic_pay }}</td>
                <td>{{ $payroll->hra }}</td>
                <td>{{ $payroll->allowances }}</td>
                <td>{{ $payroll->overtime_pay }}</td>
                <td>{{ $payroll->gross_salary }}</td>
                <td>{{ $payroll->pf_deduction }}</td>
                <td>{{ $payroll->esi_deduction }}</td>
                <td>{{ $payroll->tax_deduction }}</td>
                <td><strong>{{ $payroll->net_salary }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
