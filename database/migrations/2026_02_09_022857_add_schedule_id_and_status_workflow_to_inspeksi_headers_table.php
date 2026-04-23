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
        Schema::table('inspeksi_headers', function (Blueprint $table) {
            if (!Schema::hasColumn('inspeksi_headers', 'schedule_id')) {
                $table->foreignId('schedule_id')->nullable();
            }
            if (!Schema::hasColumn('inspeksi_headers', 'status_workflow')) {
                $table->enum('status_workflow', ['draft', 'pending_ro', 'pending_pusat', 'approved', 'rejected'])->default('draft');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspeksi_headers', function (Blueprint $table) {
            if (Schema::hasColumn('inspeksi_headers', 'schedule_id')) {
                $table->dropColumn('schedule_id');
            }
            if (Schema::hasColumn('inspeksi_headers', 'status_workflow')) {
                $table->dropColumn('status_workflow');
            }
        });
    }
};
