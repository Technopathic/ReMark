<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('options', function (Blueprint $table) {
        $table->increments('id');
        $table->string('owner', 16);
        $table->string('website');
        $table->string('baseurl');
        $table->longText('siteLogo')->nullable();
        $table->integer('homePage')->default(0);
        $table->boolean('homeBanner')->default(1);
        $table->boolean('allowRegistration')->default(1);
        $table->boolean('allowSubscription')->default(1);
        $table->boolean('requireActivation')->default(0);
        $table->boolean('replyModeration')->default(0);
        $table->boolean('allowAsk')->default(1);
        $table->string('feedVer');
        $table->string('remarkVersion');
        $table->longText('aboutWebsite')->nullable();
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
        Schema::drop('options');
    }
}
