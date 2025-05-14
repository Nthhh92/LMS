<?php
session_start();
session_destroy();

// Supprimer le cookie "Se souvenir de moi"
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/', '', true, true);
}

header('Location: home.php');
exit;
?> 