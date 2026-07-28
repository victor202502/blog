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
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='22' fill='%234F46E5'/%3E%3Ctext x='50' y='68' font-size='56' font-family='sans-serif' font-weight='700' fill='white' text-anchor='middle'%3EM%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="navbar">
        <div class="container navbar__inner">
            <a href="index.php" class="navbar__brand">
                <span class="navbar__mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </span>
                <span>Mein Blog</span>
            </a>
            <nav class="navbar__nav" data-nav-menu>
                <a href="index.php" class="navbar__link">Start</a>
                <a href="login.php" class="navbar__link">Anmelden</a>
            </nav>
            <button class="navbar__toggle" data-nav-toggle aria-label="Menü öffnen" aria-expanded="false">
                <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
                <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </header>

    <main class="form-page form-page--split">
        <div class="form-page__illustration">
            <img src="images/register-illustration.png" alt="" onerror="this.style.display='none'">
        </div>
        <div class="form-page__panel">
            <div class="form-card fade-in">
                <div class="form-card__header">
                    <span class="form-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>
                    </span>
                    <h2>Vereinfachte Registrierung</h2> <!-- "Registro Simplificado" -->
                    <p>Leg los und teile deine ersten Gedanken.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert--error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="post">
                    <div class="form-group">
                        <label for="username" class="form-label">Benutzername:</label> <!-- "Nombre de Usuario:" -->
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Passwort:</label> <!-- "Contraseña:" -->
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required> <!-- Añadido autocomplete -->
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary btn--block btn--lg">Registrieren</button> <!-- "Registrarse" -->
                    <div class="form-footer">
                        <p>Hast du bereits ein Konto? <a href="login.php">Hier einloggen</a></p> <!-- "¿Ya tienes una cuenta? Inicia sesión aquí" -->
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="script.js" defer></script>
</body>
</html>
