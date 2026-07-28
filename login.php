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
                <a href="register.php" class="btn btn--primary btn--sm">Registrieren</a>
            </nav>
            <button class="navbar__toggle" data-nav-toggle aria-label="Menü öffnen" aria-expanded="false">
                <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
                <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </header>

    <main class="form-page form-page--photo">
        <div class="container">
            <div class="form-card fade-in">
                <div class="form-card__header">
                    <span class="form-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
                    </span>
                    <h2>Anmelden</h2> <!-- "Iniciar Sesión" -->
                    <p>Schön, dich wiederzusehen.</p>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert--success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        <p><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): /* Para mensajes de error de otras páginas, ej. "Debes iniciar sesión" */ ?>
                    <div class="alert alert--error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                        <p><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
                    </div>
                <?php endif; ?>

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

                <form action="login.php<?php echo isset($_GET['redirect_to']) ? '?redirect_to=' . urlencode($_GET['redirect_to']) : ''; ?>" method="post">
                    <div class="form-group">
                        <label for="username" class="form-label">Benutzername:</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username_input); ?>" autocomplete="username" required> <!-- Añadido autocomplete -->
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Passwort:</label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required> <!-- Añadido autocomplete -->
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary btn--block btn--lg">Anmelden</button> <!-- "Iniciar Sesión" -->
                    <div class="form-footer">
                        <p>Noch kein Konto? <a href="register.php">Hier registrieren</a></p> <!-- "¿No tienes una cuenta? Regístrate aquí" -->
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="script.js" defer></script>
</body>
</html>
