<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('payroll_id')->constrained('payroll')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('pdf_path')->nullable();
            $table->string('pdf_filename')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->boolean('is_emailed')->default(false);
            $table->timestamps();

            $table->index('company_id');
            $table->index('payroll_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
