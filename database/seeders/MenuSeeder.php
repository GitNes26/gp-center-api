<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->insert([
            'menu' => 'Catálogos',
            'caption' => 'Gestion de catálogos',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 1,
            'created_at' => now(),
        ]);
        DB::table('menus')->insert([
            'menu' => 'Usuarios',
            'type' => 'item',
            'belongs_to' => 1,
            'url' => '/admin/catalogos/usuarios',
            'icon' => 'faIconUsers',
            'order' => 1,
            'created_at' => now(),
        ]);
        DB::table('menus')->insert([
            'menu' => 'Roles',
            'type' => 'item',
            'belongs_to' => 1,
            'url' => '/admin/catalogos/roles',
            'icon' => 'IconPaperBag',
            'order' => 2,
            'created_at' => now(),
        ]);
        DB::table('menus')->insert([
            'menu' => 'Menus',
            'type' => 'item',
            'belongs_to' => 1,
            'url' => '/admin/catalogos/menus',
            // 'icon' => 'IconPaperBag',
            'order' => 3,
            'created_at' => now(),
        ]);
    }
}
