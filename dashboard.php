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
    <style>
        body { font-family: sans-serif; margin: 0; padding:0; background-color: #f9f9f9; }
        .navbar { background-color: #333; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar .nav-links a, .navbar .user-info a { color: white; text-decoration: none; margin-left: 15px; }
        .navbar .nav-links a:hover, .navbar .user-info a:hover { text-decoration: underline; }
        .navbar .user-info { display: flex; align-items: center; }
        .navbar .user-info span { margin-right: 15px; }

        .container { padding: 20px; max-width: 900px; margin: 20px auto; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .header h1 { margin: 0; }
        .create-post-btn { background-color: #28a745; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-size: 16px;}
        .create-post-btn:hover { background-color: #218838; }
        .post-list { margin-top: 20px; }
        .post-item { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; background-color: #fff; }
        .post-item h3 { margin-top: 0; }
        .post-item h3 a { text-decoration: none; color: #007bff; }
        .post-item p { margin-bottom: 10px; color: #555; }
        .post-meta { font-size: 0.9em; color: #777; margin-bottom:10px; }
        .post-actions a { margin-right: 10px; text-decoration: none; }
        .view-link { color: #17a2b8; } /* Añadido para distinguir "Ver" */
        .edit-link { color: #ffc107; }
        .delete-link { color: #dc3545; }
        .no-posts { text-align: center; color: #777; padding: 20px; }
        .success-message { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="nav-links">
            <a href="index.php"><strong>Hauptblog</strong></a> <!-- "Blog Principal" -->
        </div>
        <div class="user-info">
            <span>Willkommen, <?php echo htmlspecialchars($username_display); ?>!</span> <!-- "Bienvenido, ..." -->
            <a href="logout.php">Abmelden</a> <!-- "Cerrar Sesión" -->
        </div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Meine Posts</h1> <!-- "Mis Posts" -->
            <a href="create_post.php" class="create-post-btn">Neuen Post erstellen</a> <!-- "Crear Nuevo Post" -->
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success-message">
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
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): // Für Fehlermeldungen von anderen Seiten ?>
            <div class="errors" style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; text-align:center;">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>


        <div class="post-list">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-item">
                        <h3><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <p class="post-meta">Veröffentlicht am: <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></p> <!-- "Publicado el:" y formato de fecha alemán -->
                        <p><?php echo htmlspecialchars($post['excerpt']); ?>...</p>
                        <div class="post-actions">
                            <a href="view_post.php?id=<?php echo $post['id']; ?>" class="view-link">Ansehen</a> <!-- "Ver" -->
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit-link">Bearbeiten</a> <!-- "Editar" -->
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Bist du sicher, dass du diesen Post löschen möchtest?');" class="delete-link">Löschen</a> <!-- "¿Estás seguro de que quieres eliminar este post?" y "Eliminar" -->
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-posts">
                    Du hast noch keine Posts erstellt.
                    <a href="create_post.php">Erstelle jetzt einen</a> oder <a href="index.php">erkunde den Hauptblog</a>!
                    <!-- "Aún no has creado ningún post. ¡Crea uno ahora o explora el blog principal!" -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>