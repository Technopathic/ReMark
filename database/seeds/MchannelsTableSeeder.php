<?php

use Illuminate\Database\Seeder;

class MchannelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $date = date('Y-m-d H:i:s');

      DB::table('mchannels')->insert([
        'id' => 1,
        'channelTitle' => 'No Channel',
        'channelDesc' => 'No Channel',
        'channelImg' => 'img/uncategorized.png',
        'channelSlug' => 'No-Channel',
        'channelArchived' => 0,
        'channelFeatured' => 0,
        'channelTopics' => 0,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mchannels')->insert([
        'id' => 2,
        'channelTitle' => 'ReMark',
        'channelDesc' => 'ReMark',
        'channelImg' => 'https://invatar0.appspot.com/svg/RE.jpg?s=200',
        'channelSlug' => 'ReMark',
        'channelArchived' => 0,
        'channelFeatured' => 0,
        'channelTopics' => 2,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mchannels')->insert([
        'id' => 3,
        'channelTitle' => 'Featured',
        'channelDesc' => 'Featured',
        'channelImg' => 'https://invatar0.appspot.com/svg/FE.jpg?s=200',
        'channelSlug' => 'Featured',
        'channelArchived' => 0,
        'channelFeatured' => 0,
        'channelTopics' => 3,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('mchannels')->insert([
        'id' => 4,
        'channelTitle' => 'Guides',
        'channelDesc' => 'Guides',
        'channelImg' => 'https://invatar0.appspot.com/svg/GU.jpg?s=200',
        'channelSlug' => 'Guides',
        'channelArchived' => 0,
        'channelFeatured' => 0,
        'channelTopics' => 3,
        'created_at' => $date,
        'updated_at' => $date
      ]);
    }
}
