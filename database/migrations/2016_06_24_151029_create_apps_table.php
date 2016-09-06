<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('apps', function (Blueprint $table) {
        $table->increments('id');
        $table->string('appName');
        $table->string('appSlug');
        $table->string('appAuthor');
        $table->string('appVersion');
        $table->longText('appDesc');
        $table->longText('appPreview');
        $table->string('appFramework');
        $table->boolean('appActive');
        $table->string('appDocs');
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
        Schema::drop('apps');
    }
}
