<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['post_id'])) {
    $_SESSION['error_message'] = "Unvollständige Informationen zum Löschen des Kommentars."; // "Información incompleta para eliminar comentario."
    $redirect_url = isset($_GET['post_id']) ? "view_post.php?id=".(int)$_GET['post_id'] : "index.php";
    header("Location: " . $redirect_url);
    exit;
}

$comment_id = (int)$_GET['id'];
$post_id_redirect = (int)$_GET['post_id'];
$user_id_session = $_SESSION['user_id'];

try {
    // Eigentümer vor dem Löschen überprüfen
    $stmt_check = $pdo->prepare("SELECT user_id FROM comments WHERE id = :id");
    $stmt_check->execute(['id' => $comment_id]);
    $comment_owner_id = $stmt_check->fetchColumn();

    if (!$comment_owner_id) {
        $_SESSION['error_message'] = "Kommentar nicht gefunden."; // "Comentario no encontrado."
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }

    if ($comment_owner_id != $user_id_session) {
        $_SESSION['error_message'] = "Du hast keine Berechtigung, diesen Kommentar zu löschen."; // "No tienes permiso para eliminar este comentario."
        header("Location: view_post.php?id=" . $post_id_redirect);
        exit;
    }

    // Mit dem Löschen fortfahren
    $stmt_delete = $pdo->prepare("DELETE FROM comments WHERE id = :id AND user_id = :user_id");
    $stmt_delete->execute([
        'id' => $comment_id,
        'user_id' => $user_id_session
    ]);

    if ($stmt_delete->rowCount() > 0) {
        $_SESSION['success_message'] = "Kommentar gelöscht."; // "Comentario eliminado."
    } else {
        // Dies könnte passieren, wenn der Kommentar bereits gelöscht wurde oder ein Problem aufgetreten ist
        $_SESSION['error_message'] = "Der Kommentar konnte nicht gelöscht werden oder existierte nicht mehr."; // "No se pudo eliminar el comentario."
    }
    header("Location: view_post.php?id=" . $post_id_redirect);
    exit;

} catch (PDOException $e) {
    error_log("Fehler beim Löschen des Kommentars: " . $e->getMessage());
    $_SESSION['error_message'] = "Fehler beim Löschen des Kommentars."; // "Error al eliminar comentario."
    header("Location: view_post.php?id=" . $post_id_redirect);
    exit;
}
?>