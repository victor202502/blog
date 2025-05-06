<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['post_id'])) {
    $_SESSION['error_message'] = "Información incompleta para eliminar comentario.";
    // Intentar redirigir al post si es posible, sino al index.
    $redirect_url = isset($_GET['post_id']) ? "view_post.php?id=".(int)$_GET['post_id'] : "index.php";
    header("Location: " . $redirect_url);
    exit;
}

$comment_id = (int)$_GET['id'];
$post_id_redirect = (int)$_GET['post_id'];
$user_id_session = $_SESSION['user_id'];

try {
    // Opcional: Verificar propiedad antes de borrar
    $stmt_check = $pdo->prepare("SELECT user_id FROM comments WHERE id = :id");
    $stmt_check->execute(['id' => $comment_id]);
    $comment_owner_id = $stmt_check->fetchColumn();

    if (!$comment_owner_id) {
        $_SESSION['error_message'] = "Comentario no encontrado.";
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }

    if ($comment_owner_id != $user_id_session) {
        $_SESSION['error_message'] = "No tienes permiso para eliminar este comentario.";
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }

    // Proceder a eliminar
    $stmt_delete = $pdo->prepare("DELETE FROM comments WHERE id = :id AND user_id = :user_id");
    $stmt_delete->execute([
        'id' => $comment_id,
        'user_id' => $user_id_session
    ]);

    if ($stmt_delete->rowCount() > 0) {
        $_SESSION['success_message'] = "Comentario eliminado.";
    } else {
        $_SESSION['error_message'] = "No se pudo eliminar el comentario.";
    }
    header("Location: view_post.php?id=" . $post_id_redirect);
    exit;

} catch (PDOException $e) {
    error_log("Error al eliminar comentario: " . $e->getMessage());
    $_SESSION['error_message'] = "Error al eliminar comentario.";
    header("Location: view_post.php?id=" . $post_id_redirect);
    exit;
}
?>