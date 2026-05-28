<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class CrearTablaUsuarios {
    // El método 'up' se ejecuta al crear la tabla
    public function up() {
        Capsule::schema()->create('usuarios', function ($table) {
            $table->increments('id');          // ID autoincremental (Primary Key)
            $table->string('nombre', 100);     // VARCHAR(100) para el nombre
            $table->string('email', 150)->unique(); // VARCHAR(150) Único para el login
            $table->string('password', 255);   // VARCHAR(255) para almacenar el HASH
            $table->string('rol', 50)->default('usuario'); // Rol por defecto
            $table->timestamps();              // Crea automáticamente 'created_at' y 'updated_at'
        });
    }

    // El método 'down' se ejecuta si queremos borrar la tabla (borrón y cuenta nueva)
    public function down() {
        Capsule::schema()->dropIfExists('usuarios');
    }
}