<?php

namespace App\Mail;

use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Payroll $payroll)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Payslip for {$this->payroll->month_name} {$this->payroll->year}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payslip',
            with: [
                'employeeName' => $this->payroll->employee->full_name,
                'month' => $this->payroll->month_name,
                'year' => $this->payroll->year,
                'companyName' => $this->payroll->company->name,
                'netPay' => $this->payroll->net_salary,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $payslip = $this->payroll->payslip;
        if ($payslip && \Illuminate\Support\Facades\Storage::disk('public')->exists($payslip->pdf_path)) {
            return [
                Attachment::fromStorageDisk('public', $payslip->pdf_path)
                    ->as("Payslip_{$this->payroll->month_name}_{$this->payroll->year}.pdf")
                    ->withMime('application/pdf'),
            ];
        }

        // Fallback: If for some reason the file doesn't exist on disk, we could theoretically generate it here,
        // but since Payroll process explicitly generates it via PdfService, it should always exist.
        return [];
    }
}
