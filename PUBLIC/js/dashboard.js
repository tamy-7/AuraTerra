let ciudadActualCargada = "Paraná";

// --- CONTROL DEL MENÚ DESPLEGABLE ---
const userTrigger = document.getElementById('userTrigger');
const dropdownMenu = document.getElementById('dropdownMenu');
if (userTrigger && dropdownMenu) {
    userTrigger.addEventListener('click', (e) => { 
        e.stopPropagation(); 
        dropdownMenu.classList.toggle('show'); 
    });
    document.addEventListener('click', () => { 
        dropdownMenu.classList.remove('show'); 
    });
}

// --- TRADUCTOR VISUAL DE CLIMA ---
function obtenerClaseClima(mainWeather) {
    switch(mainWeather.toLowerCase()) {
        case 'clear': return { clase: 'clima-clear', icono: '☀️' };
        case 'clouds': return { clase: 'clima-clouds', icono: '☁️' };
        case 'rain': case 'drizzle': return { clase: 'clima-rain', icono: '🌧️' };
        case 'thunderstorm': return { clase: 'clima-thunderstorm', icono: '⚡⛈️' };
        default: return { clase: 'clima-clouds', icono: '🌤️' };
    }
}

// --- GESTIÓN DE FAVORITOS (localStorage) ---
const btnFav = document.getElementById('btnFav');

function obtenerFavoritos() { 
    return JSON.parse(localStorage.getItem('auraterra_favs')) || []; 
}

function actualizarEstrellaFavorito(ciudad) {
    if (!btnFav) return;
    if (obtenerFavoritos().map(c => c.toLowerCase()).includes(ciudad.toLowerCase())) {
        btnFav.classList.add('active');
    } else {
        btnFav.classList.remove('active');
    }
}

function renderizarMenuFavoritos() {
    const favs = obtenerFavoritos();
    const contenedorMenu = document.getElementById('listaFavoritosContent');
    if (!contenedorMenu) return;
    contenedorMenu.innerHTML = '';
    
    if (favs.length === 0) { 
        contenedorMenu.innerHTML = '<div class="no-favs">No hay favoritos</div>'; 
        return; 
    }
    
    // MEJORA: Agregamos el botón de eliminar (❌) a cada ítem de la lista
    favs.forEach(c => {
        contenedorMenu.innerHTML += `
            <div class="favorito-item" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px;">
                <span onclick="cargarCiudadDesdeFavs('${c}')" style="cursor:pointer; flex: 1;">📍 ${c}</span>
                <span onclick="eliminarFavoritoIndividual(event, '${c}')" style="cursor:pointer; color:#e53e3e; font-size:0.8rem; padding: 2px 5px;" title="Eliminar">❌</span>
            </div>`;
    });
}

// NUEVO: Función para eliminar un favorito individual desde el menú desplegable
function eliminarFavoritoIndividual(event, ciudadAEliminar) {
    event.stopPropagation(); // Evita que se cierre el menú al hacer clic
    let favs = obtenerFavoritos();
    favs = favs.filter(c => c.toLowerCase() !== ciudadAEliminar.toLowerCase());
    localStorage.setItem('auraterra_favs', JSON.stringify(favs));
    
    renderizarMenuFavoritos();
    actualizarEstrellaFavorito(ciudadActualCargada);
}

if (btnFav) {
    btnFav.addEventListener('click', () => {
        let favs = obtenerFavoritos();
        if (favs.map(c => c.toLowerCase()).includes(ciudadActualCargada.toLowerCase())) {
            favs = favs.filter(c => c.toLowerCase() !== ciudadActualCargada.toLowerCase());
        } else { 
            favs.push(ciudadActualCargada); 
        }
        localStorage.setItem('auraterra_favs', JSON.stringify(favs));
        actualizarEstrellaFavorito(ciudadActualCargada);
        renderizarMenuFavoritos();
    });
}

function cargarCiudadDesdeFavs(c) { 
    const input = document.getElementById('inputCiudad');
    if (input) input.value = c; 
    ejecutarConsultasPorNombre(c); 
}

// --- DISPARADORES DE BÚSQUEDA ---
const btnBuscar = document.getElementById('btnBuscar');
if (btnBuscar) {
    btnBuscar.addEventListener('click', () => {
        const input = document.getElementById('inputCiudad');
        const c = input ? input.value.trim() : '';
        if (c) ejecutarConsultasPorNombre(c);
    });
}

