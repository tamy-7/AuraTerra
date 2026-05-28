<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Clima extends Model {
    // Especificamos la tabla por si a futuro guardan análisis agroclimáticos
    protected $table = 'historial_clima';
    
    protected $fillable = ['ciudad', 'temperatura', 'descripcion', 'humedad', 'viento', 'usuario_id'];
}