<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->string('machine_type', 20)->default('rvm')->index();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
        });

        $groups = DB::table('rvm_machines')
            ->whereNotNull('location_name')
            ->where('location_name', '<>', '')
            ->orderBy('id')
            ->get(['id', 'location_name', 'latitude', 'longitude'])
            ->groupBy('location_name');

        DB::transaction(function () use ($groups) {
            foreach ($groups as $machines) {
                $coordinate = $machines->first(fn ($machine) => $machine->latitude !== null && $machine->longitude !== null);
                $locationId = DB::table('locations')->insertGetId([
                    'name' => $machines->first()->location_name,
                    'latitude' => $coordinate?->latitude,
                    'longitude' => $coordinate?->longitude,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('rvm_machines')->whereIn('id', $machines->pluck('id'))
                    ->update(['location_id' => $locationId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn('machine_type');
        });

        Schema::dropIfExists('locations');
    }
};
