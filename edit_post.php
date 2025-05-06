<?php
session_start();
require_once 'db_connect.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php"); // O a index.php
    exit;
}

$post_id = $_GET['id'];
$user_id_session = $_SESSION['user_id'];
$post = null;
$title = '';
$content = '';
$errors = [];

// 1. Obtener los datos del post para pre-llenar el formulario
try {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->execute(['id' => $post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        $_SESSION['error_message'] = "Post no encontrado.";
        header("Location: dashboard.php");
        exit;
    }

    // Verificar que el usuario logueado es el autor del post
    if ($post['user_id'] != $user_id_session) {
        $_SESSION['error_message'] = "No tienes permiso para editar este post.";
        header("Location: dashboard.php"); // O a view_post.php?id=$post_id
        exit;
    }

    $title = $post['title'];
    $content = $post['content'];

} catch (PDOException $e) {
    error_log("Error al obtener post para editar: " . $e->getMessage());
    $_SESSION['error_message'] = "Error al cargar el post para editar.";
    header("Location: dashboard.php");
    exit;
}


// 2. Procesar el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title_updated = trim($_POST['title']);
    $content_updated = trim($_POST['content']);

    if (empty($title_updated)) {
        $errors[] = "El título es obligatorio.";
    }
    if (empty($content_updated)) {
        $errors[] = "El contenido es obligatorio.";
    }

    // Actualizar variables para repoblar el formulario en caso de error
    $title = $title_updated;
    $content = $content_updated;

    if (empty($errors)) {
        try {
            // Si no tienes el trigger para updated_at, tendrías que añadir SET updated_at = NOW()
            $stmt_update = $pdo->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id AND user_id = :user_id");
            $stmt_update->execute([
                'title' => $title_updated,
                'content' => $content_updated,
                'id' => $post_id,
                'user_id' => $user_id_session // Doble verificación de propiedad
            ]);

            $_SESSION['success_message'] = "¡Post actualizado exitosamente!";
            header("Location: view_post.php?id=" . $post_id);
            exit;

        } catch (PDOException $e) {
            error_log("Error al actualizar post: " . $e->getMessage());
            $errors[] = "Ocurrió un error al actualizar el post. Inténtalo de nuevo.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Post</title>
    <style> /* Reutilizar estilos de create_post.php */
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 20px; background-color: #f4f4f4; margin: 0; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 600px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], textarea { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { min-height: 150px; resize: vertical; }
        button { background-color: #ffc107; color: black; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #e0a800; }
        .errors { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .errors ul { margin: 0; padding-left: 20px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Post</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="edit_post.php?id=<?php echo $post_id; ?>" method="post">
            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Contenido:</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            <button type="submit">Actualizar Post</button>
        </form>
        <a href="view_post.php?id=<?php echo $post_id; ?>" class="back-link">Cancelar y Volver al Post</a>
         <a href="dashboard.php" class="back-link" style="margin-top:10px;">Volver al Dashboard</a>
    </div>
</body>
</html>