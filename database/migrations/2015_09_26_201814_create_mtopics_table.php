<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMtopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('mtopics', function (Blueprint $table) {
        $table->increments('id');
        $table->string('topicSlug');
        $table->string('topicTitle', 80);
        $table->longText('topicBody')->nullable();
        $table->longText('topicImg')->nullable();
        $table->longText('topicAudio')->nullable();
        $table->longText('topicVideo')->nullable();
        $table->longText('topicThumbnail')->nullable();
        $table->integer('topicChannel')->default(1);
        $table->integer('topicViews')->default(0);
        $table->integer('topicReplies')->default(0);
        $table->string('topicAuthor', 16);
        $table->string('topicStatus', 16)->default('Published');
        $table->boolean('topicArchived')->default(0);
        $table->boolean('topicFeature')->default(0);
        $table->longText('topicTags')->nullable();
        $table->integer('topicVotes')->default(0);
        $table->string('topicType')->default('Blog');
        $table->boolean('pageMenu')->default(0);
        $table->boolean('allowReplies')->default(1);
        $table->boolean('showImage')->default(1);
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
        Schema::drop('mtopics');
    }
}
