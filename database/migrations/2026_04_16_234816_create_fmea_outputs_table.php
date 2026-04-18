<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('fmea_outputs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('segment_id')->constrained()->cascadeOnDelete();

        $table->integer('bulan');
        $table->integer('tahun');

        $table->float('avg_rpn')->nullable();
        $table->float('risk_index')->nullable();

        $table->enum('priority', ['RENDAH', 'SEDANG', 'KRITIS'])->default('RENDAH');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fmea_outputs');
    }
};