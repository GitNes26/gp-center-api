<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('brands')->insert([
            'brand' => 'CHEVROLET',
            'img_path' => 'GPCenter/brands/1.JPG',
            'created_at' => now(),
        ]);
        DB::table('brands')->insert([
            'brand' => 'FORD',
            'img_path' => 'GPCenter/brands/2.JPG',
            'created_at' => now(),
        ]);
    }
}