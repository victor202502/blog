<?php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$username_input = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username_input)) {
        $errors[] = "Der Benutzername ist erforderlich."; // "El nombre de usuario es obligatorio."
    }
    if (empty($password)) {
        $errors[] = "Das Passwort ist erforderlich."; // "La contraseña es obligatoria."
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
            $stmt->execute(['username' => $username_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Si venías de una redirección (ej. intentar comentar sin estar logueado)
                if (isset($_SESSION['redirect_to'])) {
                    $redirect_url = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header("Location: " . $redirect_url);
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $errors[] = "Benutzername oder Passwort falsch."; // "Nombre de usuario o contraseña incorrectos."
            }
        } catch (PDOException $e) {
            error_log("Fehler beim Login: " . $e->getMessage());
            $errors[] = "Ein Fehler ist aufgetreten. Bitte versuche es erneut."; // "Ocurrió un error. Por favor, inténtalo de nuevo."
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de"> <!-- Cambiado a lang="de" -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden</title> <!-- "Iniciar Sesión" -->
    <style> /* Dieselben Stile */
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f4f4; margin: 0; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="password"] { width: calc(100% - 20px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #218838; }
        .errors { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .errors ul { margin: 0; padding-left: 20px; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
        .form-footer { text-align: center; margin-top: 15px; }
        .form-footer a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Anmelden</h2> <!-- "Iniciar Sesión" -->

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): /* Para mensajes de error de otras páginas, ej. "Debes iniciar sesión" */ ?>
            <div class="errors"><ul><li><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></li></ul></div>
        <?php endif; ?>


        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php<?php echo isset($_GET['redirect_to']) ? '?redirect_to=' . urlencode($_GET['redirect_to']) : ''; ?>" method="post">
            <div class="form-group">
                <label for="username">Benutzername:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username_input); ?>" autocomplete="username" required> <!-- Añadido autocomplete -->
            </div>
            <div class="form-group">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required> <!-- Añadido autocomplete -->
            </div>
            <button type="submit">Anmelden</button> <!-- "Iniciar Sesión" -->
            <div class="form-footer">
                <p>Noch kein Konto? <a href="register.php">Hier registrieren</a></p> <!-- "¿No tienes una cuenta? Regístrate aquí" -->
            </div>
        </form>
    </div>
</body>
</html>