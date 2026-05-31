<?php

use Helpers\UsuarioHelper;

function handleRegisterGet() {
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro - AuraTerra</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            .auth-container { 
                background: rgba(255, 255, 255, 0.95); 
                padding: 40px 30px; 
                border-radius: 16px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
                width: 100%; 
                max-width: 420px; 
                text-align: center;
                backdrop-filter: blur(5px);
            }
            .brand-header { margin-bottom: 25px; }
            
            /* VOLVIMOS AL LOGO ORIGINAL: Solo el degradado limpio, liso y perfecto */
            .brand-logo-mock {
                width: 70px; height: 70px; margin: 0 auto 10px;
                background: linear-gradient(135deg, #8cb89f, #f1a995);
                border-radius: 50%; opacity: 0.85;
                box-shadow: 0 4px 12px rgba(140, 184, 159, 0.2);
            }
            
            .brand-name { font-size: 1.8rem; color: #2c3e50; font-weight: bold; margin-bottom: 5px; }
            .brand-slogan { font-size: 0.82rem; color: #7f8c8d; line-height: 1.4; font-style: italic; margin-bottom: 15px; }
            h2 { font-size: 1.3rem; color: #34495e; margin-bottom: 20px; font-weight: 600; }
            
            .form-group { margin-bottom: 20px; text-align: left; }
            .form-group label { display: block; margin-bottom: 6px; font-size: 0.9rem; color: #4b6584; font-weight: 600; }
            .form-group input { 
                width: 100%; padding: 12px 15px; border-radius: 8px; 
                border: 1px solid #dcdde1; font-size: 1rem; transition: all 0.3s;
                background-color: #fcfcfc;
            }
            .form-group input:focus { border-color: #27ae60; outline: none; background-color: #fff; box-shadow: 0 0 8px rgba(39, 174, 96, 0.15); }
            
            .btn-submit { 
                background-color: #27ae60; color: white; padding: 12px; width: 100%; 
                border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; 
                font-weight: bold; transition: background 0.2s, transform 0.1s; 
                margin-top: 10px;
            }
            .btn-submit:hover { background-color: #219653; }
            .btn-submit:active { transform: scale(0.98); }
            
            .switch-text { margin-top: 25px; font-size: 0.9rem; color: #7f8c8d; }
            .btn-link { color: #2980b9; text-decoration: none; font-weight: bold; transition: color 0.2s; }
            .btn-link:hover { color: #3498db; text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="brand-header">
                <div class="brand-logo-mock"></div>
                <div class="brand-name">AuraTerra</div>
                <div class="brand-slogan">"Los 21gr del timo que acompañan el exito de la organizacion de tu actividad al aire libre"</div>
            </div>
            <h2>Registro de Usuario</h2>
            <form id="formRegistro">
                <div class="form-group"><label>Nombre Completo</label><input type="text" name="nombre" placeholder="Ej: Juan Pérez" required></div>
                <div class="form-group"><label>Correo Electrónico</label><input type="email" name="email" placeholder="ejemplo@correo.com" required></div>
                <div class="form-group"><label>Contraseña</label><input type="password" name="password" placeholder="Mínimo 6 caracteres" required></div>
                <button type="submit" class="btn-submit">Registrarse</button>
            </form>
            <p class="switch-text">¿Ya tenés cuenta? <a href="/auraTerraMayo/public/login" class="btn-link">Iniciar sesión</a></p>
        </div>

        <script>
        document.getElementById('formRegistro').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('/auraTerraMayo/public/register', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.ok) {
                Swal.fire({
                    title: '¡Registro Exitoso!',
                    text: data.message,
                    icon: 'success',
                    iconColor: '#27ae60',
                    confirmButtonColor: '#27ae60',
                    confirmButtonText: 'Ir al Login ⛅',
                    backdrop: `rgba(46, 204, 113, 0.1)`
                }).then(() => {
                    window.location.href = '/auraTerraMayo/public/login';
                });
            } else {
                let errorMsg = 'Ocurrió un problem.';
                if(data.errors) errorMsg = Object.values(data.errors).join('\\n');
                
                Swal.fire({
                    title: 'Ops... Algo falló',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#e74c3c'
                });
            }
        });
        </script>
    </body>
    </html>
HTML;
    exit;
}

function handleLoginGet() {
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iniciar Sesión - AuraTerra</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            .auth-container { 
                background: rgba(255, 255, 255, 0.95); 
                padding: 40px 30px; 
                border-radius: 16px; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
                width: 100%; 
                max-width: 420px; 
                text-align: center;
                backdrop-filter: blur(5px);
            }
            .brand-header { margin-bottom: 25px; }
            
            /* VOLVIMOS AL LOGO ORIGINAL: Solo el degradado limpio, liso y perfecto */
            .brand-logo-mock {
                width: 70px; height: 70px; margin: 0 auto 10px;
                background: linear-gradient(135deg, #8cb89f, #f1a995);
                border-radius: 50%; opacity: 0.85;
                box-shadow: 0 4px 12px rgba(140, 184, 159, 0.2);
            }
            
            .brand-name { font-size: 1.8rem; color: #2c3e50; font-weight: bold; margin-bottom: 5px; }
            .brand-slogan { font-size: 0.82rem; color: #7f8c8d; line-height: 1.4; font-style: italic; margin-bottom: 15px; }
            h2 { font-size: 1.3rem; color: #34495e; margin-bottom: 20px; font-weight: 600; }
            
            .form-group { margin-bottom: 20px; text-align: left; }
            .form-group label { display: block; margin-bottom: 6px; font-size: 0.9rem; color: #4b6584; font-weight: 600; }
            .form-group input { 
                width: 100%; padding: 12px 15px; border-radius: 8px; 
                border: 1px solid #dcdde1; font-size: 1rem; transition: all 0.3s;
                background-color: #fcfcfc;
            }
            .form-group input:focus { border-color: #007bff; outline: none; background-color: #fff; box-shadow: 0 0 8px rgba(0, 123, 255, 0.15); }
            
            .btn-submit { 
                background-color: #007bff; color: white; padding: 12px; width: 100%; 
                border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; 
                font-weight: bold; transition: background 0.2s, transform 0.1s; 
                margin-top: 10px;
            }
            .btn-submit:hover { background-color: #0056b3; }
            .btn-submit:active { transform: scale(0.98); }
            
            .switch-text { margin-top: 25px; font-size: 0.9rem; color: #7f8c8d; }
            .btn-link { color: #27ae60; text-decoration: none; font-weight: bold; transition: color 0.2s; }
            .btn-link:hover { color: #219653; text-decoration: underline; }

            /* --- ESTILOS COMPATIBLES PARA UBICAR EL OJITO --- */
            .password-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }
            .password-wrapper input {
                padding-right: 45px;
            }
            .btn-toggle-password {
                position: absolute;
                right: 12px;
                background: none;
                border: none;
                cursor: pointer;
                font-size: 1.2rem;
                padding: 0;
                line-height: 1;
                user-select: none;
            }
            .btn-toggle-password:focus {
                outline: none;
            }
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="brand-header">
                <div class="brand-logo-mock"></div>
                <div class="brand-name">AuraTerra</div>
                <div class="brand-slogan">"Los 21gr del timo que acompañan el exito de la organizacion de tu actividad al aire libre"</div>
            </div>
            <h2>Iniciar Sesión</h2>
            <form id="formLogin">
                <div class="form-group"><label>Correo Electrónico</label><input type="email" name="email" placeholder="ejemplo@correo.com" required></div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Tu contraseña" required>
                        <button type="button" id="togglePassword" class="btn-toggle-password">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Ingresar</button>
            </form>
            <p class="switch-text">¿No tenés cuenta todavía? <a href="/auraTerraMayo/public/register" class="btn-link">Registrate</a></p>
        </div>

        <script>
        /* --- LÓGICA DE INTERRUPTOR PARA EL OJITO DE LA CLAVE --- */
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });

        document.getElementById('formLogin').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('/auraTerraMayo/public/login', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.ok) {
                Swal.fire({
                    title: '¡Conexión Exitosa!',
                    text: 'Iniciando panel agroclimático... ☀️',
                    icon: 'success',
                    iconColor: '#007bff',
                    showConfirmButton: false,
                    timer: 1500,
                    backdrop: `rgba(0, 123, 255, 0.08)`,
                    willClose: () => {
                        window.location.href = '/auraTerraMayo/public/dashboard.php';
                    }
                });
            } else {
                Swal.fire({
                    title: 'Acceso Denegado',
                    text: data.error || 'Credenciales incorrectas.',
                    icon: 'error',
                    confirmButtonColor: '#e74c3c'
                });
            }
        });
        </script>
    </body>
    </html>
HTML;
    exit;
}

function handleRegisterPost() {
    $email = trim($_POST['email'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email inválido';
    if (strlen($nombre) < 3 || strlen($nombre) > 50) $errors['nombre'] = 'Nombre entre 3 y 50 caracteres';
    if (strlen($password) < 6) $errors['password'] = 'Contraseña mínima 6 caracteres';
    
    if (!empty($errors)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $guardado = UsuarioHelper::guardarUsuario($email, $nombre, $hash);
    
    if (!$guardado) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'errors' => ['email' => 'El email ya está registrado']]);
        exit;
    }
    
    http_response_code(201);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'message' => 'Usuario registrado de forma correcta.']);
    exit;
}

function handleLoginPost() {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $usuario = UsuarioHelper::autenticar($email, $password);
    
    if (!$usuario) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Credenciales incorrectas']);
        exit;
    }
    
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_nombre'] = $usuario['nombre'];
    
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'message' => 'Login exitoso', 'user' => ['nombre' => $usuario['nombre'], 'email' => $usuario['email']]]);
    exit;
}

function handleLogout() {
    session_destroy();
    header('Location: /auraTerraMayo/public/login');
    exit;
}

function handlePerfil() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'No autenticado']);
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'user' => ['nombre' => $_SESSION['user_nombre'], 'email' => $_SESSION['user_email']]]);
    exit;
} 