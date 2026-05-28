<?php
namespace Helpers;

use Models\Usuario; // Importamos el modelo de Eloquent que creamos antes

class UsuarioHelper
{
    // 1. Guardar Usuario automáticamente en MySQL
    public static function guardarUsuario($email, $nombre, $passwordHash)
    {
        // Eloquent busca si ya existe el email de forma automática
        $existe = Usuario::where('email', $email)->first();
        if ($existe) {
            return false; // Si ya existe, frena el registro
        }

        // Si no existe, creamos el registro directamente en la base de datos
        $nuevo = Usuario::create([
            'email'    => $email,
            'nombre'   => $nombre,
            'password' => $passwordHash,
            'rol'      => 'usuario' // Rol por defecto
        ]);

        return $nuevo ? true : false;
    }

    // 2. Autenticar Usuario consultando a MySQL
    public static function autenticar($email, $password)
    {
        // Buscamos al usuario por su email usando Eloquent
        $usuario = Usuario::where('email', $email)->first();

        // Si existe el usuario, verificamos que el hash de la base de datos coincida con la clave ingresada
        if ($usuario && password_verify($password, $usuario->password)) {
            // Devolvemos un array con los datos para no romper la estructura de la sesión que armó tu socio
            return [
                'id'     => $usuario->id,
                'email'  => $usuario->email,
                'nombre' => $usuario->nombre
            ];
        }

        return null; // Credenciales incorrectas
    }
}