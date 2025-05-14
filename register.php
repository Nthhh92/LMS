<?php
require 'includes/db.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'student';
    if (!$nom || !$prenom || !$username || !$password) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Ce nom d'utilisateur existe déjà.";
        } else {
            // Hasher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // Insérer l'utilisateur avec le mot de passe hashé
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role, nom, prenom) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$username, $hashedPassword, $role, $nom, $prenom]);
            $success = 'Inscription réussie ! Vous pouvez vous connecter.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <img src="assets/logo.png" alt="Logo" style="width: 60px;">
            <h3 class="mt-2">Inscription LMS</h3>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="student">Étudiant</option>
                    <option value="teacher">Enseignant</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success w-100">S'inscrire</button>
            <div class="mt-3 text-center">
                <a href="index.php">Déjà un compte ? Se connecter</a>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 