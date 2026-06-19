<?php
declare(strict_types=1);

if (!function_exists('session_start')) {
    function session_start(): bool
    {
        if (!isset($GLOBALS['_SESSION'])) {
            $GLOBALS['_SESSION'] = [];
        }
        return true;
    }
}

if (!function_exists('session_id')) {
    function session_id(?string $id = null): string
    {
        if ($id !== null) {
            $GLOBALS['_SESSION']['__session_id'] = $id;
        }
        return $GLOBALS['_SESSION']['__session_id'] ?? '';
    }
}

$sessionSupported = function_exists('session_start') && function_exists('session_id');
if ($sessionSupported) {
    if (session_id() === '' || session_id() === null) {
        session_start();
    }
} elseif (!isset($_SESSION)) {
    $_SESSION = [];
}

// 🛡️ CONTROL DE ACCESO ESTRICTO: Bloqueo total si el rol no es exactamente admin
$rolUsuario = strtolower(trim($_SESSION['user_rol'] ?? 'agricultor'));
$emailUsuario = strtolower(trim($_SESSION['user_email'] ?? 'global'));

if (!isset($_SESSION['user_id']) || $rolUsuario !== 'admin') {
    header('Location: /auraTerraMayo/dashboard.php');
    exit;
}

$nombreUsuario = $_SESSION['user_nombre'] ?? 'Administrador';

