<?php
session_start();
require 'login.php';

// Supprimer le cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, "/");

    // Supprimer aussi en BD
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("UPDATE employes SET remember_token = NULL WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
    }
}

// Détruire la session
$_SESSION = [];
session_destroy();

header("Location: login.php");
exit();
?>
