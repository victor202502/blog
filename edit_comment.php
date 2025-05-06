<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['post_id'])) {
    header("Location: index.php");
    exit;
}

$comment_id = (int)$_GET['id'];
$post_id_redirect = (int)$_GET['post_id']; // Para redirigir de vuelta al post
$user_id_session = $_SESSION['user_id'];
$comment = null;
$content = '';
$errors = [];

// 1. Obtener el comentario para editar
try {
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
    $stmt->execute(['id' => $comment_id]);
    $comment = $stmt->fetch();

    if (!$comment) {
        $_SESSION['error_message'] = "Comentario no encontrado.";
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }

    if ($comment['user_id'] != $user_id_session) {
        $_SESSION['error_message'] = "No tienes permiso para editar este comentario.";
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }
    $content = $comment['content'];

} catch (PDOException $e) {
    error_log("Error al obtener comentario para editar: " . $e->getMessage());
    $_SESSION['error_message'] = "Error al cargar comentario.";
    header("Location: view_post.php?id=" . $post_id_redirect);
    exit;
}

// 2. Procesar la actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updated_content = trim($_POST['content']);
    if (empty($updated_content)) {
        $errors[] = "El contenido del comentario no puede estar vacío.";
    } else {
        $content = $updated_content; // Repoblar con el nuevo contenido
    }

    if (empty($errors)) {
        try {
            // Si tienes el trigger para comments.updated_at, se actualiza solo
            $stmt_update = $pdo->prepare("UPDATE comments SET content = :content WHERE id = :id AND user_id = :user_id");
            $stmt_update->execute([
                'content' => $updated_content,
                'id' => $comment_id,
                'user_id' => $user_id_session
            ]);
            $_SESSION['success_message'] = "Comentario actualizado.";
            header("Location: view_post.php?id=" . $post_id_redirect . "#comment-" . $comment_id); // Ir al comentario editado
            exit;
        } catch (PDOException $e) {
            error_log("Error al actualizar comentario: " . $e->getMessage());
            $errors[] = "Error al guardar los cambios.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Comentario</title>
    <style> /* Similar a create_post.php */
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
        <h2>Editar Comentario</h2>
        <?php if (!empty($errors)): ?>
            <div class="errors"><ul><?php foreach ($errors as $error) echo "<li>".htmlspecialchars($error)."</li>"; ?></ul></div>
        <?php endif; ?>

        <form action="edit_comment.php?id=<?php echo $comment_id; ?>&post_id=<?php echo $post_id_redirect; ?>" method="post">
            <div class="form-group">
                <label for="content">Tu comentario:</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            <button type="submit">Actualizar Comentario</button>
        </form>
        <a href="view_post.php?id=<?php echo $post_id_redirect; ?>#comment-<?php echo $comment_id; ?>" class="back-link">Cancelar</a>
    </div>
</body>
</html>