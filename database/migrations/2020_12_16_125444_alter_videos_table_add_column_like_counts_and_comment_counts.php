<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterVideosTableAddColumnLikeCountsAndCommentCounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function(Blueprint $table){
            $table->double('like_counts')->after('is_published')->default(0);
            $table->double('comment_counts')->after('is_published')->default(0);
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
            $table->dropColumn('like_counts');
            $table->dropColumn('comment_counts');
        });
    }
}
