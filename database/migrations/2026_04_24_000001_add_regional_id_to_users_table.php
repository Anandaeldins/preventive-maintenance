<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('regionals')) {
            return;
        }

        if (!Schema::hasColumn('users', 'regional_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('regional_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('regionals')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'regional_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('regional_id');
        });
    }
};
