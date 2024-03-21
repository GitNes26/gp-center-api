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
                        'description' => 'Rol dedicado para la completa configuracion del sistema desde el area de desarrollo.',
                        'read' => 'todas',
                        'create' => 'todas',
                        'update' => 'todas',
                        'delete' => 'todas',
                        'more_permissions' => "2@Solicitar Servicio,2@Asignar Vehículo,2@Prestar Vehículo,2@Devolver Prestamo,2@Devolver Vehículo,5@Asignar Permisos",
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                        'role' => 'Administrador',
                        'description' => 'Rol dedicado para usuarios que gestionaran el sistema.',
                        'read' => "1,2,3,4,6,8,11,12,19,20,21,22,23",
                        'create' => 'todas',
                        'update' => 'todas',
                        'delete' => 'todas',
                        'more_permissions' => "2@Solicitar Servicio,2@Asignar Vehículo,2@Prestar Vehículo,2@Devolver Prestamo,2@Devolver Vehículo,5@Asignar Permisos",
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                        'role' => 'Encargado de Almacen',
                        'description' => 'Rol dedicado para usuarios que gestionaran el inventario de alamecen.',
                        'read' => "1,2",
                        'create' => 'todas',
                        'update' => 'todas',
                        'delete' => 'todas',
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                        'role' => 'Mecánico',
                        'description' => 'Rol dedicado para mecánicos del taller.',
                        'read' => "1,2",
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                        'role' => 'Director',
                        'description' => 'Rol dedicado para usuarios a quienes se les asignaran las unidades y haran uso de ella.',
                        'read' => "1,2,3,12,19,23,24",
                        'create' => '12,24',
                        'update' => '12,24',
                        'delete' => '12,24',
                        'more_permissions' => "2@Prestar Vehículo,2@Devolver Vehículo,24@Cancelar Vale",
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                        'role' => 'Conductor',
                        'description' => 'Rol dedicado para conductores permitidos por los directores.',
                        'read' => '1,2,19,23',
                        'more_permissions' => '2@Devolver Prestamo',
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                        'role' => 'Admin. Contro de Vales',
                        'description' => 'Rol dedicado persolan de control Vehícular para aprobar vales.',
                        'read' => "1,2,3,14,19,23,24",
                        'create' => "14,24",
                        'update' => "14",
                        'delete' => "14",
                        'more_permissions' => "24@Aprobar Vale,24@Cancelar Vale",
                        'page_index' => '/admin/cove/vales',
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                        'role' => 'Solicitador de vales',
                        'description' => 'Rol dedicado unicamente para solicitar vales.',
                        'read' => '19,24',
                        'create' => '24',
                        'more_permissions' => "24@Cancelar Vale",
                        'page_index' => '/admin/cove/vales',
                        'created_at' => now(),
                ]);
                DB::table('roles')->insert([
                    'role' => 'Supervisor de vales',
                    'description' => 'Rol dedicado unicamente para darvisto bueno (VoBo) a las solicitudes de vales antes de asignar folios.',
                    'read' => '19,24',
                    'create' => '24',
                    'more_permissions' => "24@Cancelar Vale, 24@VoBo",
                    'page_index' => '/admin/cove/vales',
                    'created_at' => now(),
            ]);
        }
}
