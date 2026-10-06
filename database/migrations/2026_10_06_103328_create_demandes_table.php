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
      Schema::create('demandes', function (Blueprint $table) {
    $table->id();
    $table->string('npi', 10)->index();
    $table->string('type_acte');
    $table->unsignedTinyInteger('nombre_copies');
    $table->string('statut')->default('deposee')->index();
    $table->text('motif_rejet')->nullable();
    $table->timestamps();
    });;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
