<?php
session_start();
require 'includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Pour un prof, on affiche ses cours. Pour un élève, on affiche tous les cours (à adapter si besoin)
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
if ($role === 'teacher') {
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE teacher_id = ?');
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query('SELECT * FROM courses');
}
$courses = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<div class="d-flex">
    <nav class="bg-primary text-white p-3 min-vh-100" style="width:220px;">
        <div class="mb-4">
            <img src="assets/logo.png" alt="Logo" style="width:40px;">
            <span class="ms-2 fw-bold">LMS</span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="#" class="nav-link text-white"><i class="bi bi-house"></i> Tableau de bord</a></li>
            <li class="nav-item mb-2"><a href="#" class="nav-link text-white"><i class="bi bi-journal-bookmark"></i> Cours</a></li>
            <li class="nav-item mb-2"><a href="#" class="nav-link text-white"><i class="bi bi-chat"></i> Messages</a></li>
            <li class="nav-item mb-2"><a href="#" class="nav-link text-white"><i class="bi bi-calendar"></i> Calendrier</a></li>
            <li class="nav-item mb-2"><a href="logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
        </ul>
    </nav>
    <main class="flex-grow-1 p-4 bg-light">
        <h2 class="mb-4">Mes cours</h2>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($course['year']) ?></h6>
                            <p class="card-text"><?= htmlspecialchars($course['description']) ?></p>
                            <a href="course.php?id=<?= $course['id'] ?>" class="btn btn-outline-primary w-100">Accéder</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?> 