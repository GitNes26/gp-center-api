<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'role' => 'SuperAdmin',
            'description' => 'Rol dedicado para la completa configuraciond del sistema desde el area de desarrollo.',
            'read' => 'todas',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'created_at' => now(),
        ]);
        DB::table('roles')->insert([
            'role' => 'Administrador',
            'description' => 'Rol dedicado para usuarios que gestionaran el sistema.',
            'read' => '1,2,3,4,6,12,13,14,15',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'created_at' => now(),
        ]);
        DB::table('roles')->insert([
            'role' => 'Usuario',
            'description' => 'Rol dedicado para usuarios que harán uso de las unidades.',
            'created_at' => now(),
        ]);
        DB::table('roles')->insert([
            'role' => 'Mecánico',
            'description' => 'Rol dedicado para mecánicos del taller.',
            'created_at' => now(),
        ]);
    }
}
