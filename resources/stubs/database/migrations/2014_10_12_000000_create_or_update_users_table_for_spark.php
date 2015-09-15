<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrUpdateUsersTableForSpark extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password', 60);
                $table->rememberToken();

                // Two-Factor Authentication Columns...
                $table->string('phone_country_code')->nullable();
                $table->string('phone_number')->nullable();
                $table->text('two_factor_options')->nullable();

                // Team Columns...
                $table->integer('current_team_id')->nullable();

                // Cashier Columns...
                $table->tinyInteger('stripe_active')->default(0);
                $table->string('stripe_id')->nullable();
                $table->string('stripe_subscription')->nullable();
                $table->string('stripe_plan', 100)->nullable();
                $table->string('last_four', 4)->nullable();
                $table->text('extra_billing_info')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('subscription_ends_at')->nullable();

                $table->timestamps();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'name')) {
                    $table->string('name');
                }

                if (! Schema::hasColumn('users', 'email')) {
                    $table->string('email')->unique();
                }

                if (! Schema::hasColumn('users', 'password')) {
                    $table->string('password', 60);
                }

                if (! Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }

                if (! Schema::hasColumn('users', 'phone_country_code')) {
                    $table->string('phone_country_code')->nullable();
                }

                if (! Schema::hasColumn('users', 'phone_number')) {
                    $table->string('phone_number')->nullable();
                }

                if (! Schema::hasColumn('users', 'two_factor_options')) {
                    $table->text('two_factor_options')->nullable();
                }

                if (! Schema::hasColumn('users', 'current_team_id')) {
                    $table->integer('current_team_id')->nullable();
                }

                if (! Schema::hasColumn('users', 'stripe_active')) {
                    $table->tinyInteger('stripe_active')->default(0);
                }

                if (! Schema::hasColumn('users', 'stripe_id')) {
                    $table->string('stripe_id')->nullable();
                }

                if (! Schema::hasColumn('users', 'stripe_subscription')) {
                    $table->string('stripe_subscription')->nullable();
                }

                if (! Schema::hasColumn('users', 'stripe_plan')) {
                    $table->string('stripe_plan', 100)->nullable();
                }

                if (! Schema::hasColumn('users', 'last_four')) {
                    $table->string('last_four', 4)->nullable();
                }

                if (! Schema::hasColumn('users', 'extra_billing_info')) {
                    $table->text('extra_billing_info')->nullable();
                }

                if (! Schema::hasColumn('users', 'trial_ends_at')) {
                    $table->timestamp('trial_ends_at')->nullable();
                }

                if (! Schema::hasColumn('users', 'subscription_ends_at')) {
                    $table->timestamp('subscription_ends_at')->nullable();
                }

                if (! Schema::hasColumns('users', ['created_at', 'updated_at'])) {
                    $table->timestamps();
                }
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
        Schema::drop('users');
    }
}
