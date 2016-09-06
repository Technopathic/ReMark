<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMchannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mchannels', function(Blueprint $table)
      {
        $table->increments('id');
        $table->string('channelTitle', 32);
        $table->text('channelDesc')->nullable();
        $table->longText('channelImg')->nullable();
        $table->string('channelSlug', 32);
        $table->boolean('channelArchived')->default(0);
        $table->boolean('channelFeatured')->default(0);
        $table->integer('channelTopics')->default(0);
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
        Schema::drop('mchannels');
    }
}
