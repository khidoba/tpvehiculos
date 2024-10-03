<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    public function run()
    {
        // Roles
        $adminSistema = Role::firstOrCreate(['name' => 'Administrador de Sistema']);
        $adminAgencia = Role::firstOrCreate(['name' => 'Administrador de Agencia']);
        $tecnico = Role::firstOrCreate(['name' => 'Tecnico']);

        // Permisos
        $permissions = [
            'ver_todas_agencias',
            'ver_agencia_propia',
            'crear_usuarios',
            'modificar_usuarios',
            'eliminar_usuarios',
            'registrarVehiculo',
            'consultarVehiculo',
            'verCalendario',
            'asignar_mantenimientos',
            'aprobar_mantenimientos',
            'modificar_mantenimientos',
            'eliminar_mantenimientos',
            'actualizar_mantenimientos',
            'ver_vehiculos_asignados',
            'ver_informes',
            'asignar_vehiculos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos a roles
        $adminSistema->syncPermissions(Permission::all());

        $adminAgencia->syncPermissions([
            'ver_agencia_propia',
            'crear_usuarios',
            'modificar_usuarios',
            'eliminar_usuarios',
            'registrarVehiculo',
            'consultarVehiculo',
            'verCalendario',
            'asignar_mantenimientos',
            'aprobar_mantenimientos',
            'modificar_mantenimientos',
            'eliminar_mantenimientos',
            'ver_informes',
            'asignar_vehiculos',
        ]);

        $tecnico->syncPermissions([
            'ver_agencia_propia',
            //'consultarVehiculo',
            'verCalendario',
            'actualizar_mantenimientos',
            'ver_vehiculos_asignados',
        ]);
    }
}
