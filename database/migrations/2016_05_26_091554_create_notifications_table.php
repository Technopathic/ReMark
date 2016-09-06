<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('userID');
          $table->integer('notifierID')->default(0);
          $table->integer('contentID');
          $table->string('notificationType');
          $table->string('notificationSubType');
          $table->boolean('notificationRead')->default(0);
          $table->timestamps(3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::drop('notifications');
    }
}
