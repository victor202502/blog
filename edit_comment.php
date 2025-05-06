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
    <style> /* Stile bleiben gleich */
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 20px; background-color: #f4f4f4; margin: 0; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 600px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        textarea { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; min-height: 100px; resize:vertical; }
        button { background-color: #ffc107; color: black; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #e0a800; }
        .errors { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .errors ul { margin: 0; padding-left: 20px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Kommentar bearbeiten</h2> <!-- "Editar Comentario" -->
        <?php if (!empty($errors)): ?>
            <div class="errors"><ul><?php foreach ($errors as $error) echo "<li>".htmlspecialchars($error)."</li>"; ?></ul></div>
        <?php endif; ?>

        <form action="edit_comment.php?id=<?php echo $comment_id; ?>&post_id=<?php echo $post_id_redirect; ?>" method="post">
            <div class="form-group">
                <label for="content">Dein Kommentar:</label> <!-- "Tu comentario:" -->
                <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            <button type="submit">Kommentar aktualisieren</button> <!-- "Actualizar Comentario" -->
        </form>
        <a href="view_post.php?id=<?php echo $post_id_redirect; ?>#comment-<?php echo $comment_id; ?>" class="back-link">Abbrechen</a> <!-- "Cancelar" -->
    </div>
</body>
</html>