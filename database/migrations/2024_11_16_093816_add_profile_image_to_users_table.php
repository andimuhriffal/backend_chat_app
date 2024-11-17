<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileImageToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom 'profile_image' yang nullable
            $table->string('profile_image')->nullable(); // Menambahkan kolom profile_image
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Menghapus kolom 'profile_image' jika rollback
            $table->dropColumn('profile_image');
        });
    }
}
