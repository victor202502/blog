<?php
session_start(); // Para saber si hay un usuario logueado para la barra de navegación
require_once 'db_connect.php';

$posts = [];
try {
    // Obtener todos los posts, mostrando el nombre de usuario del autor
    $sql = "SELECT p.id, p.title, LEFT(p.content, 200) AS excerpt, p.created_at, u.username AS author_username
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->query($sql); // Usamos query() ya que no hay parámetros de usuario
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener todos los posts: " . $e->getMessage());
    // Podrías mostrar un mensaje de error aquí
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Sencillo</title>
    <style>
        /* Puedes reutilizar estilos del dashboard o crear unos nuevos */
        body { font-family: sans-serif; margin: 0; padding:0; background-color: #f9f9f9; }
        .navbar { background-color: #333; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .navbar a:hover { text-decoration: underline; }
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
        <h1 class="page-title">Últimos Posts</h1>
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-item">
                    <h2><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                    <p class="post-meta">
                        Por: <?php echo htmlspecialchars($post['author_username']); ?> |
                        Publicado el: <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                    </p>
                    <p class="post-excerpt"><?php echo nl2br(htmlspecialchars($post['excerpt'])); ?>...</p>
                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="read-more">Leer más →</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-posts">No hay posts para mostrar todavía.</p>
        <?php endif; ?>
    </div>
</body>
</html>