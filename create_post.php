<?php
session_start();
require_once 'db_connect.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$title = '';
$content = '';
// Para manejo de imágenes, lo añadiremos después para simplificar ahora

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id']; // El ID del usuario logueado

    if (empty($title)) {
        $errors[] = "El título es obligatorio.";
    }
    if (empty($content)) {
        $errors[] = "El contenido es obligatorio.";
    }

    if (empty($errors)) {
        try {
            // Si no tienes el trigger para updated_at, tendrías que incluirlo aquí:
            // $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, updated_at) VALUES (:user_id, :title, :content, NOW())");
            // Si tienes el trigger, created_at y updated_at se manejan solos o created_at por defecto y updated_at por trigger
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (:user_id, :title, :content)");
            $stmt->execute([
                'user_id' => $user_id,
                'title' => $title,
                'content' => $content
            ]);

            $_SESSION['success_message'] = "¡Post creado exitosamente!";
            header("Location: dashboard.php"); // O a una página que liste los posts
            exit;

        } catch (PDOException $e) {
            error_log("Error al crear post: " . $e->getMessage());
            $errors[] = "Ocurrió un error al crear el post. Inténtalo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Post</title>
    <style>
        /* Puedes usar los mismos estilos de container, form-group, etc. de register/login */
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 20px; background-color: #f4f4f4; margin: 0; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 600px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], textarea { width: calc(100% - 22px); padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { min-height: 150px; resize: vertical; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .errors { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }
        .errors ul { margin: 0; padding-left: 20px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Crear Nuevo Post</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="create_post.php" method="post">
            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="form-group">
                <label for="content">Contenido:</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            <button type="submit">Crear Post</button>
        </form>
        <a href="dashboard.php" class="back-link">Volver al Dashboard</a>
    </div>
</body>
</html>