<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->unique()->nullable()->after('name');
            $table->text('bio')->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->string('banner_path')->nullable()->after('avatar_path');
            $table->string('location')->nullable()->after('banner_path');
            $table->string('website')->nullable()->after('location');
            $table->date('birthdate')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'bio',
                'avatar_path',
                'banner_path',
                'location',
                'website',
                'birthdate',
            ]);
        });
    }
};
