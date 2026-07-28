<?php
session_start();
require_once 'db_connect.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$title = '';
$content = '';
// Para manejo de imágenes, lo añadiremos después para simplificar ahora

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id']; // El ID del usuario logueado

    if (empty($title)) {
        $errors[] = "El título es obligatorio.";
    }
    if (empty($content)) {
        $errors[] = "El contenido es obligatorio.";
    }

    if (empty($errors)) {
        try {
            // Si no tienes el trigger para updated_at, tendrías que incluirlo aquí:
            // $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, updated_at) VALUES (:user_id, :title, :content, NOW())");
            // Si tienes el trigger, created_at y updated_at se manejan solos o created_at por defecto y updated_at por trigger
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (:user_id, :title, :content)");
            $stmt->execute([
                'user_id' => $user_id,
                'title' => $title,
                'content' => $content
            ]);

            $_SESSION['success_message'] = "¡Post creado exitosamente!";
            header("Location: dashboard.php"); // O a una página que liste los posts
            exit;

        } catch (PDOException $e) {
            error_log("Error al crear post: " . $e->getMessage());
            $errors[] = "Ocurrió un error al crear el post. Inténtalo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Post</title>
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
                <a href="dashboard.php" class="navbar__link">Meine Posts</a>
                <div class="navbar__user">
                    <span class="avatar avatar--sm" aria-hidden="true">
                        <?php echo htmlspecialchars(strtoupper(mb_substr($_SESSION['username'], 0, 1))); ?>
                        <img src="images/avatars/<?php echo rawurlencode(strtolower($_SESSION['username'])); ?>.jpg" alt="" class="avatar__photo" onerror="this.style.display='none'">
                    </span>
                    <span class="navbar__username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="navbar__icon-link" aria-label="Abmelden" title="Abmelden">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                    </a>
                </div>
            </nav>
            <button class="navbar__toggle" data-nav-toggle aria-label="Menü öffnen" aria-expanded="false">
                <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
                <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </header>

    <main class="form-page">
        <div class="container">
            <div class="form-card form-card--wide fade-in">
                <div class="form-card__header">
                    <span class="form-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    </span>
                    <h2>Crear Nuevo Post</h2>
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

                <form action="create_post.php" method="post">
                    <div class="form-group">
                        <label for="title" class="form-label">Título:</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content" class="form-label">Contenido:</label>
                        <textarea id="content" name="content" class="form-control" required><?php echo htmlspecialchars($content); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary btn--block btn--lg">Crear Post</button>
                </form>
                <div class="form-footer">
                    <a href="dashboard.php">← Volver al Dashboard</a>
                </div>
            </div>
        </div>
    </main>

    <script src="script.js" defer></script>
</body>
</html>
