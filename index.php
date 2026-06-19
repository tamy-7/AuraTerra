<?php
declare(strict_types=1);

// Evitamos colisiones de hilos de datos si XAMPP destruyó la sesión en segundo plano
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$dirAlmacenamientoLimiter = __DIR__ . '/storage/rate_limiter';

require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Controllers/ClimaController.php';

class RateLimiter {
    private string $storageDir; private int $maxRequests; private int $windowSeconds; private int $blockDuration;
    public function __construct(string $storageDir, int $maxRequests = 8, int $windowSeconds = 10, int $blockDuration = 60) {
        $this->storageDir = rtrim($storageDir, '/'); $this->maxRequests = $maxRequests; $this->windowSeconds = $windowSeconds; $this->blockDuration = $blockDuration;
        if (!is_dir($this->storageDir)) { mkdir($this->storageDir, 0755, true); }
    }
    public function getClientIP(): string {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]); }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    public function isBlocked(string $ip): bool {
        $blockFile = $this->storageDir . '/blocked_' . md5($ip) . '.json';
        if (!file_exists($blockFile)) return false;
        $data = json_decode(file_get_contents($blockFile), true);
        if ($data['blocked_until'] > time()) return true;
        @unlink($blockFile); return false;
    }
    public function check(string $ip): bool {
        if ($this->isBlocked($ip)) return false;
        $logFile = $this->storageDir . '/log_' . md5($ip) . '.json'; $now = time(); $windowStart = $now - $this->windowSeconds;
        $log = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : ['requests' => []];
        $log['requests'] = array_filter($log['requests'], fn($ts) => $ts > $windowStart);
        if (count($log['requests']) >= $this->maxRequests) {
            $blockData = ['ip' => $ip, 'blocked_at' => $now, 'blocked_until' => $now + $this->blockDuration];
            file_put_contents($this->storageDir . '/blocked_' . md5($ip) . '.json', json_encode($blockData, JSON_PRETTY_PRINT));
            @unlink($logFile); return false;
        }
        $log['requests'][] = $now; file_put_contents($logFile, json_encode($log)); return true;
    }
}

$limiter = new RateLimiter($dirAlmacenamientoLimiter, 8, 10, 60);
$clientIP = $limiter->getClientIP();

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
$path = str_replace(['/auraTerraMayo/public', '/auraTerraMayo'], '', $path);
$path = '/' . ltrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Si viene con bandera de bot o la IP está en la lista negra, cortamos el hilo y mostramos la plantilla premium
if (!$limiter->check($clientIP) || isset($_GET['error_suspension_manual'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Acceso Restringido - AuraTerra</title>
        <style>
            body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .card-suspension { background: white; padding: 45px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.06); text-align: center; max-width: 480px; border-top: 6px solid #e53e3e; }
            h1 { color: #e53e3e; font-size: 1.7rem; margin-bottom: 12px; font-weight: 800; }
            p { color: #4a5568; font-size: 1.05rem; line-height: 1.6; margin-bottom: 20px; }
            .contacto-soporte { background: #fff5f5; color: #c53030; padding: 14px; border-radius: 8px; font-weight: bold; font-size: 1rem; border: 1px solid #fed7d7; }
        </style>
    </head>
    <body>
        <div class="card-suspension">
            <h1>🛡️ Cuenta Temporalmente Suspendida</h1>
            <p>El sistema de seguridad de AuraTerra ha bloqueado este acceso por detectar comportamiento automatizado de ráfagas (Bot) o inactividad prolongada mayor a 3 meses.</p>
            <div class="contacto-soporte">Soporte Técnico: AuraTerraClima@hotmail.com</div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$authController = new \Src\Controllers\AuthController();
$climaController = new \Src\Controllers\ClimaController();

if ($path === '/registrar_click') { require_once __DIR__ . '/registrar_click.php'; }
elseif ($path === '/login') { $method === 'POST' ? $authController->handleLoginPost() : $authController->handleLoginGet(); }
elseif ($path === '/register') { $method === 'POST' ? $authController->handleOpenRegisterPost() : $authController->handleRegisterGet(); }
elseif ($path === '/logout') { $authController->handleLogout(); }
elseif ($path === '/clima/actual') { $climaController->handleClimaActual(); }
elseif ($path === '/clima/pronostico') { $climaController->handleClimaPronostico(); }
else { 
    if ($method === 'GET' && ($path === '/' || $path === '/index.php' || $path === '')) {
        header('Location: /auraTerraMayo/login'); exit;
    }
    require_once __DIR__ . '/dashboard.php'; exit; 
}