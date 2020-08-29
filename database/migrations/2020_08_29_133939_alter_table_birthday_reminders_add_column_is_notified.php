<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableBirthdayRemindersAddColumnIsNotified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('birthday_reminders', function(Blueprint $table){
            $table->boolean('is_notified')->after('is_enable')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('birthday_reminders', function(Blueprint $table){
            $table->dropColumn('is_notified');
        });
    }
}