function ejecutarConsultasPorNombre(c) { 
    consultarClimaActual(`/auraTerraMayo/public/clima/actual?ciudad=${encodeURIComponent(c)}`); 
    consultarPronostico(`/auraTerraMayo/public/clima/pronostico?ciudad=${encodeURIComponent(c)}`); 
}

// --- NUEVO: GEOLOCALIZACIÓN POR COORDENADAS (GPS) ---
const btnGps = document.getElementById('btnGps');
if (btnGps) {
    btnGps.addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert("Tu navegador no soporta geolocalización.");
            return;
        }
        
        btnGps.innerText = "⏳";
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Le pegamos a las URLs de tu backend usando latitud y longitud directas
                consultarClimaActual(`/auraTerraMayo/public/clima/actual?lat=${lat}&lon=${lon}`);
                consultarPronostico(`/auraTerraMayo/public/clima/pronostico?lat=${lat}&lon=${lon}`);
                
                btnGps.innerText = "📍";
            },
            (error) => {
                alert("No se pudo obtener tu ubicación. Verifica los permisos de tu navegador.");
                btnGps.innerText = "📍";
            }
        );
    });
}


// --- FETCH CENTRALIZADO: CLIMA ACTUAL ---
async function consultarClimaActual(endpointUrl) {
    const bloque = document.getElementById('bloqueActual');
    const cardActual = document.getElementById('cardActual');
    if (!bloque || !cardActual) return;
    
    try {
        const response = await fetch(endpointUrl);
        const res = await response.json();
        if (!res.ok) { bloque.innerHTML = `<p style="color:#e74c3c;">${res.error}</p>`; return; }

        const clima = res.data;
        ciudadActualCargada = clima.ubicacion;
        
        const input = document.getElementById('inputCiudad');
        if (input) input.value = ciudadActualCargada; // Seteamos el buscador con la ubicación oficial
        
        actualizarEstrellaFavorito(ciudadActualCargada);

        let estado = 'clouds';
        const desc = clima.descripcion.toLowerCase();
        if (desc.includes('despejado') || desc.includes('claro')) estado = 'clear';
        else if (desc.includes('lluvia') || desc.includes('llovizna')) estado = 'rain';
        else if (desc.includes('tormenta')) estado = 'thunderstorm';

        const visual = obtenerClaseClima(estado);
        cardActual.className = `card ${visual.clase}`;

        bloque.innerHTML = `
            <div style="font-size: 0.95rem;">
                <p style="color: #718096; margin-bottom:5px;">📍 <b>${clima.ubicacion}</b></p>
                <div class="temp-principal">${Math.round(clima.temperatura)}°C <span style="font-size:2rem;">${visual.icono}</span></div>
                <p style="text-transform: capitalize; font-weight:600; color:#4a5568;">${clima.descripcion}</p>
                <p style="margin-top:8px;">💧 <b>Humedad:</b> ${clima.humedad}%</p>
                <p style="margin-top:4px;">💨 <b>Viento:</b> ${clima.viento} m/s</p>
                <small style="display:block; margin-top:15px; color:#a0aec0; font-size:0.75rem;">Sincronizado: ${clima.timestamp}</small>
            </div>
        `;
    } catch (e) { 
        bloque.innerHTML = '<p style="color:#e74c3c;">Error backend.</p>'; 
    }
}

