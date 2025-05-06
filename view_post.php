<?php
session_start();
require_once 'db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = $_GET['id'];
$post = null;

try {
    $stmt = $pdo->prepare("SELECT p.*, u.username AS author_username
                           FROM posts p
                           JOIN users u ON p.user_id = u.id
                           WHERE p.id = :id");
    $stmt->execute(['id' => $post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        // Post no encontrado, podrías redirigir o mostrar mensaje
        header("Location: index.php"); // Simple redirección por ahora
        exit;
    }
} catch (PDOException $e) {
    error_log("Error al ver post: " . $e->getMessage());
    die("Error al cargar el post.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Mi Blog</title>
    <style>
        /* Estilos similares a index.php y dashboard.php */
        body { font-family: sans-serif; margin: 0; padding:0; background-color: #f9f9f9; }
        .navbar { background-color: #333; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .navbar a:hover { text-decoration: underline; }
        .container { padding: 20px; max-width: 800px; margin: 20px auto; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .post-title { font-size: 2.5em; margin-bottom: 10px; color: #333; }
        .post-meta { color: #777; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .post-content { line-height: 1.7; color: #444; font-size: 1.1em; }
        .post-content img { max-width: 100%; height: auto; border-radius: 5px; margin-top: 10px; margin-bottom: 10px; } /* Para futuras imágenes */
        .actions { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .actions a { margin-right: 15px; text-decoration: none; padding: 8px 12px; border-radius: 4px; }
        .edit-link { background-color: #ffc107; color: black; }
        .delete-link { background-color: #dc3545; color: white; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; }
    </style>
</head>
<body>
    <div class="navbar">
         <div>
            <a href="index.php"><strong>Mi Blog</strong></a>
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Mis Posts</a>
                <a href="logout.php">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Iniciar Sesión</a>
                <a href="register.php">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="post-meta">
            Por: <?php echo htmlspecialchars($post['author_username']); ?> |
            Publicado el: <?php echo date('d F, Y \a \l\a\s H:i', strtotime($post['created_at'])); ?>
            <?php if ($post['created_at'] != $post['updated_at']): ?>
                | Última actualización: <?php echo date('d F, Y \a \l\a\s H:i', strtotime($post['updated_at'])); ?>
            <?php endif; ?>
        </p>
        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); // nl2br para convertir saltos de línea a <br> ?>
            <!-- Aquí mostrarías la imagen si la tuvieras: -->
            <!-- <?php if (!empty($post['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Imagen del post">
            <?php endif; ?> -->
        </div>

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
            <div class="actions">
                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit-link">Editar Post</a>
                <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este post?');" class="delete-link">Eliminar Post</a>
            </div>
        <?php endif; ?>
        <a href="index.php" class="back-link">← Volver a todos los posts</a>
    </div>
</body>
</html>