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

?>
<!DOCTYPE html>
<html lang="de"> <!-- Geändert -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Mein Blog</title> <!-- Geändert -->
    <style>
        /* ... (Stile bleiben weitgehend gleich, ggf. Schriftgrößen anpassen) ... */
        body { font-family: sans-serif; margin: 0; padding:0; background-color: #f9f9f9; }
        .navbar { background-color: #333; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar .nav-brand a, .navbar .nav-auth a { color: white; text-decoration: none; margin-left: 15px; }
        .navbar .nav-brand a:hover, .navbar .nav-auth a:hover { text-decoration: underline; }
        .container { padding: 20px; max-width: 800px; margin: 20px auto; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .post-title { font-size: 2.5em; margin-bottom: 10px; color: #333; }
        .post-meta { color: #777; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .post-content { line-height: 1.7; color: #444; font-size: 1.1em; }
        .actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .actions a { margin-right: 15px; text-decoration: none; padding: 8px 12px; border-radius: 4px; }
        .edit-link { background-color: #ffc107; color: black; }
        .delete-link-post { background-color: #dc3545; color: white; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }

        .comments-section { margin-top: 40px; padding-top: 20px; border-top: 2px solid #ccc; }
        .comments-section h3 { margin-bottom: 20px; }
        .comment-form textarea { width: calc(100% - 22px); min-height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; resize: vertical; }
        .comment-form button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .comment-form button:hover { background-color: #218838; }
        .comment-list { list-style: none; padding: 0; }
        .comment-item { border: 1px solid #eee; padding: 15px; margin-bottom: 15px; border-radius: 5px; background-color: #fdfdfd; }
        .comment-meta { font-size: 0.9em; color: #666; margin-bottom: 5px; }
        .comment-meta strong { color: #333; }
        .comment-content { margin-bottom: 10px; }
        .comment-actions a { font-size: 0.85em; margin-right: 8px; text-decoration: none; }
        .edit-comment-link { color: #ffc107; }
        .delete-comment-link { color: #dc3545; }
        .no-comments { color: #777; }
        .errors { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .errors ul { margin: 0; padding-left: 20px; }
        .success-message { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; text-align: center;}
    </style>
</head>
<body>
    <div class="navbar">
         <div class="nav-brand">
            <a href="index.php"><strong>Mein Blog</strong></a> <!-- Geändert -->
        </div>
        <div class="nav-auth">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Meine Posts</a> <!-- Geändert -->
                <a href="logout.php">Abmelden (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a> <!-- Geändert -->
            <?php else: ?>
                <a href="login.php">Anmelden</a> <!-- Geändert -->
                <a href="register.php">Registrieren</a> <!-- Geändert -->
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="post-meta">
            Von: <?php echo htmlspecialchars($post['author_username']); ?> | <!-- Geändert -->
            Veröffentlicht am: <?php echo date('d. F Y \u\m H:i', strtotime($post['created_at'])); ?> Uhr <!-- Geändert, deutsches Datumsformat -->
            <?php if ($post['created_at'] != $post['updated_at']): ?>
                | Letzte Aktualisierung: <?php echo date('d. F Y \u\m H:i', strtotime($post['updated_at'])); ?> Uhr <!-- Geändert -->
            <?php endif; ?>
        </p>
        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
            <div class="actions">
                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit-link">Post bearbeiten</a> <!-- Geändert -->
                <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bist du sicher, dass du diesen Post löschen möchtest?');" class="delete-link-post">Post löschen</a> <!-- Geändert -->
            </div>
        <?php endif; ?>
        <a href="index.php" class="back-link">← Zurück zu allen Posts</a> <!-- Geändert -->

        <!-- Kommentarbereich -->
        <div class="comments-section">
            <h3>Kommentare (<?php echo count($comments); ?>)</h3> <!-- Geändert -->

            <?php if (isset($_SESSION['success_message'])): ?>
                <p class="success-message"><?php echo htmlspecialchars(translate_session_message_view($_SESSION['success_message'], $translations_view)); unset($_SESSION['success_message']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="errors"><ul><li><?php echo htmlspecialchars(translate_session_message_view($_SESSION['error_message'], $translations_view)); unset($_SESSION['error_message']); ?></li></ul></div>
            <?php endif; ?>

            <!-- Formular zum Hinzufügen eines Kommentars -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <h4>Einen Kommentar hinzufügen:</h4> <!-- Geändert -->
                <?php if (!empty($comment_errors)): ?>
                    <div class="errors">
                        <ul>
                            <?php foreach ($comment_errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li> <!-- Diese Fehler sind schon auf Deutsch -->
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="view_post.php?id=<?php echo $post_id; ?>" method="post" class="comment-form">
                    <input type="hidden" name="add_comment" value="1">
                    <div>
                        <textarea name="comment_content" placeholder="Schreibe deinen Kommentar hier..." required><?php echo htmlspecialchars($comment_content); ?></textarea> <!-- Geändert -->
                    </div>
                    <button type="submit">Kommentar absenden</button> <!-- Geändert -->
                </form>
            <?php else: ?>
                <p><a href="login.php?redirect_to=<?php echo urlencode("view_post.php?id=".$post_id); ?>">Anmelden</a>, um einen Kommentar zu hinterlassen.</p> <!-- Geändert -->
            <?php endif; ?>

            <!-- Kommentarliste -->
            <ul class="comment-list">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <li class="comment-item" id="comment-<?php echo $comment['id']; ?>"> <!-- id hinzugefügt für Anker -->
                            <p class="comment-meta">
                                <strong><?php echo htmlspecialchars($comment['commenter_username']); ?></strong>
                                am <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?> Uhr <!-- Geändert -->
                            </p>
                            <p class="comment-content"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                <div class="comment-actions">
                                    <a href="edit_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $post_id; ?>" class="edit-comment-link">Bearbeiten</a> <!-- Geändert -->
                                    <a href="delete_comment.php?id=<?php echo $comment['id']; ?>&post_id=<?php echo $post_id; ?>" onclick="return confirm('Bist du sicher, dass du diesen Kommentar löschen möchtest?');" class="delete-comment-link">Löschen</a> <!-- Geändert -->
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">Noch keine Kommentare. Sei der Erste!</p> <!-- Geändert -->
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>