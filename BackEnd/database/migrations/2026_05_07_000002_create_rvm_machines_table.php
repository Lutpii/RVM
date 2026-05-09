<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rvm_machines', function (Blueprint $table) {
            $table->id();
            $table->string('machine_code')->unique();
            $table->string('name');
            $table->string('location_name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedTinyInteger('aluminum_level')->default(0);
            $table->unsignedTinyInteger('plastic_level')->default(0);
            $table->unsignedTinyInteger('glass_level')->default(0);
            $table->unsignedTinyInteger('paper_level')->default(0);
            $table->timestamps();
        });

        // Seed one default machine so QR generation works immediately
        DB::table('rvm_machines')->insert([
            'machine_code'   => 'RVM-001',
            'name'           => 'RVM Machine 1',
            'location_name'  => 'Main Lobby',
            'status'         => 'active',
            'aluminum_level' => 0,
            'plastic_level'  => 0,
            'glass_level'    => 0,
            'paper_level'    => 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rvm_machines');
    }
};
