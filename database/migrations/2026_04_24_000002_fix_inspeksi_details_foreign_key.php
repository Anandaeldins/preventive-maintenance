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

        if ($this->hasLegacyForeignKey()) {
            Schema::table('inspeksi_details', function (Blueprint $table) {
                $table->dropForeign('inspeksi_details_inspeksi_id_foreign');
            });
        }

        if (!$this->hasCurrentForeignKey()) {
            Schema::table('inspeksi_details', function (Blueprint $table) {
                $table->foreign('inspeksi_id')
                    ->references('id')
                    ->on('inspeksi_headers')
                    ->onDelete('cascade');
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

        if ($this->hasCurrentForeignKey()) {
            Schema::table('inspeksi_details', function (Blueprint $table) {
                $table->dropForeign('inspeksi_details_inspeksi_id_foreign');
            });
        }

        if (!$this->hasLegacyForeignKey() && Schema::hasTable('inspeksis')) {
            Schema::table('inspeksi_details', function (Blueprint $table) {
                $table->foreign('inspeksi_id')
                    ->references('id')
                    ->on('inspeksis')
                    ->onDelete('cascade');
            });
        }
    }

    private function canManageForeignKey(): bool
    {
        return Schema::hasTable('inspeksi_details')
            && Schema::hasColumn('inspeksi_details', 'inspeksi_id');
    }

    private function hasLegacyForeignKey(): bool
    {
        return DB::table('information_schema.referential_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('constraint_name', 'inspeksi_details_inspeksi_id_foreign')
            ->where('referenced_table_name', 'inspeksis')
            ->exists();
    }

    private function hasCurrentForeignKey(): bool
    {
        return DB::table('information_schema.referential_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('constraint_name', 'inspeksi_details_inspeksi_id_foreign')
            ->where('referenced_table_name', 'inspeksi_headers')
            ->exists();
    }
};
