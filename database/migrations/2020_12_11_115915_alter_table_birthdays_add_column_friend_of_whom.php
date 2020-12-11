<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableBirthdaysAddColumnFriendOfWhom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('birthdays', function(Blueprint $table){
            $table->unsignedBigInteger('friend_id')->index()->nullable()->after('birthday');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('birthdays', function(Blueprint $table){
            $table->dropColumn('friend_id');
        });
    }
}
