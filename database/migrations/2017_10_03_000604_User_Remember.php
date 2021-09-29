<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserRemember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('users', function (Blueprint $table) {
            $table->rememberToken();
        });

        if (Schema::hasColumn('users', 'stripe_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('permissions');
                $table->dropColumn('activation_code');
                $table->dropColumn('persist_code');
                $table->dropColumn('reset_password_code');
                $table->dropColumn('phone_number');
                $table->dropColumn('photo');
                $table->dropColumn('address');
                $table->dropColumn('age');
                $table->dropColumn('city');
                $table->dropColumn('native_tongue');
                $table->dropColumn('calendar_enabled');
                $table->dropColumn('sound_activated');
                $table->dropColumn('institutes_id');
                $table->dropColumn('skype');
                $table->dropColumn('stripe_id');
                $table->dropColumn('is_hourly');
                $table->dropColumn('end_free_days');
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
}
