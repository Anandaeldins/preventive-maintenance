<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!$this->canManageForeignKey()) {
            return;
        }

        if (!$this->hasScheduleForeignKey()) {
            Schema::table('inspeksi_headers', function (Blueprint $table) {
                $table->foreign('schedule_id')
                    ->references('id')
                    ->on('pm_schedules')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!$this->canManageForeignKey()) {
            return;
        }

        if ($this->hasScheduleForeignKey()) {
            Schema::table('inspeksi_headers', function (Blueprint $table) {
                $table->dropForeign('inspeksi_headers_schedule_id_foreign');
            });
        }
    }

    private function canManageForeignKey(): bool
    {
        return Schema::hasTable('inspeksi_headers')
            && Schema::hasTable('pm_schedules')
            && Schema::hasColumn('inspeksi_headers', 'schedule_id');
    }

    private function hasScheduleForeignKey(): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', 'inspeksi_headers')
            ->where('constraint_name', 'inspeksi_headers_schedule_id_foreign')
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
