<?php

use Helpers\UsuarioHelper;

function handleRegisterGet() {
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Registro AuraTerra</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f6f9; color: #333; }
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
            .form-group input { padding: 8px; width: 250px; border-radius: 4px; border: 1px solid #ccc; }
            .btn-submit { background-color: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
            .btn-link { display: inline-block; margin-top: 10px; padding: 6px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h2>Registro de Usuario</h2>
        <form id="formRegistro">
            <div class="form-group"><label>Email:</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Nombre:</label><input type="text" name="nombre" required></div>
            <div class="form-group"><label>Contraseña:</label><input type="password" name="password" required></div>
            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
        
        <p>¿Ya tenés cuenta? <br>
            <a href="/auraTerraMayo/public/login" class="btn-link">Iniciar sesión</a>
        </p>

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
                alert(data.message);
                window.location.href = '/auraTerraMayo/public/login';
            } else {
                alert('Error: ' + JSON.stringify(data.errors));
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
    echo json_encode(['ok' => true, 'message' => 'Usuario registrado. Ahora podés iniciar sesión.']);
    exit;
}

function handleLoginGet() {
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login AuraTerra</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f6f9; color: #333; }
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
            .form-group input { padding: 8px; width: 250px; border-radius: 4px; border: 1px solid #ccc; }
            .btn-submit { background-color: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
            .btn-link { display: inline-block; margin-top: 10px; padding: 6px 12px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h2>Iniciar sesión</h2>
        <form id="formLogin">
            <div class="form-group"><label>Email:</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Contraseña:</label><input type="password" name="password" required></div>
            <button type="submit" class="btn-submit">Ingresar</button>
        </form>
        
        <div style="margin-top: 20px;">
            <p>¿No tenés cuenta todavía?</p>
            <a href="/auraTerraMayo/public/register" class="btn-link">Registrate</a>
        </div>

        <script>
        document.getElementById('formLogin').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('/auraTerraMayo/public/login', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.ok) {
                // CORRECCIÓN: Al ingresar con éxito, saltamos directo al panel visual del Dashboard
                window.location.href = '/auraTerraMayo/public/dashboard.php'; 
            } else {
                alert('Error: ' + data.error);
            }
        });
        </script>
    </body>
    </html>
    HTML;
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