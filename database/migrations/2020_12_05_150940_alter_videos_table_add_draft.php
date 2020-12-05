<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterVideosTableAddDraft extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function(Blueprint $table){
            $table->boolean('is_draft')->after('user_id')->nullable();
            $table->boolean('is_published')->after('is_draft')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function(Blueprint $table){
            $table->dropColumn('is_draft');
            $table->dropColumn('is_published');
        });
    }
}
