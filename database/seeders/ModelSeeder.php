<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class ModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('models')->insert([
            'brand_id' => 1,
            'model' => 'CAMIONETA',
            'created_at' => now(),
        ]);
        DB::table('models')->insert([
            'brand_id' => 2,
            'model' => 'FIESTA',
            'created_at' => now(),
        ]);
        DB::table('models')->insert([
            'brand_id' => 2,
            'model' => 'FOCUS',
            'created_at' => now(),
        ]);
        DB::table('models')->insert([
            'brand_id' => 1,
            'model' => 'CARRITO',
            'created_at' => now(),
        ]);
    }
}
