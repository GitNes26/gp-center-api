<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class VehicleStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('vehicle_status')->insert([
            'vehicle_status' => 'SIN ASIGNAR',
            'bg_color' => '#d9d9d9',
            'letter_black' => true,
            'description' => 'EL vehículo no tiene un estatus definido.',
            'created_at' => now(),
        ]);
        DB::table('vehicle_status')->insert([
            'vehicle_status' => 'DISPONIBLE',
            'bg_color' => '#128129',
            'letter_black' => false,
            'description' => 'EL vehículo esta disponible en patrimonio.',
            'created_at' => now(),
        ]);
        DB::table('vehicle_status')->insert([
            'vehicle_status' => 'ASIGNADO',
            'bg_color' => '#083691',
            'letter_black' => false,
            'description' => 'EL vehículo esta asignado a un usuario.',
            'created_at' => now(),
        ]);
        DB::table('vehicle_status')->insert([
            'vehicle_status' => 'PRESTADO',
            'bg_color' => '#99860a',
            'letter_black' => false,
            'description' => 'EL vehículo esta asignado a un usuario, un tercero se lo pidio prestado.',
            'created_at' => now(),
        ]);
        DB::table('vehicle_status')->insert([
            'vehicle_status' => 'EN SERVICIO',
            'bg_color' => '#59575c',
            'letter_black' => false,
            'description' => 'EL vehículo se encuentra en el taller.',
            'created_at' => now(),
        ]);

    }
}