try {
    $pdo = new \PDO("mysql:host=localhost;dbname=auraterra_db;charset=utf8", "root", "");
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch(\Exception $e) { 
    die("<div style='font-family:sans-serif; padding:30px; background:#fff5f5; color:#c53030; border-radius:8px; margin:20px; border:1px solid #fed7d7;'><h3>⚠️ Error de Conexión</h3><p>" . $e->getMessage() . "</p></div>"); 
}

/**
 * 🔄 MÓDULO INTERACTIVO: CAMBIO Y ALTERNANCIA DE ESTADOS DE LICENCIA
 */
if (isset($_GET['cambiar_estado_id'])) {
    $idUsuario = (int)$_GET['cambiar_estado_id'];
    
    $query = $pdo->prepare("SELECT estado FROM usuarios WHERE id = ?");
    $query->execute([$idUsuario]); 
    $userActual = $query->fetch(\PDO::FETCH_ASSOC);
    
    if ($userActual) {
        $estadoActual = strtolower(trim($userActual['estado']));
        
        // Rotación cíclica de estados: prueba -> activo -> suspendido -> prueba
        if ($estadoActual === 'prueba' || $estadoActual === 'bajo prueba') {
            $nuevoEstado = 'activo';
        } elseif ($estadoActual === 'activo' || $estadoActual === 'activado') {
            $nuevoEstado = 'suspendido';
        } else {
            $nuevoEstado = 'prueba';
        }
        
        $update = $pdo->prepare("UPDATE usuarios SET estado = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$nuevoEstado, $idUsuario]);
    }
    header('Location: /auraTerraMayo/admin_console.php'); 
    exit;
}

// 📊 ADQUISICIÓN DE LOGS Y VECTORES ESTADÍSTICOS REPARADOS
$usuarios = $pdo->query("SELECT id, nombre, email, rol, estado FROM usuarios ORDER BY id DESC")->fetchAll(\PDO::FETCH_ASSOC);

// ✅ REPARADO: Búsqueda de strings limpia para que la tabla liste el historial telemétrico sin conflictos de emojis
$telemetria = $pdo->query("SELECT usuario, componente_clickeado, fecha_hora FROM telemetria_clicks ORDER BY id DESC LIMIT 15")->fetchAll(\PDO::FETCH_ASSOC);

$rankingCiudades = $pdo->query("
    SELECT DISTINCT componente_clickeado AS ciudad, COUNT(*) AS total_busquedas
    FROM telemetria_clicks 
    WHERE componente_clickeado LIKE '%Buscó Ciudad:%'
    GROUP BY componente_clickeado 
    ORDER BY total_busquedas DESC 
    LIMIT 5
")->fetchAll(\PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consola de Administración - AuraTerra</title>
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f7f6; padding: 30px; color: #2d3748; margin: 0; }
        .box-premium { background: white; padding: 35px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.04); max-width: 1300px; margin: 0 auto; border: 1px solid #e2e8f0; }
        h1 { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #edf2f7; padding-bottom: 20px; margin: 0; font-size: 1.8rem; font-weight: 800; color: #1a202c; }
        .btn-volver { background: #3182ce; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 0.95rem; box-shadow: 0 4px 6px rgba(49, 130, 206, 0.15); }
        .btn-volver:hover { background: #2b6cb0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 25px; border-radius: 8px; overflow: hidden; }
        th, td { padding: 14px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #edf2f7; color: #4a5568; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        tr:hover { background: #f8fafc; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-activo { background: #c6f6d5; color: #22543d; }
        .badge-prueba { background: #feebc8; color: #744210; }
        .badge-suspendido { background: #fed7d7; color: #9b2c2c; }
        
        /* ✅ CORREGIDO: Removido el # de white para estabilizar los estilos visuales */
        .btn-toggle { background: white; color: #4a5568; text-decoration: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; border: 1px solid #cbd5e0; transition: all 0.2s; display: inline-block; }
        .btn-toggle:hover { background: #edf2f7; color: #1a202c; border-color: #a0aec0; }
        
        .grid-admin-dashboard { display: grid; grid-template-columns: 1.8fr 1fr; gap: 30px; margin-top: 35px; }
        .podio-item-premium { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f8fafc; border-left: 4px solid #3182ce; margin-bottom: 10px; border-radius: 0 8px 8px 0; font-weight: 600; border: 1px solid #e2e8f0; border-left-width: 4px; }
    </style>
</head>
<body>
    <div class="box-premium">
        <h1>
            <span>👑 Consola de Ingeniería y Control Perimetral</span>
            <a href="/auraTerraMayo/dashboard.php" class="btn-volver">⬅️ Volver al Dashboard</a>
        </h1>
        
        <div style="background: #ebf8ff; padding: 15px; border-radius: 8px; margin-top: 20px; border: 1px solid #bee3f8; color: #2b6cb0; font-weight: 600;">
            👤 Operador de Infraestructura Activo: <span style="color:#2c5282; font-weight:800;"><?php echo htmlspecialchars($nombreUsuario); ?></span> (Permisos Globales Concedidos)
        </div>

        <h3 style="margin-top: 35px; color:#2b6cb0; font-size: 1.3rem; font-weight: 700; margin-bottom: 5px;">¼ Matriz General de Cuentas y Clientes</h3>
        <p style="color:#718096; font-size: 0.9rem; margin: 0 0 15px 0;">Hacé clic en "Alternar Estado" para conmutar cíclicamente las licencias operativas de los lotes.</p>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre y Apellido</th>
                    <th>Email Corporativo</th>
                    <th>Rol Asignado</th>
                    <th>Estado de Licencia</th>
                    <th>Acción del Sistema</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): 
                    $est = strtolower(trim($u['estado'] ?? 'prueba'));
                    $claseBadge = ($est === 'activo' || $est === 'activado') ? 'badge-activo' : (($est === 'suspendido') ? 'badge-suspendido' : 'badge-prueba');
                    $textoMostrar = ($est === 'activo' || $est === 'activado') ? 'Activo' : (($est === 'suspendido') ? 'Suspendido' : 'Bajo Prueba');
                ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><b><?php echo htmlspecialchars($u['nombre']); ?></b></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge" style="background:#e9d8fd; color:#44337a; font-size: 0.75rem;"><?php echo htmlspecialchars($u['rol']); ?></span></td>
                    <td><span class="badge <?php echo $claseBadge; ?>"><?php echo $textoMostrar; ?></span></td>
                    <td>
                        <a href="/auraTerraMayo/admin_console.php?cambiar_estado_id=<?php echo $u['id']; ?>" class="btn-toggle">🔄 Alternar Estado</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="grid-admin-dashboard">
            <div>
                <h3 style="color:#dd6b20; font-size: 1.3rem; margin-bottom: 15px;">📝 Telemetría Reciente (Rastreo de Clicks e Interacciones)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Componente / Acción Registrada</th>
                            <th>Fecha y Hora Local</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($telemetria)): ?>
                            <tr><td colspan="3" style="color:#a0aec0; text-align:center; font-style:italic; padding: 20px;">Esperando disparos de interacción telemétrica perimetral...</td></tr>
                        <?php else: ?>
                            <?php foreach ($telemetria as $t): ?>
                            <tr>
                                <td><b><?php echo htmlspecialchars($t['usuario']); ?></b></td>
                                <td style="color:#3182ce; font-weight:600;"><?php echo htmlspecialchars($t['componente_clickeado']); ?></td>
                                <td style="font-size:0.9rem; color:#718096;"><?php echo $t['fecha_hora']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div>
                <h3 style="color:#2b6cb0; font-size: 1.3rem; margin-bottom: 15px;">🏆 Podio de Localidades Más Consultadas</h3>
                <div style="margin-top: 15px;">
                    <?php if (empty($rankingCiudades)): ?>
                        <p style="color:#a0aec0; font-style:italic; background:#f7fafc; padding:20px; border-radius:8px; text-align:center; border: 1px dashed #cbd5e0;">No hay estadísticas acumuladas en este lote de sesión.</p>
                    <?php else: ?>
                        <?php foreach ($rankingCiudades as $puesto => $rc): 
                            $cleanCiudad = str_replace(['🔍 Buscó Ciudad: ', 'Buscó Ciudad: '], '', $rc['ciudad']);
                        ?>
                            <div class="podio-item-premium">
                                <span style="color:#2d3748;"><b style="color:#3182ce; margin-right:5px;"><?php echo ($puesto + 1); ?>°.</b> <?php echo htmlspecialchars($cleanCiudad); ?></span>
                                <span style="color:#3182ce; background:#ebf8ff; padding:4px 10px; border-radius:12px; font-size:0.8rem; font-weight:bold;">📊 Consultas: <?php echo $rc['total_busquedas']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>