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
    <style>
        /* Dieselben Stile wie im Dashboard oder neue erstellen */
        body { font-family: sans-serif; margin: 0; padding:0; background-color: #f9f9f9; }
        .navbar { background-color: #333; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar .nav-brand a, .navbar .nav-auth a { color: white; text-decoration: none; margin-left: 15px; } /* Angepasst für bessere Struktur */
        .navbar .nav-brand a:hover, .navbar .nav-auth a:hover { text-decoration: underline; }
        .container { padding: 20px; max-width: 900px; margin: 20px auto; }
        h1.page-title { text-align: center; margin-bottom: 30px; color: #333; }
        .post-item { background-color: #fff; border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .post-item h2 { margin-top: 0; }
        .post-item h2 a { text-decoration: none; color: #007bff; }
        .post-item h2 a:hover { text-decoration: underline; }
        .post-meta { font-size: 0.9em; color: #777; margin-bottom: 10px; }
        .post-excerpt { color: #555; line-height: 1.6; }
        .read-more { display: inline-block; margin-top: 10px; color: #007bff; text-decoration: none; font-weight: bold; }
        .read-more:hover { text-decoration: underline; }
        .no-posts { text-align: center; color: #777; padding: 20px; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-brand"> <!-- Für den Markennamen/Logo -->
            <a href="index.php"><strong>Mein Blog</strong></a> <!-- "Mi Blog" -->
        </div>
        <div class="nav-auth"> <!-- Für Login/Logout Links -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Meine Posts</a> <!-- "Mis Posts" -->
                <a href="logout.php">Abmelden (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a> <!-- "Cerrar Sesión (...)" -->
            <?php else: ?>
                <a href="login.php">Anmelden</a> <!-- "Iniciar Sesión" -->
                <a href="register.php">Registrieren</a> <!-- "Registrarse" -->
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h1 class="page-title">Neueste Posts</h1> <!-- "Últimos Posts" -->
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-item">
                    <h2><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                    <p class="post-meta">
                        Von: <?php echo htmlspecialchars($post['author_username']); ?> | <!-- "Por:" -->
                        Veröffentlicht am: <?php echo date('d.m.Y', strtotime($post['created_at'])); ?> <!-- "Publicado el:" y formato de fecha alemán -->
                    </p>
                    <p class="post-excerpt"><?php echo nl2br(htmlspecialchars($post['excerpt'])); ?>...</p>
                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="read-more">Weiterlesen →</a> <!-- "Leer más →" -->
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-posts">Noch keine Posts zum Anzeigen vorhanden.</p> <!-- "No hay posts para mostrar todavía." -->
        <?php endif; ?>
    </div>
</body>
</html>