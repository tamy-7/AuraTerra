<?php
declare(strict_types=1);
session_start();

// 1. Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Encender los motores de la Base de Datos (Eloquent) centralizado
require_once __DIR__ . '/../config/database.php';

// 3. Cargar la configuración global
$config = require __DIR__ . '/../config/config.php';

// 4. Cargar Componentes del Sistema (Helpers, Modelos y Validadores)
require_once __DIR__ . '/../src/Services/ClimaService.php';
require_once __DIR__ . '/../src/Helpers/UsuarioHelper.php';
require_once __DIR__ . '/../src/Models/Usuario.php'; 
require_once __DIR__ . '/../src/Models/Clima.php'; 
require_once __DIR__ . '/../src/Validators/ClimaValidator.php'; 

// 5. Cargar Controladores
require_once __DIR__ . '/../src/Controllers/HealthController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/ClimaController.php';

use Validators\ClimaValidator;
use Services\ClimaService;
use Helpers\UsuarioHelper;

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Basepath ajustado a tu carpeta actual en XAMPP
$basePath = '/auraTerraMayo/public';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . ltrim($path, '/');
if ($path === '//') $path = '/';


// ========== FUNCIONES DE MANEJO DE ENDPOINTS ==========

function handleNotFound($path) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Not Found', 'path' => $path]);
}

// ========== LÓGICA DE ENRUTAMIENTO ==========

if ($method === 'GET' && $path === '/health') {
    handleHealth();
} elseif ($method === 'GET' && $path === '/register') {
    handleRegisterGet();
} elseif ($method === 'POST' && $path === '/register') {
    handleRegisterPost();
} elseif ($method === 'GET' && $path === '/login') {
    handleLoginGet();
} elseif ($method === 'POST' && $path === '/login') {
    handleLoginPost();
} elseif ($method === 'GET' && $path === '/logout') {
    handleLogout();
} elseif ($method === 'GET' && $path === '/perfil') {
    handlePerfil();
} elseif ($method === 'GET' && $path === '/clima/actual') {
    handleClimaActual($config);
} elseif ($method === 'GET' && $path === '/clima/pronostico') {
    handleClimaPronostico($config);
    
// INTERFAZ DE BIENVENIDA: Cuando entran a /public directo sin poner rutas
} elseif ($method === 'GET' && $path === '/') {
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bienvenido - AuraTerra</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            body { 
                background: linear-gradient(135deg, #e0eccf 0%, #f9f6f0 50%, #fcdad1 100%); 
                min-height: 100vh; 
                display: flex; 
                justify-content: center; 
                align-items: center; 
                padding: 20px;
            }
            .welcome-container { 
                background: rgba(255, 255, 255, 0.95); 
                padding: 45px 35px; 
                border-radius: 16px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
                width: 100%; 
                max-width: 450px; 
                text-align: center;
                backdrop-filter: blur(5px);
            }
            .brand-header { margin-bottom: 30px; }
            .brand-logo-mock {
                width: 70px; height: 70px; margin: 0 auto 10px;
                background: linear-gradient(135deg, #8cb89f, #f1a995);
                border-radius: 50%; opacity: 0.85;
                box-shadow: 0 4px 12px rgba(140, 184, 159, 0.2);
            }
            .brand-name { font-size: 2rem; color: #2c3e50; font-weight: bold; margin-bottom: 5px; }
            .brand-slogan { font-size: 0.85rem; color: #7f8c8d; line-height: 1.4; font-style: italic; }
            
            .intro-text { color: #34495e; font-size: 1rem; margin-bottom: 30px; line-height: 1.5; }
            
            .menu-grid { display: flex; flex-direction: column; gap: 15px; }
            
            .btn-navigation {
                display: block;
                text-decoration: none;
                padding: 14px;
                border-radius: 8px;
                font-size: 1.05rem;
                font-weight: bold;
                transition: all 0.2s ease;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }
            
            .btn-login { background-color: #007bff; color: white; }
            .btn-login:hover { background-color: #0056b3; transform: translateY(-2px); }
            
            .btn-register { background-color: #27ae60; color: white; }
            .btn-register:hover { background-color: #219653; transform: translateY(-2px); }
            
            .footer-note { margin-top: 35px; font-size: 0.78rem; color: #95a5a6; }
        </style>
    </head>
    <body>
        <div class="welcome-container">
            <div class="brand-header">
                <div class="brand-logo-mock"></div>
                <div class="brand-name">AuraTerra</div>
                <div class="brand-slogan">"Los 21gr del timo que acompañan el exito de la organizacion de tu actividad al aire libre"</div>
            </div>
            
            <p class="intro-text">¡Te damos la bienvenida al Portal Agroclimático! Elegí una opción para ingresar a la plataforma y gestionar tus actividades.</p>
            
            <div class="menu-grid">
                <a href="/auraTerraMayo/public/login" class="btn-navigation btn-login">🔑 Iniciar Sesión</a>
                <a href="/auraTerraMayo/public/register" class="btn-navigation btn-register">🌱 Crear Cuenta Nueva</a>
            </div>
            
            <p class="footer-note">AuraTerra v1.0 - Gestión Ambiental Inteligente</p>
        </div>
    </body>
    </html>
    HTML;
    exit;
} else {
    handleNotFound($path);
}