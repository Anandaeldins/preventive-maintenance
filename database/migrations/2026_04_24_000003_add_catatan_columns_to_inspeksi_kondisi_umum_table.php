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
        if (!Schema::hasTable('inspeksi_kondisi_umum')) {
            return;
        }

        Schema::table('inspeksi_kondisi_umum', function (Blueprint $table) {
            if (!Schema::hasColumn('inspeksi_kondisi_umum', 'catatan_marker_post')) {
                $table->text('catatan_marker_post')->nullable()->after('jc_odp');
            }
            if (!Schema::hasColumn('inspeksi_kondisi_umum', 'catatan_hand_hole')) {
                $table->text('catatan_hand_hole')->nullable()->after('catatan_marker_post');
            }
            if (!Schema::hasColumn('inspeksi_kondisi_umum', 'catatan_aksesoris_ku')) {
                $table->text('catatan_aksesoris_ku')->nullable()->after('catatan_hand_hole');
            }
            if (!Schema::hasColumn('inspeksi_kondisi_umum', 'catatan_jc_odp')) {
                $table->text('catatan_jc_odp')->nullable()->after('catatan_aksesoris_ku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('inspeksi_kondisi_umum')) {
            return;
        }

        Schema::table('inspeksi_kondisi_umum', function (Blueprint $table) {
            foreach ([
                'catatan_marker_post',
                'catatan_hand_hole',
                'catatan_aksesoris_ku',
                'catatan_jc_odp',
            ] as $column) {
                if (Schema::hasColumn('inspeksi_kondisi_umum', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
