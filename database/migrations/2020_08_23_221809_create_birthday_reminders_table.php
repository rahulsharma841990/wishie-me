<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBirthdayRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('birthday_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('birthday_id')->index();
            $table->unsignedBigInteger('reminder_id')->index()->nullable();
            $table->string('title')->nullable();
            $table->string('days_before')->nullable();
            $table->string('time')->nullable();
            $table->string('tone')->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->boolean('is_manual');
            $table->boolean('is_enable')->default(1);
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
        Schema::dropIfExists('birthday_reminders');
    }
}
