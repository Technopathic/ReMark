<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $this->call(AppsTableSeeder::class);
      $this->call(MchannelsTableSeeder::class);
      $this->call(MtopicsTableSeeder::class);
      $this->call(RolesTableSeeder::class);
    }
}
