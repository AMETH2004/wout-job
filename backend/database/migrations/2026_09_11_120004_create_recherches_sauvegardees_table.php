<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recherches_sauvegardees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nom');
            $table->string('mots_cles')->nullable();
            $table->string('lieu')->nullable();
            $table->enum('type_contrat', ['emploi', 'stage', 'alternance', 'autre'])->nullable();
            $table->string('categorie')->nullable();
            $table->enum('frequence_alerte', ['quotidienne', 'hebdomadaire', 'desactivee'])->default('quotidienne');
            $table->timestamp('derniere_alerte_envoyee_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recherches_sauvegardees');
    }
};
