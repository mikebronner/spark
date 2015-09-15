<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTablesForSpark extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('teams')) {
            // Create Teams Table...
            Schema::create('teams', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('owner_id')->index();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_teams')) {
            // Create User Teams Intermediate Table...
            Schema::create('user_teams', function (Blueprint $table) {
                $table->integer('team_id');
                $table->integer('user_id');
                $table->string('role', 25);

                $table->unique(['team_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('user_teams')) {
            // Create Invitations Table...
            Schema::create('user_teams', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('team_id')->index();
                $table->integer('user_id')->nullable()->index();
                $table->string('email');
                $table->string('token', 40)->unique();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('teams');
        Schema::drop('user_teams');
        Schema::drop('invitations');
    }
}
