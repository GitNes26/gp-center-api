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
        // DASHBOARD
        DB::table('menus')->insert([ #1
            'menu' => 'Dashboard',
            'caption' => '',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 1,
            'created_at' => now(),
        ]);
            DB::table('menus')->insert([ #2
                'menu' => 'Buscador',
                'caption' => '',
                'type' => 'item',
                'belongs_to' => 1,
                'url' => '/admin',
                'icon' => 'IconSearch',
                'order' => 1,
                'created_at' => now(),
            ]);
        // ADMINISTRATIVO
        DB::table('menus')->insert([ #3
            'menu' => 'Administrativo',
            'caption' => 'Control de usuarios',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 2,
            'created_at' => now(),
        ]);
            DB::table('menus')->insert([ #4
                'menu' => 'Usuarios',
                'type' => 'item',
                'belongs_to' => 3,
                'url' => '/admin/usuarios',
                'icon' => 'IconUsers',
                'order' => 1,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #5
                'menu' => 'Roles',
                'type' => 'item',
                'belongs_to' => 3,
                'url' => '/admin/roles',
                'icon' => 'IconPaperBag',
                'order' => 2,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #6
                'menu' => 'Departamentos',
                'type' => 'item',
                'belongs_to' => 3,
                'url' => '/admin/departamentos',
                'icon' => 'IconBuildingSkyscraper',
                'order' => 3,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #7
                'menu' => 'Menus',
                'type' => 'item',
                'belongs_to' => 3,
                'url' => '/admin/menus',
                'icon' => 'IconCategory2',
                'order' => 4,
                'created_at' => now(),
            ]);
        // TALLER
        DB::table('menus')->insert([ #8
            'menu' => 'Taller',
            'caption' => 'Catálogos del Taller',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 3,
            'created_at' => now(),
        ]);
            DB::table('menus')->insert([ #9
                'menu' => 'Almacen (Stock)',
                'type' => 'item',
                'belongs_to' => 8,
                'url' => '/admin/taller/almacen',
                'icon' => 'IconCarGarage',
                'order' => 1,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #10
                'menu' => 'Servicios',
                'type' => 'item',
                'belongs_to' => 8,
                'url' => '/admin/taller/servicios',
                'icon' => 'IconTool',
                'order' => 2,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #11
                'menu' => 'Requisiones - PENDIENTE',
                'type' => 'item',
                'belongs_to' => 8,
                'url' => '/admin/taller/requisiciones',
                'icon' => 'IconFileInvoice',
                'order' => 3,
                'created_at' => now(),
            ]);
        // CoVe
        DB::table('menus')->insert([ #12
            'menu' => 'CoVe',
            'caption' => 'Control Vehicular',
            'type' => 'group',
            'belongs_to' => 0,
            'order' => 4,
            'created_at' => now(),
        ]);
            DB::table('menus')->insert([ #13
                'menu' => 'Marcas',
                'type' => 'item',
                'belongs_to' => 12,
                'url' => '/admin/cove/marcas',
                'icon' => 'IconBadgeTm',
                'order' => 1,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #14
                'menu' => 'Modelos',
                'type' => 'item',
                'belongs_to' => 12,
                'url' => '/admin/cove/modelos',
                'icon' => 'IconBoxModel2',
                'order' => 2,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #15
                'menu' => 'Estatus de Vehíuclo',
                'type' => 'item',
                'belongs_to' => 12,
                'url' => '/admin/cove/estatus-vehiculo',
                'icon' => 'IconStatusChange',
                'order' => 3,
                'created_at' => now(),
            ]);
            DB::table('menus')->insert([ #15
                'menu' => 'Vehículos',
                'type' => 'item',
                'belongs_to' => 12,
                'url' => '/admin/cove/vehiculos',
                'icon' => 'IconCar',
                'order' => 3,
                'created_at' => now(),
            ]);
    }
}


// INSERT INTO menus VALUES
// (1,'Dashboard','','group',0,null,null,1,0,1,'2023-11-05 01:55:45',null,null),
// (2,'Buscador','','item',1,'/admin','IconSearch',1,0,1,'2023-11-05 01:55:45',null,null),
// (3,'Administrativo','Control de usuarios','group',0,null,null,2,0,1,'2023-11-05 01:55:45',null,null),
// (4,'Usuarios','','item',3,'/admin/usuarios','IconUsers',1,0,1,'2023-11-05 01:55:45',null,null),
// (5,'Roles','','item',3,'/admin/roles','IconPaperBag',2,0,1,'2023-11-05 01:55:45',null,null),
// (6,'Departamentos','','item',3,'/admin/departamentos','IconBuildingSkyscraper',3,0,1,'2023-11-05 01:55:45',null,null),
// (7,'Menus','','item',3,'/admin/menus','IconCategory2',4,0,1,'2023-11-05 01:55:45',null,null),
// (8,'Taller','Catálogos del Taller','group',0,null,null,3,0,1,'2023-11-05 01:55:45',null,null),
// (9,'Almacen (Stock)','','item',8,'/admin/taller/almacen','IconCarGarage',1,0,1,'2023-11-05 01:55:45',null,null),
// (10,'Servicios','','item',8,'/admin/taller/servicios','IconTool',2,0,1,'2023-11-05 01:55:45',null,null),
// (11,'Requisiones - PENDIENTE','','item',8,'/admin/taller/requisiciones','IconFileInvoice',3,0,1,'2023-11-05 01:55:45',null,null),
// (12,'CoVe','Control Vehicular','group',0,null,null,4,0,1,'2023-11-05 01:55:45',null,null),
// (13,'Marcas','','item',12,'/admin/cove/marcas','IconBadgeTm',1,0,1,'2023-11-05 01:55:45',null,null),
// (14,'Modelos','','item',12,'/admin/cove/modelos','IconBoxModel2',2,0,1,'2023-11-05 01:55:45',null,null),
// (15,'Estatus de Vehíuclo','','item',12,'/admin/cove/estatus-vehiculo','IconStatusChange',3,0,1,'2023-11-05 01:55:45',null,null),
// (16,'Vehículos','','item',12,'/admin/cove/vehiculos','IconCar',3,0,1,'2023-11-05 01:55:45',null,null);