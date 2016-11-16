<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('feeds', function (Blueprint $table) {
        $table->increments('id');
        $table->longText('feedUrl');
        $table->string('feedName');
        $table->longText('feedImg');
        $table->longText('feedDesc');
        $table->longText('feedLoc');
        $table->longText('feedTags');
        $table->string('feedType')->nullable()->default('normal');
        $table->longText('feedAPI')->nullable();
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
        Schema::drop('feeds');
    }
}
