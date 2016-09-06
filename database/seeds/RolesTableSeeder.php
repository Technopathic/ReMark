<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      $date = date('Y-m-d H:i:s');
      
      DB::table('roles')->insert([
        'id' => 1,
        'roleName' =>
        'Administrator',
        'roleSlug' => 'Administrator',
        'roleDesc' => 'Access to everything.',
        'roleCount' => 1,
        'created_at' => $date,
        'updated_at' => $date
      ]);

      DB::table('roles')->insert([
        'id' => 2,
        'roleName' => 'Member',
        'roleSlug' => 'Member',
        'roleDesc' => 'A regular member.',
        'roleCount' => 0,
        'created_at' => $date,
        'updated_at' => $date
      ]);
    }
}
