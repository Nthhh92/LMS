<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="mb-4">Calendrier</h1>
                    <p class="lead">Cette fonctionnalité sera disponible prochainement.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 