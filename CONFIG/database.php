<?php
// C:\xampp\htdocs\auraTerraMayo\config\database.php

use Illuminate\Database\Capsule\Manager as Capsule;

// Usamos __DIR__ para que busque el config.php al lado de este archivo, sin importar quién lo llame
$config = require __DIR__ . '/config.php';

$capsule = new Capsule;

// Pasamos los datos de la conexión
$capsule->addConnection($config['database']);

$capsule->setAsGlobal();
$capsule->bootEloquent();