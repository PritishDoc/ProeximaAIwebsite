<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .highlight {
            background-color: #f1f5f9;
            padding: 15px;
            border-left: 4px solid #4f46e5;
            margin: 20px 0;
            font-size: 16px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $companyName }}</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $employeeName }}</strong>,</p>
            
            <p>We are pleased to inform you that your salary for <strong>{{ $month }} {{ $year }}</strong> has been fully credited to your designated bank account.</p>
            
            <div class="highlight">
                <strong>Net Pay Credited:</strong> INR {{ number_format($netPay, 2) }}
            </div>
            
            <p>Please find your official secure PDF Payslip attached to this email for your records and reference.</p>
            
            <p>If you have any questions regarding your payroll, deductions, or taxes, please do not hesitate to contact the HR department or reply directly to this email.</p>
            
            <p>Best Regards,<br>
            <strong>HR & Payroll Team</strong><br>
            {{ $companyName }}</p>
        </div>
        <div class="footer">
            This is an automatically generated email. Please do not reply unless regarding a payroll discrepancy.
        </div>
    </div>
</body>
</html>
