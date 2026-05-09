<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->tinyInteger('month'); // 1-12
            $table->year('year');

            // Earnings
            $table->decimal('basic_pay', 12, 2)->default(0);
            $table->decimal('hra', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);

            // Deductions
            $table->decimal('pf_deduction', 12, 2)->default(0);   // Employee PF 12%
            $table->decimal('esi_deduction', 12, 2)->default(0);  // Employee ESI 0.75%
            $table->decimal('tax_deduction', 12, 2)->default(0);  // TDS
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);

            // Net
            $table->decimal('net_salary', 12, 2)->default(0);

            // Attendance summary
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);

            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'employee_id', 'month', 'year']);
            $table->index(['company_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
