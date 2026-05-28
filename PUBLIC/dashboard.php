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
    <title>Dashboard - AuraTerra</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: #f0f4f8; color: #333; min-height: 100vh; }
        
        /* BARRA SUPERIOR PROFESIONAL */
        header { 
            background-color: #2c3e50; color: white; padding: 15px 30px; 
            display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: relative; z-index: 10;
        }
        header h1 { margin: 0; font-size: 1.4rem; font-weight: bold; }
        
        /* MENU DESPLEGABLE DEL USUARIO */
        .user-menu-container { position: relative; cursor: pointer; }
        .user-trigger { font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.1); transition: background 0.2s; }
        .user-trigger:hover { background: rgba(255,255,255,0.2); }
        .dropdown-menu { 
            position: absolute; right: 0; top: 40px; background: white; 
            min-width: 220px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); 
            display: none; flex-direction: column; overflow: hidden; border: 1px solid #e2e8f0;
        }
        .dropdown-menu.show { display: flex; }
        .dropdown-header { padding: 12px; font-size: 0.8rem; text-transform: uppercase; color: #a0aec0; background: #f7fafc; font-weight: bold; border-bottom: 1px solid #edf2f7; }
        .favorito-item { padding: 10px 15px; color: #4a5568; text-decoration: none; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; }
        .favorito-item:hover { background: #f7fafc; color: #2b6cb0; }
        .no-favs { padding: 12px 15px; font-size: 0.9rem; color: #a0aec0; font-style: italic; }
        .btn-logout { background-color: #fff5f5; color: #e53e3e; padding: 12px 15px; text-decoration: none; font-weight: bold; border-top: 1px solid #edf2f7; transition: background 0.2s; }
        .btn-logout:hover { background-color: #fed7d7; }

        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .bienvenida { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; border-left: 5px solid #8cb89f; }
        
        /* BUSCADOR CON ESTRELLA DE FAVORITO */
        .buscador-box { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 25px; display: flex; gap: 10px; align-items: center; }
        .buscador-box input { flex: 1; padding: 12px; border: 1px solid #cbd5e0; border-radius: 8px; font-size: 1rem; }
        .buscador-box button { background-color: #27ae60; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 1rem; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .buscador-box button:hover { background-color: #219653; }
        .btn-fav-star { background: none; border: none; font-size: 1.7rem; cursor: pointer; color: #cbd5e0; transition: transform 0.2s, color 0.2s; padding: 0 5px; }
        .btn-fav-star.active { color: #f6ad55; transform: scale(1.15); }

        .clima-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        @media (max-width: 768px) { .clima-grid { grid-template-columns: 1fr; } }
        
        .card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.5s ease; }
        .card h3 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; margin-bottom: 15px; }
        
        /* FONDOS DINÁMICOS SEGÚN EL CLIMA */
        .clima-clear { background: linear-gradient(135deg, #ffefba 0%, #ffffff 100%) !important; border-left: 6px solid #f6ad55; }
        .clima-clouds { background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 100%) !important; border-left: 6px solid #4a5568; }
        .clima-rain { background: linear-gradient(135deg, #bee3f8 0%, #ffffff 100%) !important; border-left: 6px solid #3182ce; }
        .clima-thunderstorm { background: linear-gradient(135deg, #feebc8 0%, #cbd5e0 100%) !important; border-left: 6px solid #dd6b20; position: relative; }
        
        .temp-principal { font-size: 3.5rem; font-weight: bold; color: #2d3748; margin: 15px 0; }
        .pronostico-item { padding: 15px; border-radius: 10px; margin-bottom: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: all 0.4s; }
        .loading { color: #7f8c8d; font-style: italic; }
    </style>
</head>
<body>

    <header>
        <h1>AuraTerra ⛅</h1>
        
        <div class="user-menu-container" id="userMenuBox">
            <div class="user-trigger" id="userTrigger">👤 <?php echo htmlspecialchars($nombreUsuario); ?> ▾</div>
            <div class="dropdown-menu" id="dropdownMenu">
                <div class="dropdown-header">📍 Mis Favoritos</div>
                <div id="listaFavoritosContent">
                    </div>
                <a href="/auraTerraMayo/public/logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="bienvenida">
            <h2>Planificador Agroclimático Inteligente</h2>
            <p>Monitoreá en tiempo real y organizá tus actividades al aire libre con precisión biológica.</p>
        </div>

        <div class="buscador-box">
            <input type="text" id="inputCiudad" placeholder="Ej: Paraná, Entre Ríos" value="Paraná">
            <button class="btn-fav-star" id="btnFav" title="Guardar en favoritos">★</button>
            <button id="btnBuscar">Consultar Clima</button>
        </div>

        <div class="clima-grid">
            <div class="card" id="cardActual">
                <h3>Condiciones Actuales</h3>
                <div id="bloqueActual">
                    <p class="loading">Ingresá una ciudad para comenzar.</p>
                </div>
            </div>

            <div class="card" id="cardPronostico">
                <h3>Pronóstico Extendido</h3>
                <div id="bloquePronostico">
                    <p class="loading">Esperando consulta...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let ciudadActualCargada = "Paraná";

        // --- LÓGICA DEL MENÚ DESPLEGABLE ---
        const userTrigger = document.getElementById('userTrigger');
        const dropdownMenu = document.getElementById('dropdownMenu');

        userTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            dropdownMenu.classList.remove('show');
        });

        // --- LÓGICA DE DETECCIÓN DE ESTILOS CLIMÁTICOS ---
        function obtenerClaseClima(mainWeather) {
            switch(mainWeather.toLowerCase()) {
                case 'clear': return { clase: 'clima-clear', icono: '☀️' };
                case 'clouds': return { clase: 'clima-clouds', icono: '☁️' };
                case 'rain': 
                case 'drizzle': return { clase: 'clima-rain', icono: '🌧️' };
                case 'thunderstorm': return { clase: 'clima-thunderstorm', icono: '⚡⛈️' };
                default: return { clase: 'clima-clouds', icono: '🌤️' };
            }
        }

        // --- GESTIÓN DE UBICACIONES FAVORITAS (localStorage) ---
        const btnFav = document.getElementById('btnFav');

        function obtenerFavoritos() {
            return JSON.parse(localStorage.getItem('auraterra_favs')) || [];
        }

        function actualizarEstrellaFavorito(ciudad) {
            const favs = obtenerFavoritos();
            if (favs.map(c => c.toLowerCase()).includes(ciudad.toLowerCase())) {
                btnFav.classList.add('active');
            } else {
                btnFav.classList.remove('active');
            }
        }

        function renderizarMenuFavoritos() {
            const favs = obtenerFavoritos();
            const contenedorMenu = document.getElementById('listaFavoritosContent');
            contenedorMenu.innerHTML = '';

            if (favs.length === 0) {
                contenedorMenu.innerHTML = '<div class="no-favs">No hay ubicaciones favoritas</div>';
                return;
            }

            favs.forEach(ciudad => {
                contenedorMenu.innerHTML += `
                    <a href="#" class="favorito-item" onclick="cargarCiudadDesdeFavs('${ciudad}')">
                        <span>📍 ${ciudad}</span>
                        <span style="color:#f6ad55;">★</span>
                    </a>
                `;
            });
        }

        btnFav.addEventListener('click', () => {
            let favs = obtenerFavoritos();
            const ciudadNorm = ciudadActualCargada;

            if (favs.map(c => c.toLowerCase()).includes(ciudadNorm.toLowerCase())) {
                // Si ya existe, la sacamos
                favs = favs.filter(c => c.toLowerCase() !== ciudadNorm.toLowerCase());
                btnFav.classList.remove('active');
            } else {
                // Si no existe, la sumamos
                favs.push(ciudadNorm);
                btnFav.classList.add('active');
            }
            localStorage.setItem('auraterra_favs', JSON.stringify(favs));
            renderizarMenuFavoritos();
        });

        function cargarCiudadDesdeFavs(ciudad) {
            document.getElementById('inputCiudad').value = ciudad;
            consultarClimaActual(ciudad);
            consultarPronostico(ciudad);
            dropdownMenu.classList.remove('show');
        }


        // --- DISPARADOR DEL BUSCADOR ---
        document.getElementById('btnBuscar').addEventListener('click', () => {
            const ciudad = document.getElementById('inputCiudad').value.trim();
            if (!ciudad) return;
            consultarClimaActual(ciudad);
            consultarPronostico(ciudad);
        });


        // --- CONSULTA: CLIMA ACTUAL ---
        async function consultarClimaActual(ciudad) {
            const bloque = document.getElementById('bloqueActual');
            const cardActual = document.getElementById('cardActual');
            bloque.innerHTML = '<p class="loading">Cargando condiciones actuales...</p>';

            try {
                const response = await fetch(`/auraTerraMayo/public/clima/actual?ciudad=${encodeURIComponent(ciudad)}`);
                const resultado = await response.json();

                if (!resultado.ok) {
                    bloque.innerHTML = `<p style="color: #e74c3c;"><b>Error:</b> ${resultado.error}</p>`;
                    return;
                }

                const clima = resultado.data;
                ciudadActualCargada = clima.ubicacion; // Guardamos el nombre oficial devuelto
                actualizarEstrellaFavorito(ciudadActualCargada);

                // Averiguamos el fondo correspondiente según el clima que mande OpenWeather
                // Nota: se asume que tu controlador no procesa el campo raw "main" del clima de OpenWeather,
                // por lo que deduciremos el estado según palabras clave en la descripción en español.
                let estadoFalso = 'clouds';
                const desc = clima.descripcion.toLowerCase();
                if (desc.includes('despejado') || desc.includes('claro') || desc.includes('sol')) estadoFalso = 'clear';
                else if (desc.includes('lluvia') || desc.includes('llovizna') || desc.includes('chubasco')) estadoFalso = 'rain';
                else if (desc.includes('tormenta') || desc.includes('rayo')) estadoFalso = 'thunderstorm';

                const configVisual = obtenerClaseClima(estadoFalso);

                // Aplicamos el fondo dinámico a la tarjeta
                cardActual.className = `card ${configVisual.clase}`;

                bloque.innerHTML = `
                    <div class="info-clima">
                        <p style="font-size: 1.2rem; color: #4a5568;">Ubicación: <b>${clima.ubicacion}</b></p>
                        <div class="temp-principal">${Math.round(clima.temperatura)}°C <span style="font-size:2.5rem;">${configVisual.icono}</span></div>
                        <p style="text-transform: capitalize; font-size:1.1rem;"><b>Condición:</b> ${clima.descripcion}</p>
                        <p style="margin-top: 5px;"><b>Humedad Ambiente:</b> ${clima.humedad}%</p>
                        <p style="margin-top: 5px;"><b>Velocidad del Viento:</b> ${clima.viento} m/s</p>
                        <hr style="border: 0; border-top: 1px solid rgba(0,0,0,0.06); margin: 15px 0;">
                        <small style="color: #718096;">Actualizado: ${clima.timestamp}</small>
                    </div>
                `;
            } catch (error) {
                bloque.innerHTML = '<p style="color: #e74c3c;">Error de comunicación con el backend.</p>';
            }
        }

        // --- CONSULTA: PRONÓSTICO EXTENDIDO ---
        async function consultarPronostico(ciudad) {
            const bloque = document.getElementById('bloquePronostico');
            bloque.innerHTML = '<p class="loading">Procesando tendencias climáticas...</p>';

            try {
                const response = await fetch(`/auraTerraMayo/public/clima/pronostico?ciudad=${encodeURIComponent(ciudad)}`);
                const resultado = await response.json();

                if (resultado.error) {
                    bloque.innerHTML = `<p style="color: #e74c3c;"><b>Error:</b> ${resultado.mensaje}</p>`;
                    return;
                }

                const lista = resultado.data;
                if (!lista || lista.length === 0) {
                    bloque.innerHTML = '<p>No hay datos proyectados.</p>';
                    return;
                }

                bloque.innerHTML = '';
                const pronosticoDiario = lista.filter(item => item.dt_txt.includes("12:00:00"));
                const itemsAProcesar = pronosticoDiario.length > 0 ? pronosticoDiario : lista.slice(0, 5);

                itemsAProcesar.forEach(item => {
                    const fechaObj = new Date(item.dt * 1000);
                    const fechaFormateada = fechaObj.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric' });
                    
                    const temp = Math.round(item.main.temp);
                    const desc = item.weather[0].description;
                    const mainWeatherRaw = item.weather[0].main; // Acá sí tenemos el raw de OpenWeather
                    const configVisualItem = obtenerClaseClima(mainWeatherRaw);

                    bloque.innerHTML += `
                        <div class="pronostico-item ${configVisualItem.clase}">
                            <h4 style="text-transform: capitalize;">${fechaFormateada} ${configVisualItem.icono}</h4>
                            <p style="margin: 3px 0;"><b>${temp}°C</b> - <span style="text-transform: capitalize; font-size:0.95rem;">${desc}</span></p>
                            <small style="color: #4a5568;">Humedad: ${item.main.humidity}%</small>
                        </div>
                    `;
                });

            } catch (error) {
                bloque.innerHTML = '<p style="color: #e74c3c;">Error al armar el mapa de pronósticos.</p>';
            }
        }

        // CARGA INICIAL AUTOMÁTICA
        window.addEventListener('DOMContentLoaded', () => {
            renderizarMenuFavoritos();
            const ciudadInicial = document.getElementById('inputCiudad').value;
            if(ciudadInicial) {
                consultarClimaActual(ciudadInicial);
                consultarPronostico(ciudadInicial);
            }
        });
    </script>
</body>
</html>