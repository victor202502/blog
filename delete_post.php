<?php
session_start();
require_once 'db_connect.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID de post no especificado.";
    header("Location: dashboard.php");
    exit;
}

$post_id = $_GET['id'];
$user_id_session = $_SESSION['user_id'];

try {
    // Opcional: Verificar que el post existe y pertenece al usuario antes de borrar
    $stmt_check = $pdo->prepare("SELECT user_id FROM posts WHERE id = :id");
    $stmt_check->execute(['id' => $post_id]);
    $post_owner = $stmt_check->fetchColumn();

    if (!$post_owner) {
        $_SESSION['error_message'] = "Post no encontrado.";
        header("Location: dashboard.php");
        exit;
    }

    if ($post_owner != $user_id_session) {
        $_SESSION['error_message'] = "No tienes permiso para eliminar este post.";
        header("Location: dashboard.php"); // O a view_post.php?id=$post_id
        exit;
    }

    // Proceder a eliminar
    $stmt_delete = $pdo->prepare("DELETE FROM posts WHERE id = :id AND user_id = :user_id");
    $stmt_delete->execute([
        'id' => $post_id,
        'user_id' => $user_id_session // Doble verificación
    ]);

    if ($stmt_delete->rowCount() > 0) {
        $_SESSION['success_message'] = "Post eliminado exitosamente.";
    } else {
        // Esto podría pasar si el post ya fue eliminado o hubo un problema
        $_SESSION['error_message'] = "No se pudo eliminar el post o ya no existía.";
    }
    header("Location: dashboard.php");
    exit;

} catch (PDOException $e) {
    error_log("Error al eliminar post: " . $e->getMessage());
    $_SESSION['error_message'] = "Ocurrió un error al eliminar el post.";
    header("Location: dashboard.php");
    exit;
}
?>