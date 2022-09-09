<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Part;

class CreateUnofficialVersionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unofficial_version', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->longText('file');
            $table->foreignIdFor(Part::class)->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unofficial_version');
    }
}
