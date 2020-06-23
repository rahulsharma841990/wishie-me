<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table){
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('username')->after('last_name');
            $table->date('dob')->nullable()->after('username');
            $table->string('gender')->after('dob');
            $table->double('phone')->after('password');
            $table->string('country_code')->after('phone');
            $table->text('profile_image')->after('country_code')->nullable();
            $table->text('facebook_id')->after('profile_image')->nullable();
            $table->text('gmail_id')->after('facebook_id')->nullable();
            $table->text('twitter_id')->after('gmail_id')->nullable();
            $table->text('apple_id')->after('twitter_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table){
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('username');
            $table->dropColumn('dob');
            $table->dropColumn('gender');
            $table->dropColumn('phone');
            $table->dropColumn('country_code');
            $table->dropColumn('profile_image');
            $table->dropColumn('facebook_id');
            $table->dropColumn('gmail_id');
            $table->dropColumn('twitter_id');
            $table->dropColumn('apple_id');
        });
    }
}
