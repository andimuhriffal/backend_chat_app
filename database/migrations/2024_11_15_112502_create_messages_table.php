<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // Kolom id utama
            $table->unsignedBigInteger('sender_id'); // Kolom sender_id
            $table->unsignedBigInteger('receiver_id'); // Kolom receiver_id
            $table->text('content'); // Kolom untuk menyimpan isi pesan
            $table->timestamps(); // Kolom created_at dan updated_at

            // Menambahkan foreign key untuk sender_id dan receiver_id
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