// --- FETCH CENTRALIZADO: PRONÓSTICO & ALERTAS A 72 HORAS ---
async function consultarPronostico(endpointUrl) {
    const bloquePron = document.getElementById('bloquePronostico');
    const bloqueAlertas = document.getElementById('bloqueAlertas');
    if (!bloquePron || !bloqueAlertas) return;
    
    try {
        const response = await fetch(endpointUrl);
        const res = await response.json();
        if (res.error) { bloquePron.innerHTML = `<p style="color:#e74c3c;">${res.mensaje}</p>`; return; }

        const lista = res.data;
        bloquePron.innerHTML = '';
        
        const pronosticoDiario = lista.filter(item => item.dt_txt.includes("12:00:00"));
        const itemsAProcesar = pronosticoDiario.length > 0 ? pronosticoDiario : lista.slice(0, 5);

        let alertasEncontradas = [];
        let flagHelada = false; let flagCosechaPeligro = false; let flagEstres = false; let flagViento = false;

        lista.forEach(b => {
            const fechaB = new Date(b.dt * 1000);
            const limite = new Date(); limite.setDate(limite.getDate() + 3);

            if (fechaB <= limite) {
                const temp = b.main.temp;
                const hum = b.main.humidity;
                const vKmh = b.wind.speed * 3.6;
                const raw = b.weather[0].main.toLowerCase();
                const dia = fechaB.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric' });

                if (temp <= 3 && !flagHelada) {
                    alertasEncontradas.push({ tipo: 'danger', titulo: `❄️ Riesgo Helada (${dia})`, msj: `Mínimas de ${Math.round(temp)}°C. Alistar riego por aspersión o mantas térmicas.` });
                    flagHelada = true;
                }
                if ((raw.includes('rain') || raw.includes('thunderstorm')) && !flagCosechaPeligro) {
                    alertasEncontradas.push({ tipo: 'warning', titulo: `🌾 Postergar Cosecha (${dia})`, msj: `Inestabilidad y lluvias. Alto riesgo de humedad en grano y compactación de suelo.` });
                    flagCosechaPeligro = true;
                }
                if (temp >= 30 && hum >= 70 && !flagEstres) {
                    alertasEncontradas.push({ tipo: 'danger', titulo: `🐔 Estrés Térmico Aviar (${dia})`, msj: `Calor crítico para galpones. Encender foggers/nebulizadores y ajustar ventilación.` });
                    flagEstres = true;
                }
                if (vKmh > 20 && !flagViento) {
                    alertasEncontradas.push({ tipo: 'warning', titulo: `🚜 Fumigación Suspendida (${dia})`, msj: `Vientos de ${Math.round(vKmh)} km/h. Riesgo de deriva grave de fitosanitarios.` });
                    flagViento = true;
                }
            }
        });

        bloqueAlertas.innerHTML = '';
        
        if (alertasEncontradas.length === 0) {
            bloqueAlertas.innerHTML = `
                <div class="alert-box alert-success">
                    <h4 style="font-size:0.95rem;">☀️ Ventana de Trabajo Óptima</h4>
                    <p style="margin: 4px 0 10px 0; font-size:0.85rem;">Condiciones espectaculares para las próximas 72hs. Labores recomendadas:</p>
                    <ul class="action-list">
                        <li><b>Pulverización Óptima:</b> Vientos estables y deriva nula. Ventana ideal para fitosanitarios.</li>
                        <li><b>Cosecha a Pleno:</b> Humedad de suelo y grano en rangos comerciales perfectos.</li>
                        <li><b>Bienestar Avícola:</b> Temperaturas estables en galpones, consumo de alimento normal.</li>
                        <li><b>Siembra y Laboreo:</b> Suelo con temperatura ideal para agilizar la germinación.</li>
                    </ul>
                </div>`;
        } else {
            alertasEncontradas.forEach(al => {
                bloqueAlertas.innerHTML += `
                    <div class="alert-box alert-${al.tipo}">
                        <h4 style="font-size:0.9rem;">${al.titulo}</h4>
                        <p style="margin-top:3px; font-size:0.8rem; line-height:1.3;">${al.msj}</p>
                    </div>`;
            });
        }

        itemsAProcesar.forEach(item => {
            const fObj = new Date(item.dt * 1000);
            const fForm = fObj.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric' });
            const temp = Math.round(item.main.temp);
            const desc = item.weather[0].description;
            const visualI = obtenerClaseClima(item.weather[0].main);

            bloquePron.innerHTML += `
                <div class="pronostico-item ${visualI.clase}">
                    <h4 style="text-transform: capitalize;">${fForm} ${visualI.icono}</h4>
                    <p style="margin-top:2px;"><b>${temp}°C</b> - <span style="text-transform: capitalize; color:#555; font-size:0.85rem;">${desc}</span></p>
                </div>
            `;
        });

    } catch (e) { 
        bloquePron.innerHTML = '<p style="color:#e74c3c;">Error de procesamiento.</p>'; 
    }
}

// --- EJECUCIÓN INICIAL AL CARGAR ---
window.addEventListener('DOMContentLoaded', () => {
    renderizarMenuFavoritos();
    const input = document.getElementById('inputCiudad');
    const ciudadInicial = input ? input.value : 'Paraná';
    ejecutarConsultasPorNombre(ciudadInicial);
});