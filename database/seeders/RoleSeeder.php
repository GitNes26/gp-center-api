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
            'more_permissions' => 'todas',
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
            'role' => 'Encargado de Almacen',
            'description' => 'Rol dedicado para usuarios que gestionaran el inventario de alamecen.',
            'read' => '1,2',
            'create' => 'todas',
            'update' => 'todas',
            'delete' => 'todas',
            'created_at' => now(),
        ]);
        DB::table('roles')->insert([
            'role' => 'Mecánico',
            'description' => 'Rol dedicado para mecánicos del taller.',
            'read' => '1,2',
            'created_at' => now(),
        ]);
        DB::table('roles')->insert([
            'role' => 'Director',
            'description' => 'Rol dedicado para usuarios a quienes se les asignaran las unidades y haran uso de ella.',
            'read' => '1,2',
            'created_at' => now(),
        ]);
        DB::table('roles')->insert([
            'role' => 'Conductor',
            'description' => 'Rol dedicado para conductores permitidos por los directores.',
            'read' => '1,2',
            'created_at' => now(),
        ]);
    }
}