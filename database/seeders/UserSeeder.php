<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('desarrollo'),
            'role_id' => 1, //SuperAdmin
        // 'department_id' => '1',
            'created_at' => now()
        ]);
        DB::table('users')->insert([
            'username' => 'patrimonio',
            'email' => 'patrimonio@gomezpalacio.gob.mx',
            'password' => Hash::make('123456'),
            'role_id' => 2, //Admin
            'created_at' => now()
        ]);
    }
}
