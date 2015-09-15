<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create Teams Table...
        Schema::create('teams', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id')->unsigned()->nullable();
            $table->string('name');
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
        });

        // Add Foreign Key Constraint to Users Table...
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('current_team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');
        });

        // Create User Teams Intermediate Table...
        Schema::create('user_teams', function (Blueprint $table) {
            $table->integer('team_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('role', 25);

            $table->unique(['team_id', 'user_id']);
            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });

        // Create Invitations Table...
        Schema::create('invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('email');
            $table->string('token', 40)->unique();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invitations');
        Schema::drop('user_teams');

        Schema::table('users', function(Blueprint $table) {
            $table->dropForeign('users_current_team_id_foreign');
        });

        Schema::drop('teams');
    }
}
