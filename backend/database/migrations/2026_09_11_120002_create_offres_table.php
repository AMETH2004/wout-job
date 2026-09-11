<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->string('source_ref', 512);
            $table->string('titre');
            $table->string('entreprise')->nullable();
            $table->string('lieu')->nullable();
            $table->enum('type_contrat', ['emploi', 'stage', 'alternance', 'autre'])->default('autre');
            $table->string('categorie')->nullable();
            $table->longText('description_html')->nullable();
            $table->longText('description_texte')->nullable();
            $table->date('date_publication')->nullable();
            $table->date('date_limite')->nullable();
            $table->string('url_source', 1024);
            $table->string('url_image', 1024)->nullable();
            $table->enum('statut', ['active', 'expiree'])->default('active');
            $table->string('hash_contenu', 64)->nullable();
            $table->timestamp('premiere_vue_at');
            $table->timestamp('derniere_vue_at');
            $table->timestamps();

            $table->unique(['source_id', 'source_ref']);
            $table->index(['statut', 'date_publication']);
            $table->index('type_contrat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};
