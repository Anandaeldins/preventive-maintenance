<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeksi_details', function (Blueprint $table) {

            $table->id();

            $table->foreignId('inspeksi_id')
                  ->constrained('inspeksi_headers')
                  ->onDelete('cascade');

            $table->string('objek');      // kabel_putus, tiang, dll
            $table->string('status')->nullable();
            $table->json('atribut')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeksi_details');
    }
};
