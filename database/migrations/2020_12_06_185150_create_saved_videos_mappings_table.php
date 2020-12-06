<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedVideosMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_videos_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('publisher_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saved_videos_mappings');
    }
}
