<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username_display = $_SESSION['username'];

// Posts des eingeloggten Benutzers abrufen
$posts = [];
try {
    // Der SQL-Befehl bleibt derselbe
    $stmt = $pdo->prepare("SELECT id, title, LEFT(content, 150) AS excerpt, created_at FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fehler beim Abrufen der Posts: " . $e->getMessage());
    // Hier könnte eine Fehlermeldung angezeigt werden
}
?>
<!DOCTYPE html>
<html lang="de"> <!-- Geändert zu lang="de" -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Meine Posts</title> <!-- "Dashboard - Mis Posts" -->
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
                <span>Hauptblog</span> <!-- "Blog Principal" -->
            </a>

            <nav class="navbar__nav" data-nav-menu>
                <a href="index.php" class="navbar__link">Hauptblog</a>
                <a href="dashboard.php" class="navbar__link">Meine Posts</a>
                <a href="create_post.php" class="btn btn--primary btn--sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Neuer Artikel
                </a>
                <div class="navbar__user">
                    <span class="avatar avatar--sm" aria-hidden="true">
                        <?php echo htmlspecialchars(strtoupper(mb_substr($username_display, 0, 1))); ?>
                        <img src="images/avatars/<?php echo rawurlencode(strtolower($username_display)); ?>.jpg" alt="" class="avatar__photo" onerror="this.style.display='none'">
                    </span>
                    <span class="navbar__username">Willkommen, <?php echo htmlspecialchars($username_display); ?>!</span> <!-- "Bienvenido, ..." -->
                    <a href="logout.php" class="navbar__icon-link" aria-label="Abmelden" title="Abmelden"> <!-- "Cerrar Sesión" -->
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

    <main class="section section--tight">
        <div class="container">

            <div class="dashboard-banner fade-in">
                <img src="images/dashboard-banner.jpg" alt="" onerror="this.style.display='none'">
            </div>

            <div class="dash-header fade-in">
                <div>
                    <h1>Meine Posts</h1> <!-- "Mis Posts" -->
                    <p>Verwalte deine veröffentlichten Artikel an einem Ort.</p>
                </div>
                <a href="create_post.php" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Neuen Post erstellen</a> <!-- "Crear Nuevo Post" -->
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert--success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    <p>
                        <?php
                        // Übersetzungen für Erfolgsmeldungen
                        $translations_success = [
                            "¡Post creado exitosamente!" => "Post erfolgreich erstellt!",
                            "¡Post actualizado exitosamente!" => "Post erfolgreich aktualisiert!",
                            "Post eliminado exitosamente." => "Post erfolgreich gelöscht.",
                            "Comentario añadido." => "Kommentar hinzugefügt.",
                            "Comentario actualizado." => "Kommentar aktualisiert.",
                            "Comentario eliminado." => "Kommentar gelöscht."
                            // Füge hier weitere Übersetzungen hinzu, falls nötig
                        ];
                        $message_key = $_SESSION['success_message'];
                        echo htmlspecialchars(isset($translations_success[$message_key]) ? $translations_success[$message_key] : $message_key);
                        unset($_SESSION['success_message']);
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): // Für Fehlermeldungen von anderen Seiten ?>
                <div class="alert alert--error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <p><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card slide-up">
                    <span class="stat-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                    </span>
                    <div>
                        <div class="stat-card__value"><?php echo count($posts); ?></div>
                        <div class="stat-card__label">Beiträge gesamt</div>
                    </div>
                </div>

                <div class="stat-card slide-up slide-up--1">
                    <span class="stat-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                    </span>
                    <div>
                        <?php if (count($posts) > 0): ?>
                            <div class="stat-card__value"><?php echo date('d.m.Y', strtotime($posts[0]['created_at'])); ?></div>
                            <div class="stat-card__label">Letzter Beitrag</div>
                        <?php else: ?>
                            <div class="stat-card__value">–</div>
                            <div class="stat-card__label">Noch kein Beitrag</div>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="create_post.php" class="stat-card stat-card--cta slide-up slide-up--2">
                    <span class="stat-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    </span>
                    <div class="stat-card__title">Neuen Artikel schreiben</div>
                    <div class="stat-card__desc">Teile deine nächste Idee mit der Community.</div>
                </a>
            </div>

            <div class="table-card fade-in">
                <?php if (count($posts) > 0): ?>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Titel</th>
                                    <th>Veröffentlicht am</th>
                                    <th style="text-align:right;">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td>
                                            <div class="data-table__title"><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></div>
                                            <div class="data-table__excerpt"><?php echo htmlspecialchars($post['excerpt']); ?>...</div>
                                        </td>
                                        <td class="data-table__date"><?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></td> <!-- "Publicado el:" y formato de fecha alemán -->
                                        <td>
                                            <div class="row-actions">
                                                <a href="view_post.php?id=<?php echo $post['id']; ?>" aria-label="Ansehen" title="Ansehen"> <!-- "Ver" -->
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </a>
                                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" aria-label="Bearbeiten" title="Bearbeiten"> <!-- "Editar" -->
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                                </a>
                                                <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bist du sicher, dass du diesen Post löschen möchtest?');" class="is-danger" aria-label="Löschen" title="Löschen"> <!-- "¿Estás seguro de que quieres eliminar este post?" y "Eliminar" -->
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <img src="images/empty-state.png" alt="" class="empty-state__image" onerror="this.style.display='none'">
                        <p class="no-posts">
                            Du hast noch keine Posts erstellt.
                            <a href="create_post.php">Erstelle jetzt einen</a> oder <a href="index.php">erkunde den Hauptblog</a>!
                            <!-- "Aún no has creado ningún post. ¡Crea uno ahora o explora el blog principal!" -->
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <a href="create_post.php" class="fab" aria-label="Neuen Post erstellen" title="Neuen Post erstellen">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
    </a>

    <footer class="footer">
        <div class="container">
            <span class="footer__brand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Mein Blog
            </span>
            <span>&copy; <?php echo date('Y'); ?> Mein Blog. Alle Rechte vorbehalten.</span>
        </div>
    </footer>

    <script src="script.js" defer></script>
</body>
</html>
