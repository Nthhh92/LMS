<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$error = '';
$success = '';

// Traitement de l'ajout de cours pour les enseignants
if ($role === 'teacher' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title && $year) {
        $stmt = $pdo->prepare('INSERT INTO courses (title, teacher_id, year, description) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$title, $user_id, $year, $description])) {
            $success = 'Cours ajouté avec succès !';
        } else {
            $error = 'Erreur lors de l\'ajout du cours.';
        }
    } else {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    }
}

// Récupération des cours
if ($role === 'teacher') {
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE teacher_id = ? ORDER BY year DESC');
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query('SELECT c.*, u.nom, u.prenom FROM courses c JOIN users u ON c.teacher_id = u.id ORDER BY c.year DESC');
}
$courses = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Mes cours</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($role === 'teacher'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ajouter un nouveau cours</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre du cours</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="year" class="form-label">Année</label>
                                <input type="text" class="form-control" id="year" name="year" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Ajouter le cours</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <?= htmlspecialchars($course['year']) ?>
                                    <?php if ($role !== 'teacher'): ?>
                                        - Par <?= htmlspecialchars($course['prenom'] . ' ' . $course['nom']) ?>
                                    <?php endif; ?>
                                </h6>
                                <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
                                <a href="course.php?id=<?= $course['id'] ?>" class="btn btn-outline-primary w-100">Accéder au cours</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 