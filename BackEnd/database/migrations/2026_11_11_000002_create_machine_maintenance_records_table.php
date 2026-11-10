<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Service history per machine: who worked on it, what was wrong, what was
    // done, and who closed it (A06). Separate from the kiosk "maintenance
    // shutdown" action, which stays as it is.
    public function up(): void
    {
        Schema::create('machine_maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('rvm_machines')->cascadeOnDelete();
            $table->string('technician_name', 100);
            $table->text('issue');
            $table->text('action_taken')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_maintenance_records');
    }
};
