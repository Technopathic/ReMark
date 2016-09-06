<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMrepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mreplies', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('topicID');
        $table->integer('replyParent')->default(0);
        $table->string('replyAuthor', 16);
        $table->longText('replyBody');
        $table->boolean('replyflagged')->default(0);
        $table->boolean('replyApproved')->default(1);
        $table->boolean('replyFeature')->default(0);
        $table->integer('childCount')->default(0);
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
        Schema::drop('mreplies');
    }
}
