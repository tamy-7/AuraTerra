<?php
// C:\auraTerraMayo\database\seeders\usuario_seeder.php

use Illuminate\Database\Capsule\Manager as Capsule;

class UsuarioSeeder {
    public function run() {
        // Vaciamos la tabla por si ya tenía datos viejos
        Capsule::table('usuarios')->truncate();

        // Insertamos dos usuarios de prueba con claves seguras (admin123 y user123)
        Capsule::table('usuarios')->insert([
            [
                'nombre' => 'Admin AuraTerra',
                'email' => 'AuraTerraClima@hotmail.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'rol' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Usuario Prueba',
                'email' => 'user@auraterra.com',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
                'rol' => 'usuario',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}