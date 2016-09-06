<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('senderID');
          $table->integer('recipientID');
          $table->string('messageTitle', 150)->nullable()->default('No Subject');
          $table->longText('messageBody');
          $table->boolean('messageRead')->default(0);
          $table->integer('messageParent')->default(0);
          $table->boolean('messageArchived')->default(0);
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
        Schema::drop('messages');
    }
}
