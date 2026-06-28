<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GevlaAccessSeeder extends Seeder
{
    /**
     * Crea los roles y usuarios base para ingresar a GEVLA.
     */
    public function run(): void
    {
        foreach (['Aprendiz', 'Instructor', 'Coordinador'] as $role) {
            DB::table('rol')->updateOrInsert(
                ['nombre_rol' => $role],
                ['nombre_rol' => $role]
            );
        }

        $password = Hash::make('Gevla2026*');

        $users = [
            [
                'role' => 'Aprendiz',
                'username' => 'aprendiz.gevla',
                'numero_documento' => '1000000001',
                'tipo_documento' => 'TI',
                'nombres' => 'Aprendiz',
                'apellidos' => 'GEVLA',
                'correo' => 'aprendiz.gevla@sena.edu.co',
                'telefono' => '3000000001',
            ],
            [
                'role' => 'Instructor',
                'username' => 'instructor.gevla',
                'numero_documento' => '1000000002',
                'tipo_documento' => 'CC',
                'nombres' => 'Instructor',
                'apellidos' => 'GEVLA',
                'correo' => 'instructor.gevla@sena.edu.co',
                'telefono' => '3000000002',
            ],
            [
                'role' => 'Coordinador',
                'username' => 'coordinador.gevla',
                'numero_documento' => '1000000003',
                'tipo_documento' => 'CC',
                'nombres' => 'Coordinador',
                'apellidos' => 'GEVLA',
                'correo' => 'coordinador.gevla@sena.edu.co',
                'telefono' => '3000000003',
            ],
        ];

        foreach ($users as $user) {
            DB::table('usuario')->updateOrInsert(
                ['username' => $user['username']],
                [
                    'numero_documento' => $user['numero_documento'],
                    'tipo_documento' => $user['tipo_documento'],
                    'nombres' => $user['nombres'],
                    'apellidos' => $user['apellidos'],
                    'correo' => $user['correo'],
                    'telefono' => $user['telefono'],
                    'password_hash' => $password,
                    'estado_usuario' => 'activo',
                ]
            );

            $userId = DB::table('usuario')->where('username', $user['username'])->value('id_usuario');
            $roleId = DB::table('rol')->where('nombre_rol', $user['role'])->value('id_rol');

            DB::table('usuario_rol')->updateOrInsert(
                ['id_usuario' => $userId, 'id_rol' => $roleId],
                ['estado_asignacion' => 'activa']
            );
        }

        $aprendizId = DB::table('usuario')->where('username', 'aprendiz.gevla')->value('id_usuario');
        $instructorId = DB::table('usuario')->where('username', 'instructor.gevla')->value('id_usuario');
        $coordinadorId = DB::table('usuario')->where('username', 'coordinador.gevla')->value('id_usuario');

        DB::table('aprendiz')->updateOrInsert(
            ['id_usuario' => $aprendizId],
            [
                'correo_institucional' => 'aprendiz.gevla@sena.edu.co',
                'correo_personal' => 'aprendiz.gevla@gmail.com',
                'estado_academico' => 'en_formacion',
                'tiene_apoyo_sostenimiento' => 0,
            ]
        );

        DB::table('instructor')->updateOrInsert(
            ['id_usuario' => $instructorId],
            [
                'codigo_instructor' => 'INS-GEVLA',
                'area_formacion' => 'Gestion disciplinaria y formacion',
                'estado_instructor' => 'activo',
            ]
        );

        DB::table('coordinacion')->updateOrInsert(
            ['id_usuario' => $coordinadorId],
            [
                'cargo' => 'Coordinador GEVLA',
                'dependencia' => 'Coordinacion academica',
                'estado_coordinacion' => 'activo',
            ]
        );
    }
}
