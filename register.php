<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$username = '';
// $email = ''; // Ya no es necesario para el input

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    // $password_confirm = $_POST['password_confirm']; // Ya no es necesario
    // $email = trim($_POST['email']); // Ya no es necesario

    // Validaciones básicas
    if (empty($username)) {
        $errors[] = "El nombre de usuario es obligatorio.";
    }
    // Quitar validaciones de email
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    }
    // Quitar validación de confirmación de contraseña
    // if ($password !== $password_confirm) {
    //     $errors[] = "Las contraseñas no coinciden.";
    // }

    // Si no hay errores de validación inicial, verificar si el usuario ya existe
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $errors[] = "El nombre de usuario ya está en uso.";
        }

        // Quitar verificación de existencia de email
    }

    // Si no hay errores hasta ahora, proceder a registrar
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Ajustar la consulta INSERT si eliminaste la columna email de la tabla
            // Si la columna email existe pero es opcional (NULL), puedes omitirla del INSERT o pasar NULL
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $params = [
                'username' => $username,
                'password_hash' => $password_hash
            ];
            // Si mantuviste la columna email como opcional y quieres guardar un email (quizás vacío o nulo):
            // $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
            // $params = [
            //     'username' => $username,
            //     'email' => null, // o un valor por defecto si lo quieres
            //     'password_hash' => $password_hash
            // ];
            $stmt->execute($params);


            $_SESSION['success_message'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            header("Location: login.php");
            exit;

        } catch (PDOException $e) {
            error_log("Error en registro: " . $e->getMessage());
            $errors[] = "Ocurrió un error durante el registro. Por favor, inténtalo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <style> /* Mismos estilos que antes */
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f4; margin: 0; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="email"], input[type="password"] { width: calc(100% - 20px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #0056b3; }
        .errors { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .errors ul { margin: 0; padding-left: 20px; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
        .form-footer { text-align: center; margin-top: 15px; }
        .form-footer a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro Simplificado</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Nombre de Usuario:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <!-- Campo de Email eliminado -->
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <!-- Campo de Confirmar Contraseña eliminado -->
            <button type="submit">Registrarse</button>
            <div class="form-footer">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </form>
    </div>
</body>
</html>