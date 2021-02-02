<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableFriendsAddColumnIsBlocked extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('friends', function(Blueprint $table){
            $table->boolean('is_blocked')->default(0)->nullable()
                ->comment('1 for block the user and 0 for unblock')->after('is_rejected');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('friends', function(Blueprint $table){
            $table->dropColumn('is_blocked');
        });
    }
}
