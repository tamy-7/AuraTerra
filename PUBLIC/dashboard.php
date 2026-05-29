<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /auraTerraMayo/public/login');
    exit;
}

$nombreUsuario = $_SESSION['user_nombre'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AuraTerra</title>
    
    <link rel="stylesheet" href="/auraTerraMayo/public/css/dashboard.css">
</head>
<body>

    <header>
        <h1>AuraTerra ⛅</h1>
        
        <div class="user-menu-container" id="userMenuBox">
            <div class="user-trigger" id="userTrigger"> 👤 <?php echo htmlspecialchars($nombreUsuario); ?> ▾</div>
            <div class="dropdown-menu" id="dropdownMenu">
                <div class="dropdown-header">📍 Mis Favoritos</div>
                <div id="listaFavoritosContent"></div>
                <a href="/auraTerraMayo/public/logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="buscador-box">
            <input type="text" id="inputCiudad" placeholder="Ej: Paraná, Entre Ríos" value="Paraná">
            <!-- 👇 NUEVO: Botón de Geolocalización por GPS -->
            <button id="btnGps" title="Usar mi ubicación actual" style="background-color: #3182ce; padding: 10px 14px;">📍</button>
            <button class="btn-fav-star" id="btnFav" title="Guardar en favoritos">★</button>
            <button id="btnBuscar">Consultar Clima</button>
        </div>

        <div class="dashboard-layout">
            <div class="card" id="cardActual">
                <h3>Condiciones Actuales</h3>
                <div id="bloqueActual"><p class="loading">Cargando datos...</p></div>
            </div>

            <div class="card" id="cardPronostico">
                <h3>Tendencias (Próximos Días)</h3>
                <div id="bloquePronostico"><p class="loading">Esperando consulta...</p></div>
            </div>

            <div class="card">
                <h3>🛡️ Decisiones de Campo (Próximas 72hs)</h3>
                <div id="bloqueAlertas">
                    <p class="loading">Analizando vectores...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="/auraTerraMayo/public/js/dashboard.js" defer></script>
</body>
</html>