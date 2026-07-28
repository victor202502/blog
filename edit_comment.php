<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['post_id'])) {
    // Wenn post_id fehlt, versuchen, zum Dashboard oder Index umzuleiten
    $_SESSION['error_message'] = "Unvollständige Informationen zum Bearbeiten des Kommentars."; // "Información incompleta para editar comentario."
    $fallback_redirect = isset($_SESSION['user_id']) ? "dashboard.php" : "index.php";
    header("Location: " . $fallback_redirect);
    exit;
}

$comment_id = (int)$_GET['id'];
$post_id_redirect = (int)$_GET['post_id'];
$user_id_session = $_SESSION['user_id'];
$comment = null;
$content = '';
$errors = [];

// 1. Kommentar zum Bearbeiten abrufen
try {
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
    $stmt->execute(['id' => $comment_id]);
    $comment = $stmt->fetch();

    if (!$comment) {
        $_SESSION['error_message'] = "Kommentar nicht gefunden."; // "Comentario no encontrado."
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }

    if ($comment['user_id'] != $user_id_session) {
        $_SESSION['error_message'] = "Du hast keine Berechtigung, diesen Kommentar zu bearbeiten."; // "No tienes permiso para editar este comentario."
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }
    $content = $comment['content'];

} catch (PDOException $e) {
    error_log("Fehler beim Abrufen des Kommentars zum Bearbeiten: " . $e->getMessage());
    $_SESSION['error_message'] = "Fehler beim Laden des Kommentars."; // "Error al cargar comentario."
    header("Location: view_post.php?id=" . $post_id_redirect);
    exit;
}

// 2. Aktualisierung verarbeiten
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updated_content = trim($_POST['content']);
    if (empty($updated_content)) {
        $errors[] = "Der Kommentarinhalt darf nicht leer sein."; // "El contenido del comentario no puede estar vacío."
    } else {
        $content = $updated_content; 
    }

    if (empty($errors)) {
        try {
            $stmt_update = $pdo->prepare("UPDATE comments SET content = :content WHERE id = :id AND user_id = :user_id");
            $stmt_update->execute([
                'content' => $updated_content,
                'id' => $comment_id,
                'user_id' => $user_id_session
            ]);
            $_SESSION['success_message'] = "Kommentar aktualisiert."; // "Comentario actualizado."
            header("Location: view_post.php?id=" . $post_id_redirect . "#comment-" . $comment_id);
            exit;
        } catch (PDOException $e) {
            error_log("Fehler beim Aktualisieren des Kommentars: " . $e->getMessage());
            $errors[] = "Fehler beim Speichern der Änderungen."; // "Error al guardar los cambios."
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de"> <!-- Geändert -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kommentar bearbeiten</title> <!-- "Editar Comentario" -->
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
            <div class="form-card fade-in">
                <div class="form-card__header">
                    <span class="form-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </span>
                    <h2>Kommentar bearbeiten</h2> <!-- "Editar Comentario" -->
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert--error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                        <ul><?php foreach ($errors as $error) echo "<li>".htmlspecialchars($error)."</li>"; ?></ul>
                    </div>
                <?php endif; ?>

                <form action="edit_comment.php?id=<?php echo $comment_id; ?>&post_id=<?php echo $post_id_redirect; ?>" method="post">
                    <div class="form-group">
                        <label for="content" class="form-label">Dein Kommentar:</label> <!-- "Tu comentario:" -->
                        <textarea id="content" name="content" class="form-control" required><?php echo htmlspecialchars($content); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary btn--block btn--lg">Kommentar aktualisieren</button> <!-- "Actualizar Comentario" -->
                </form>
                <div class="form-footer">
                    <a href="view_post.php?id=<?php echo $post_id_redirect; ?>#comment-<?php echo $comment_id; ?>">Abbrechen</a> <!-- "Cancelar" -->
                </div>
            </div>
        </div>
    </main>

    <script src="script.js" defer></script>
</body>
</html>
