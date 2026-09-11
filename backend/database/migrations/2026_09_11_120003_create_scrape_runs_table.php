<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->timestamp('demarre_at');
            $table->timestamp('termine_at')->nullable();
            $table->unsignedInteger('offres_trouvees')->default(0);
            $table->unsignedInteger('offres_nouvelles')->default(0);
            $table->unsignedInteger('offres_maj')->default(0);
            $table->text('erreurs')->nullable();
            $table->enum('statut', ['en_cours', 'succes', 'echec'])->default('en_cours');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_runs');
    }
};
