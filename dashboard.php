<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username_display = $_SESSION['username'];

// Obtener los posts del usuario logueado
$posts = [];
try {
    $stmt = $pdo->prepare("SELECT id, title, LEFT(content, 150) AS excerpt, created_at FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener posts: " . $e->getMessage());
    // Podrías mostrar un mensaje de error aquí si quieres
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mis Posts</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding:0; background-color: #f9f9f9; }
        .navbar { background-color: #333; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .navbar a:hover { text-decoration: underline; }
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
        .edit-link { color: #ffc107; }
        .delete-link { color: #dc3545; }
        .no-posts { text-align: center; color: #777; padding: 20px; }
        .success-message { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; text-align: center; }

    </style>
</head>
<body>
    <div class="navbar">
        <div>
            <a href="index.php">Ver Todos los Posts</a> <!-- Enlace a la página principal del blog -->
            Bienvenido, <?php echo htmlspecialchars($username_display); ?>!
        </div>
        <a href="logout.php">Cerrar Sesión</a>
    </div>

    <div class="container">
        <div class="header">
            <h1>Mis Posts</h1>
            <a href="create_post.php" class="create-post-btn">Crear Nuevo Post</a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>

        <div class="post-list">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-item">
                        <h3><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <p class="post-meta">Publicado el: <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></p>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?>...</p>
                        <div class="post-actions">
                            <a href="view_post.php?id=<?php echo $post['id']; ?>" class="view-link">Ver</a>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit-link">Editar</a>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este post?');" class="delete-link">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-posts">Aún no has creado ningún post. ¡<a href="create_post.php">Crea uno ahora</a>!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>