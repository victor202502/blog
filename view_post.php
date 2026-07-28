<?php
session_start();
require_once 'db_connect.php';

// Array für Übersetzungen von Session-Nachrichten
$translations_view = [
    "Post no encontrado." => "Post nicht gefunden.",
    "Debes iniciar sesión para comentar." => "Du musst dich anmelden, um zu kommentieren.",
    "Comentario añadido." => "Kommentar hinzugefügt.",
    "Error al guardar el comentario." => "Fehler beim Speichern des Kommentars."
    // Füge hier weitere session-basierte Nachrichten hinzu, die von anderen Seiten kommen könnten
];

function translate_session_message_view($message_key, $translations_array) {
    return isset($translations_array[$message_key]) ? $translations_array[$message_key] : $message_key;
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_GET['id'];
$post = null;
$comments = [];
$comment_errors = [];
$comment_content = '';

// --- Post abrufen ---
try {
    $stmt_post = $pdo->prepare("SELECT p.*, u.username AS author_username
                               FROM posts p
                               JOIN users u ON p.user_id = u.id
                               WHERE p.id = :id");
    $stmt_post->execute(['id' => $post_id]);
    $post = $stmt_post->fetch();

    if (!$post) {
        $_SESSION['error_message'] = "Post nicht gefunden."; // "Post no encontrado."
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Fehler beim Anzeigen des Posts: " . $e->getMessage());
    die("Fehler beim Laden des Posts."); // "Error al cargar el post."
}

// --- Neuen Kommentar verarbeiten ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_to'] = "view_post.php?id=" . $post_id;
        $_SESSION['error_message'] = "Du musst dich anmelden, um zu kommentieren."; // "Debes iniciar sesión para comentar."
        header("Location: login.php");
        exit;
    }

    $comment_content = trim($_POST['comment_content']);
    $user_id_commenter = $_SESSION['user_id'];

    if (empty($comment_content)) {
        $comment_errors[] = "Der Kommentarinhalt darf nicht leer sein."; // "El contenido del comentario no puede estar vacío."
    }

    if (empty($comment_errors)) {
        try {
            $stmt_add_comment = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
            $stmt_add_comment->execute([
                'post_id' => $post_id,
                'user_id' => $user_id_commenter,
                'content' => $comment_content
            ]);
            $_SESSION['success_message'] = "Kommentar hinzugefügt."; // "Comentario añadido."
            $comment_content = ''; 
            $comment_errors = [];
            // Kein Redirect hier, damit Fehler (falls doch welche auftreten) angezeigt werden können.
            // Die Seite wird effektiv neu geladen, da die Kommentare unten erneut abgerufen werden.
        } catch (PDOException $e) {
            error_log("Fehler beim Hinzufügen des Kommentars: " . $e->getMessage());
            $comment_errors[] = "Fehler beim Speichern des Kommentars."; // "Error al guardar el comentario."
        }
    }
}


// --- Kommentare des Posts abrufen ---
try {
    $stmt_comments = $pdo->prepare("SELECT c.*, u.username AS commenter_username
                                    FROM comments c
                                    JOIN users u ON c.user_id = u.id
                                    WHERE c.post_id = :post_id
                                    ORDER BY c.created_at ASC");
    $stmt_comments->execute(['post_id' => $post_id]);
    $comments = $stmt_comments->fetchAll();
} catch (PDOException $e) {
    error_log("Fehler beim Abrufen der Kommentare: " . $e->getMessage());
}

// Rein optische Hilfswerte (keine neuen Datenbankabfragen): Monogramm, Lesezeit, Initialen
$post_monogram = strtoupper(mb_substr($post['title'], 0, 1));
$post_word_count = str_word_count(strip_tags($post['content']));
$post_read_minutes = max(1, (int) ceil($post_word_count / 200));
$post_author_initial = strtoupper(mb_substr($post['author_username'], 0, 1));

// Themenbild im Wechsel — dieselbe Formel wie in index.php, damit derselbe Post
// überall dasselbe Bild zeigt (rein visuell, keine neue Datenbankabfrage)
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
<!DOCTYPE html>
<html lang="de"> <!-- Geändert -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Mein Blog</title> <!-- Geändert -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='22' fill='%234F46E5'/%3E%3Ctext x='50' y='68' font-size='56' font-family='sans-serif' font-weight='700' fill='white' text-anchor='middle'%3EM%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="reading-progress" data-reading-progress aria-hidden="true"></div>

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
        <div class="container container--narrow article-header">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="index.php">Start</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                <span class="breadcrumb__current"><?php echo htmlspecialchars($post['title']); ?></span>
            </nav>

            <article class="fade-in">
                <div class="article__cover">
                    <span class="post-card__monogram"><?php echo htmlspecialchars($post_monogram); ?></span>
                    <img src="<?php echo htmlspecialchars($cover_image); ?>" alt="" class="cover-photo" onerror="this.style.display='none'">
                </div>

                <span class="badge badge--primary" style="margin-bottom:18px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                    Artikel
                </span>

                <h1 class="article__title"><?php echo htmlspecialchars($post['title']); ?></h1>

                <div class="article__meta">
                    <div class="author-line">
                        <span class="avatar avatar--md" aria-hidden="true">
                            <?php echo htmlspecialchars($post_author_initial); ?>
                            <img src="images/avatars/<?php echo rawurlencode(strtolower($post['author_username'])); ?>.jpg" alt="" class="avatar__photo" onerror="this.style.display='none'">
                        </span>
                        <span><strong><?php echo htmlspecialchars($post['author_username']); ?></strong></span>
                    </div>
                    <span class="article__meta-divider" aria-hidden="true"></span>
                    <span class="meta-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                        <?php echo date('d. F Y \u\m H:i', strtotime($post['created_at'])); ?> Uhr <!-- Geändert, deutsches Datumsformat -->
                    </span>
                    <span class="article__meta-divider" aria-hidden="true"></span>
                    <span class="meta-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        <?php echo $post_read_minutes; ?> Min. Lesezeit
                    </span>
                    <?php if ($post['created_at'] != $post['updated_at']): ?>
                        <span class="article__meta-divider" aria-hidden="true"></span>
                        <span class="meta-chip">Aktualisiert: <?php echo date('d. F Y \u\m H:i', strtotime($post['updated_at'])); ?> Uhr</span> <!-- Geändert -->
                    <?php endif; ?>
                </div>

                <div class="article__body">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                    <div class="article__actions">
                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn--secondary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                            Post bearbeiten
                        </a> <!-- Geändert -->
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bist du sicher, dass du diesen Post löschen möchtest?');" class="btn btn--danger">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            Post löschen
                        </a> <!-- Geändert -->
                    </div>
                <?php endif; ?>
                <a href="index.php" class="back-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    Zurück zu allen Posts
                </a> <!-- Geändert -->

                <!-- Kommentarbereich -->
                <div class="comments">
                    <div class="comments__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        <h3>Kommentare (<?php echo count($comments); ?>)</h3> <!-- Geändert -->
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert--success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            <p><?php echo htmlspecialchars(translate_session_message_view($_SESSION['success_message'], $translations_view)); unset($_SESSION['success_message']); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert--error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                            <p><?php echo htmlspecialchars(translate_session_message_view($_SESSION['error_message'], $translations_view)); unset($_SESSION['error_message']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Formular zum Hinzufügen eines Kommentars -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="comment-form">
                            <h4 style="margin-bottom:14px;">Einen Kommentar hinzufügen:</h4> <!-- Geändert -->
                            <?php if (!empty($comment_errors)): ?>
                                <div class="alert alert--error">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                                    <ul>
                                        <?php foreach ($comment_errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li> <!-- Diese Fehler sind schon auf Deutsch -->
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <form action="view_post.php?id=<?php echo $post_id; ?>" method="post">
                                <input type="hidden" name="add_comment" value="1">
                                <div class="comment-form__row">
                                    <span class="avatar avatar--md" aria-hidden="true">
                                        <?php echo htmlspecialchars(strtoupper(mb_substr($_SESSION['username'], 0, 1))); ?>
                                        <img src="images/avatars/<?php echo rawurlencode(strtolower($_SESSION['username'])); ?>.jpg" alt="" class="avatar__photo" onerror="this.style.display='none'">
                                    </span>
                                    <div class="comment-form__fields">
                                        <textarea name="comment_content" class="form-control form-control--comment" placeholder="Schreibe deinen Kommentar hier..." required><?php echo htmlspecialchars($comment_content); ?></textarea> <!-- Geändert -->
                                        <div style="margin-top:12px; text-align:right;">
                                            <button type="submit" class="btn btn--primary">Kommentar absenden</button> <!-- Geändert -->
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="comment-prompt">
                            <span>Melde dich an, um einen Kommentar zu hinterlassen.</span>
                            <a href="login.php?redirect_to=<?php echo urlencode("view_post.php?id=".$post_id); ?>" class="btn btn--primary btn--sm">Anmelden</a> <!-- Geändert -->
                        </div>
                    <?php endif; ?>

                    <!-- Kommentarliste -->
                    <ul class="comment-list">
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <?php $commenter_initial = strtoupper(mb_substr($comment['commenter_username'], 0, 1)); ?>
                                <li class="comment-item" id="comment-<?php echo $comment['id']; ?>"> <!-- id hinzugefügt für Anker -->
                                    <div class="comment-item__head">
                                        <span class="avatar avatar--sm" aria-hidden="true">
                                            <?php echo htmlspecialchars($commenter_initial); ?>
                                            <img src="images/avatars/<?php echo rawurlencode(strtolower($comment['commenter_username'])); ?>.jpg" alt="" class="avatar__photo" onerror="this.style.display='none'">
                                        </span>
                                        <span class="author-line">
                                            <strong><?php echo htmlspecialchars($comment['commenter_username']); ?></strong>
                                            &nbsp;·&nbsp;<?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?> Uhr <!-- Geändert -->
                                        </span>
                                    </div>
                                    <p class="comment-item__content"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                        <div class="comment-item__actions">
                                            <a href="edit_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $post_id; ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                                Bearbeiten
                                            </a> <!-- Geändert -->
                                            <a href="delete_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $post_id; ?>" onclick="return confirm('Bist du sicher, dass du diesen Kommentar löschen möchtest?');" class="is-danger">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                Löschen
                                            </a> <!-- Geändert -->
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                <p class="no-comments">Noch keine Kommentare. Sei der Erste!</p> <!-- Geändert -->
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>
            </article>
        </div>
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
