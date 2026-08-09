<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class UsuarioSeeder {
    public function run() {
        Capsule::table('usuarios')->truncate();

        Capsule::table('usuarios')->insert([
            [
                'nombre' => 'Administradores',
                'email' => 'admin@auraterra.com',
                'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
                'rol' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Usuario Prueba',
                'email' => 'user@auraterra.com',
                'password' => password_hash('User123!', PASSWORD_DEFAULT),
                'rol' => 'usuario',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}