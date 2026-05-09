<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('login_time')->nullable();
            $table->time('logout_time')->nullable();
            $table->decimal('working_hours', 4, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'half_day', 'holiday', 'leave', 'weekend'])->default('present');
            $table->text('remarks')->nullable();
            $table->boolean('is_overtime')->default(false);
            $table->decimal('overtime_hours', 4, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'employee_id', 'date']);
            $table->index(['company_id', 'date']);
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
