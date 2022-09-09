<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\PartCategory;
use App\Models\PartRelease;
use App\Models\PartType;
use App\Models\PartTypeQualifier;

class CreatePartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(PartCategory::class)->nullable()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignIdFor(PartRelease::class)->constrained();
            $table->string('filename')->index();
            $table->string('data_filename')->index();
//            $table->longText('file')->fullText();
            $table->string('description')->index();
            $table->foreignIdFor(PartType::class)->constrained();
            $table->foreignIdFor(PartTypeQualifier::class)->nullable()->constrained();
            $table->boolean('unofficial');
            $table->foreignId('official_part_id')->nullable()->references('id')->on('parts')->cascadeOnUpdate()->nullOnDelete();
            $table->index('user_id');
            $table->index('part_category_id');
            $table->unique(['filename','part_release_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parts');
    }
}
