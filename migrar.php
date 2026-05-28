<?php
// C:\xampp\htdocs\auraTerraMayo\migrar.php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

try {
    echo "<div style='font-family: Arial, sans-serif; padding: 20px;'>";
    echo "<h2 style='color: #333;'>🛠 Inicializando Conexión Directa a MySQL...</h2>";
    echo "<hr>";

    // 1. Inicializamos Eloquent acá mismo para no depender de la carpeta config por ahora
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'auraterra_db',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    echo "<p style='color: blue;'>• Conexión con XAMPP establecida de forma directa.</p>";

    // 2. Correr Migración (Crear la tabla)
    echo "<p>• Creando estructura física de la tabla <b>'usuarios'</b> en MySQL... ";
    
    Capsule::schema()->dropIfExists('usuarios'); // Resetea si ya existía
    
    Capsule::schema()->create('usuarios', function ($table) {
        $table->increments('id');
        $table->string('nombre', 100);
        $table->string('email', 150)->unique();
        $table->string('password', 255);
        $table->string('rol', 50)->default('usuario');
        $table->timestamps();
    });
    echo "<span style='color:green; font-weight:bold;'>¡Tabla creada con éxito!</span></p>";

    // 3. Insertar los datos del Seeder directamente
    echo "<p>• Insertando usuarios de prueba iniciales (Seeders)... ";
    Capsule::table('usuarios')->insert([
        [
            'nombre' => 'Admin AuraTerra',
            'email' => 'admin@auraterra.com',
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
    echo "<span style='color:green; font-weight:bold;'>¡Usuarios sembrados con éxito!</span></p>";

    echo "<br>";
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<b>🎉 ¡Base de datos sincronizada perfectamente de forma directa!</b> Ya podés entrar a phpMyAdmin a verificar la tabla 'usuarios'.";
    echo "</div>";
    echo "</div>";

} catch (\Exception $e) {
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; color: red;'>";
    echo "<h3>❌ Error crítico durante el proceso:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}