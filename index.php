<?php
session_start(); // Um zu wissen, ob ein Benutzer für die Navigationsleiste angemeldet ist
require_once 'db_connect.php';

$posts = [];
try {
    // Alle Posts abrufen und den Benutzernamen des Autors anzeigen
    $sql = "SELECT p.id, p.title, LEFT(p.content, 200) AS excerpt, p.created_at, u.username AS author_username
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->query($sql); // query() verwenden, da keine Benutzerparameter vorhanden sind
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fehler beim Abrufen aller Posts: " . $e->getMessage());
    // Hier könnte eine Fehlermeldung angezeigt werden
}
?>
<!DOCTYPE html>
<html lang="de"> <!-- Geändert zu lang="de" -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einfacher Blog</title> <!-- "Blog Sencillo" -->
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="navbar__link">Meine Posts</a>
                    <a href="create_post.php" class="btn btn--primary btn--sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Neuer Artikel
                    </a>
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
                <?php else: ?>
                    <a href="login.php" class="navbar__link">Anmelden</a>
                    <a href="register.php" class="btn btn--primary btn--sm">Registrieren</a>
                <?php endif; ?>
            </nav>

            <button class="navbar__toggle" data-nav-toggle aria-label="Menü öffnen" aria-expanded="false">
                <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
                <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <span class="hero__eyebrow fade-in">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><path d="m12 3-1.9 4.6L5 9l4.1 3.4L7.8 17 12 14.4 16.2 17l-1.3-4.6L19 9l-5.1-1.4z"/></svg>
                    Ideen &amp; Geschichten
                </span>
                <h1 class="hero__title slide-up">Willkommen bei Mein Blog</h1>
                <p class="hero__subtitle slide-up slide-up--1">Ein ruhiger Ort für ehrliche Artikel, Notizen und neue Perspektiven — geschrieben von unserer Community.</p>
                <div class="hero__actions slide-up slide-up--2">
                    <a href="#posts" class="btn btn--primary btn--lg">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        Artikel entdecken
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="create_post.php" class="btn btn--secondary btn--lg">Neuen Artikel schreiben</a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn--secondary btn--lg">Jetzt registrieren</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section section--tight" id="posts">
            <div class="container">
                <?php if (count($posts) > 0): ?>
                    <div class="post-grid">
                        <?php foreach ($posts as $post): ?>
                            <?php
                                $monogram = strtoupper(mb_substr($post['title'], 0, 1));
                                $wordCount = str_word_count(strip_tags($post['excerpt']));
                                $readMinutes = max(1, (int) ceil($wordCount / 200));
                                $authorInitial = strtoupper(mb_substr($post['author_username'], 0, 1));
                                // Themenbilder im Wechsel (rein visuell, keine neue Datenbankabfrage)
                                $topic_images = [
                                    'images/topic-ia.png',
                                    'images/topic-programacion.png',
                                    'images/topic-ciberseguridad.webp',
                                    'images/topic-cloud.png',
                                    'images/topic-bases-datos.png',
                                    'images/topic-devops.png',
                                    'images/topic-web-dev.png',
                                ];
                                $cover_image = $topic_images[$post['id'] % count($topic_images)];
                            ?>
                            <article class="post-card slide-up">
                                <a href="view_post.php?id=<?php echo $post['id']; ?>" class="post-card__cover" aria-hidden="true" tabindex="-1">
                                    <span class="post-card__monogram"><?php echo htmlspecialchars($monogram); ?></span>
                                    <img src="<?php echo htmlspecialchars($cover_image); ?>" alt="" class="cover-photo" onerror="this.style.display='none'">
                                </a>
                                <div class="post-card__body">
                                    <h2 class="post-card__title">
                                        <a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                                    </h2>
                                    <p class="post-card__excerpt"><?php echo nl2br(htmlspecialchars($post['excerpt'])); ?>...</p>
                                    <div class="post-card__meta">
                                        <span class="avatar avatar--sm" aria-hidden="true">
                                            <?php echo htmlspecialchars($authorInitial); ?>
                                            <img src="images/avatars/<?php echo rawurlencode(strtolower($post['author_username'])); ?>.jpg" alt="" class="avatar__photo" onerror="this.style.display='none'">
                                        </span>
                                        <span class="author-line"><strong><?php echo htmlspecialchars($post['author_username']); ?></strong></span>
                                    </div>
                                    <div class="post-card__footer">
                                        <span class="meta-chip">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                                            <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                                        </span>
                                        <span class="meta-chip">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                            <?php echo $readMinutes; ?> Min.
                                        </span>
                                    </div>
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="post-card__read-more">
                                        Weiterlesen
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <img src="images/empty-state.png" alt="" class="empty-state__image" onerror="this.style.display='none'">
                        <p class="no-posts">Noch keine Posts zum Anzeigen vorhanden.</p> <!-- "No hay posts para mostrar todavía." -->
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

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
