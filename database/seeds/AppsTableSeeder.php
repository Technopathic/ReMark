<?php

use Illuminate\Database\Seeder;

class AppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $date = date('Y-m-d H:i:s');

      DB::table('apps')->insert([
        'id' => 1,
        'appName' => 'Default',
        'appSlug' => 'default',
        'appAuthor' => 'Nate Grey',
        'appVersion' => '1.0',
        'appDesc' => 'The Default Theme for the ReMark Platform.',
        'appPreview' => 'img/dashpic-small.png',
        'appFramework' => 'AngularJS',
        'appActive' => '1',
        'appDocs' => 'Docs',
        'created_at' => $date,
        'updated_at' => $date
      ]);
    }
}
