<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$username = '';
// $email = ''; // Nicht mehr für die Eingabe benötigt

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Grundlegende Validierungen
    if (empty($username)) {
        $errors[] = "Der Benutzername ist erforderlich."; // "El nombre de usuario es obligatorio."
    }
    if (empty($password)) {
        $errors[] = "Das Passwort ist erforderlich."; // "La contraseña es obligatoria."
    }

    // Wenn keine anfänglichen Validierungsfehler vorliegen, prüfen, ob der Benutzer bereits existiert
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $errors[] = "Der Benutzername ist bereits vergeben."; // "El nombre de usuario ya está en uso."
        }
    }

    // Wenn bisher keine Fehler aufgetreten sind, mit der Registrierung fortfahren
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $params = [
                'username' => $username,
                'password_hash' => $password_hash
            ];
            $stmt->execute($params);

            $_SESSION['success_message'] = "Registrierung erfolgreich! Du kannst dich jetzt einloggen."; // "¡Registro exitoso! Ahora puedes iniciar sesión."
            header("Location: login.php");
            exit;

        } catch (PDOException $e) {
            error_log("Fehler bei der Registrierung: " . $e->getMessage());
            $errors[] = "Bei der Registrierung ist ein Fehler aufgetreten. Bitte versuche es erneut."; // "Ocurrió un error durante el registro. Por favor, inténtalo de nuevo."
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de"> <!-- Cambiado a lang="de" -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerregistrierung</title> <!-- "Registro de Usuario" -->
    <style> /* Dieselben Stile wie zuvor */
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
        <h2>Vereinfachte Registrierung</h2> <!-- "Registro Simplificado" -->

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
                <label for="username">Benutzername:</label> <!-- "Nombre de Usuario:" -->
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort:</label> <!-- "Contraseña:" -->
                <input type="password" id="password" name="password" autocomplete="new-password" required> <!-- Añadido autocomplete -->
            </div>
            <button type="submit">Registrieren</button> <!-- "Registrarse" -->
            <div class="form-footer">
                <p>Hast du bereits ein Konto? <a href="login.php">Hier einloggen</a></p> <!-- "¿Ya tienes una cuenta? Inicia sesión aquí" -->
            </div>
        </form>
    </div>
</body>
</html>