<?php
declare(strict_types=1);
session_start();

// Control de acceso: Si no hay usuario en la sesión, rebota al login
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
    <title>Dashboard AuraTerra</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; background-color: #f0f4f8; color: #333; }
        header { background-color: #2c3e50; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        header h1 { margin: 0; font-size: 1.5rem; }
        .btn-logout { background-color: #e74c3c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; transition: background 0.2s; }
        .btn-logout:hover { background-color: #c0392b; }
        
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .bienvenida { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; }
        
        .buscador-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; display: flex; gap: 10px; }
        .buscador-box input { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        .buscador-box button { background-color: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-size: 1rem; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .buscador-box button:hover { background-color: #219653; }
        
        .clima-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .clima-grid { grid-template-columns: 1fr; } }
        
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        
        .info-clima p { margin: 12px 0; font-size: 1.1rem; }
        .temp-principal { font-size: 3rem; font-weight: bold; color: #2980b9; margin: 10px 0; }
        
        .pronostico-item { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 12px; border-left: 4px solid #2980b9; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .pronostico-item h4 { margin: 0 0 5px 0; text-transform: capitalize; color: #34495e; }
        .pronostico-item p { margin: 0; font-size: 1.05rem; }
        .pronostico-item small { color: #7f8c8d; }
        
        .loading { color: #7f8c8d; font-style: italic; }
    </style>
</head>
<body>

    <header>
        <h1>AuraTerra - Panel de Control</h1>
        <div>
            <span style="margin-right: 15px; font-weight: 500;">Hola, <?php echo htmlspecialchars($nombreUsuario); ?>!</span>
            <a href="/auraTerraMayo/public/logout" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <div class="container">
        <div class="bienvenida">
            <h2>Bienvenida al Analizador Agroclimático</h2>
            <p>Ingresá una ciudad para obtener las condiciones climáticas actuales y las recomendaciones de planificación para los próximos días.</p>
        </div>

        <div class="buscador-box">
            <input type="text" id="inputCiudad" placeholder="Ej: Paraná, Entre Ríos" value="Paraná">
            <button id="btnBuscar">Consultar Clima</button>
        </div>

        <div class="clima-grid">
            <div class="card">
                <h3>Condiciones Actuales</h3>
                <div id="bloqueActual">
                    <p class="loading">Ingresá una ciudad y hacé clic en Consultar.</p>
                </div>
            </div>

            <div class="card">
                <h3>Pronóstico Extendidos (Próximos Días)</h3>
                <div id="bloquePronostico">
                    <p class="loading">Esperando consulta...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('btnBuscar').addEventListener('click', () => {
            const ciudad = document.getElementById('inputCiudad').value.trim();
            if (!ciudad) {
                alert('Por favor, ingresá el nombre de una ciudad.');
                return;
            }
            consultarClimaActual(ciudad);
            consultarPronostico(ciudad);
        });

        // 1. Llamada a Clima Actual
        async function consultarClimaActual(ciudad) {
            const bloque = document.getElementById('bloqueActual');
            bloque.innerHTML = '<p class="loading">Cargando clima actual...</p>';

            try {
                const response = await fetch(`/auraTerraMayo/public/clima/actual?ciudad=${encodeURIComponent(ciudad)}`);
                const resultado = await response.json();

                if (!resultado.ok) {
                    bloque.innerHTML = `<p style="color: #e74c3c;"><b>Error:</b> ${resultado.error || 'No se pudieron cargar los datos.'}</p>`;
                    return;
                }

                const clima = resultado.data;
                bloque.innerHTML = `
                    <div class="info-clima">
                        <p style="font-size: 1.3rem; margin: 0; color: #7f8c8d;">Ubicación: <b>${clima.ubicacion}</b></p>
                        <div class="temp-principal">${Math.round(clima.temperatura)}°C</div>
                        <p style="text-transform: capitalize;"><b>Estado:</b> ${clima.descripcion}</p>
                        <p><b>Humedad:</b> ${clima.humedad}%</p>
                        <p><b>Viento:</b> ${clima.viento} m/s</p>
                        <hr style="border: 0; border-top: 1px solid #ecf0f1; margin: 15px 0;">
                        <small style="color: #95a5a6;">Fuente: ${clima.fuente} | Actualizado: ${clima.timestamp}</small>
                    </div>
                `;
            } catch (error) {
                console.error(error);
                bloque.innerHTML = '<p style="color: #e74c3c;">Error de conexión con el servidor.</p>';
            }
        }

        // 2. Llamada a Pronóstico Extendidos
        async function consultarPronostico(ciudad) {
            const bloque = document.getElementById('bloquePronostico');
            bloque.innerHTML = '<p class="loading">Cargando pronóstico...</p>';

            try {
                const response = await fetch(`/auraTerraMayo/public/clima/pronostico?ciudad=${encodeURIComponent(ciudad)}`);
                const resultado = await response.json();

                // Validamos según la nueva estructura de ClimaService
                if (resultado.error) {
                    bloque.innerHTML = `<p style="color: #e74c3c;"><b>Error:</b> ${resultado.mensaje || 'No se pudo obtener el pronóstico.'}</p>`;
                    return;
                }

                const lista = resultado.data;
                if (!lista || lista.length === 0) {
                    bloque.innerHTML = '<p>No hay datos de pronóstico disponibles.</p>';
                    return;
                }

                bloque.innerHTML = ''; // Limpiamos el cargando

                // OpenWeather devuelve registros cada 3 horas. Filtramos para mostrar solo los de las 12:00 de cada día
                const pronosticoDiario = lista.filter(item => item.dt_txt.includes("12:00:00"));

                // Si por la hora del día el filtro queda vacío, tomamos los primeros 5 registros para no dejar la pantalla en blanco
                const itemsAProcesar = pronosticoDiario.length > 0 ? pronosticoDiario : lista.slice(0, 5);

                itemsAProcesar.forEach(item => {
                    // Formateamos la fecha al estilo local (Ej: martes 26)
                    const fechaObj = new Date(item.dt * 1000);
                    const fechaFormateada = fechaObj.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric' });
                    
                    const temp = Math.round(item.main.temp);
                    const desc = item.weather[0].description;
                    const humedad = item.main.humidity;

                    bloque.innerHTML += `
                        <div class="pronostico-item">
                            <h4>${fechaFormateada}</h4>
                            <p><b>${temp}°C</b> - <span style="text-transform: capitalize;">${desc}</span></p>
                            <small>Humedad promedio: ${humedad}%</small>
                        </div>
                    `;
                });

            } catch (error) {
                console.error(error);
                bloque.innerHTML = '<p style="color: #e74c3c;">Error al conectar con el servicio de pronósticos.</p>';
            }
        }

        // Carga automática inicial al entrar al Dashboard
        window.addEventListener('DOMContentLoaded', () => {
            const ciudadInicial = document.getElementById('inputCiudad').value;
            if(ciudadInicial) {
                consultarClimaActual(ciudadInicial);
                consultarPronostico(ciudadInicial);
            }
        });
    </script>
</body>
</html>