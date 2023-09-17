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
        Schema::create('omr_sets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(\App\Models\User::class);
            $table->foreignIdFor(\App\Models\Omr\Set::class);
            $table->foreignIdFor(\App\Models\PartLicense::class)->constrained();
            $table->boolean('missing_parts');
            $table->boolean('missing_patterns');
            $table->boolean('missing_stickers');
            $table->boolean('alt_model');
            $table->json('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('omr_sets');
    }
};
