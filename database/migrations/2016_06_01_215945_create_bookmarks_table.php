<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('bookmarks', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('feedID');
        $table->string('bookmarkDomain');
        $table->string('bookmarkTitle');
        $table->longText('bookmarkImg');
        $table->string('bookmarkAuthor');
        $table->longText('bookmarkSource');
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
      Schema::drop('bookmarks');
    }
}
