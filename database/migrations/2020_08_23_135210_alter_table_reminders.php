<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableReminders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reminders', function(Blueprint $table){
            $table->boolean('is_manual')->after('user_id')->default(1);
            $table->boolean('is_enable')->after('is_manual')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reminders', function(Blueprint $table){
            $table->dropColumn('is_manual');
            $table->dropColumn('is_enabled');
        });
    }
}
