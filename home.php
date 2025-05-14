<?php
session_start();
require 'includes/db.php';

// Récupérer les cours si l'utilisateur est connecté (pour la page courses.php)
$courses = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    if ($role === 'teacher') {
        $stmt = $pdo->prepare('SELECT * FROM courses WHERE teacher_id = ?');
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->query('SELECT * FROM courses');
    }
    $courses = $stmt->fetchAll();
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- Hero Section with Carousel -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <h1 class="display-4 fw-bold mb-4">
                            Bienvenue <?= !empty($_SESSION['prenom']) ? htmlspecialchars($_SESSION['prenom']) : '' ?> 
                            sur votre plateforme d'apprentissage
                        </h1>
                        <p class="lead mb-4">
                            Retrouvez tous vos cours, suivez votre progression et échangez avec votre communauté. 
                            Tout ce dont vous avez besoin pour réussir votre parcours de formation.
                        </p>
                    <?php else: ?>
                        <h1 class="display-4 fw-bold mb-4">Bienvenue sur notre plateforme LMS</h1>
                        <p class="lead mb-4">Une solution complète pour la gestion de vos formations en ligne. Apprenez, partagez et développez vos compétences.</p>
                    <?php endif; ?>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="register.php" class="btn btn-primary btn-lg px-4 me-md-2">Commencer</a>
                            <a href="#features" class="btn btn-outline-secondary btn-lg px-4">En savoir plus</a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="courses.php" class="btn btn-primary btn-lg px-4 me-md-2">Mes cours</a>
                            <a href="#features" class="btn btn-outline-secondary btn-lg px-4">En savoir plus</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                        </div>
                        <div class="carousel-inner rounded shadow">
                            <div class="carousel-item active">
                                <img src="assets/hero-image-1.jpg" class="d-block w-100" alt="LMS Platform 1">
                                <div class="carousel-caption d-none d-md-block">
                                    
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="assets/hero-image-2.jpg" class="d-block w-100" alt="LMS Platform 2">
                                <div class="carousel-caption d-none d-md-block">
                                    
                                </div>
                            </div>
                            <div class="carousel-item">
                                <img src="assets/hero-image-3.jpg" class="d-block w-100" alt="LMS Platform 3">
                                <div class="carousel-caption d-none d-md-block">
                                
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                            <span class="visually-hidden">Précédent</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                            <span class="visually-hidden">Suivant</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section pour tous -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Nos Fonctionnalités</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-book fs-1 text-primary mb-3"></i>
                            <h3 class="card-title">Cours en ligne</h3>
                            <p class="card-text">Accédez à une large gamme de cours et de ressources pédagogiques.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 text-primary mb-3"></i>
                            <h3 class="card-title">Communauté</h3>
                            <p class="card-text">Interagissez avec d'autres apprenants et partagez vos connaissances.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up fs-1 text-primary mb-3"></i>
                            <h3 class="card-title">Suivi de progression</h3>
                            <p class="card-text">Suivez votre progression et obtenez des certificats de réussite.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section pour tous -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4">À propos de notre plateforme</h2>
                    <p class="lead">Notre plateforme LMS est conçue pour offrir une expérience d'apprentissage optimale et interactive.</p>
                    <p>Nous nous engageons à fournir des outils innovants et des ressources de qualité pour faciliter l'apprentissage en ligne.</p>
                </div>
                <div class="col-lg-6">
                    <img src="assets/about-image.jpg" alt="About LMS" class="img-fluid rounded shadow" style="max-width:400px;width:100%;height:auto;">
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> 