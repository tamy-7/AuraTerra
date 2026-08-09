<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class CrearTablaUsuarios {
    public function up() {
        Capsule::schema()->create('usuarios', function ($table) {
            $table->increments('id');          
            $table->string('nombre', 100);    
            $table->string('email', 150)->unique(); 
            $table->string('password', 255);   // VARCHAR(255) para almacenar el HASH
            $table->string('rol', 50)->default('usuario'); 
            $table->timestamps();//'created_at' y 'updated_at'
        });
    }

    public function down() {
        Capsule::schema()->dropIfExists('usuarios');
    }
}