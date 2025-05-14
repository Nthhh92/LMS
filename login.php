<?php
session_start();
require 'includes/db.php';

$error = '';
$remembered_username = '';

// Vérifier si un cookie de connexion existe
if (isset($_COOKIE['remember_me'])) {
    $cookie_data = json_decode($_COOKIE['remember_me'], true);
    if ($cookie_data) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$cookie_data['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['nom'] = $user['nom'];
            header('Location: home.php');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Récupérer l'utilisateur avec son nom d'utilisateur uniquement
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Vérifier le mot de passe avec password_verify
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];

        // Si "Se souvenir de moi" est coché, créer un cookie
        if ($remember) {
            $cookie_data = [
                'user_id' => $user['id'],
                'expires' => time() + (30 * 24 * 60 * 60) // 30 jours
            ];
            setcookie('remember_me', json_encode($cookie_data), $cookie_data['expires'], '/', '', true, true);
        }

        header('Location: home.php');
        exit;
    } else {
        $error = 'Identifiants invalides';
        $remembered_username = $username;
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow" style="max-width: 350px; width: 100%;">
        <div class="text-center mb-4">
            <img src="assets/logo.png" alt="Logo" style="width: 60px;">
            <h3 class="mt-2">LMS Connexion</h3>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($remembered_username) ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <div class="mt-3 text-center">
            <p class="mb-2">Pas encore de compte ?</p>
            <a href="register.php" class="btn btn-outline-primary w-100">Créer un compte</a>
            <div class="mt-3">
                <a href="home.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 