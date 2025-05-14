<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Traitement de l'envoi de message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = $_POST['recipient_id'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if ($recipient_id && $message) {
        $stmt = $pdo->prepare('INSERT INTO messages (sender_id, recipient_id, message, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)');
        if ($stmt->execute([$user_id, $recipient_id, $message])) {
            $success = 'Message envoyé avec succès !';
        } else {
            $error = 'Erreur lors de l\'envoi du message.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}

// Récupération des utilisateurs pour le formulaire
$stmt = $pdo->prepare('SELECT id, username, nom, prenom, role FROM users WHERE id != ? ORDER BY role, nom, prenom');
$stmt->execute([$user_id]);
$users = $stmt->fetchAll();

// Récupération des conversations
$stmt = $pdo->prepare('
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = ? THEN m.recipient_id
            ELSE m.sender_id
        END as other_user_id,
        u.nom, u.prenom, u.username, u.role,
        (SELECT message FROM messages 
         WHERE (sender_id = ? AND recipient_id = other_user_id) 
            OR (sender_id = other_user_id AND recipient_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages 
         WHERE (sender_id = ? AND recipient_id = other_user_id) 
            OR (sender_id = other_user_id AND recipient_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message_date,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = other_user_id AND recipient_id = ? AND is_read = 0) as unread_count
    FROM messages m
    JOIN users u ON u.id = CASE 
        WHEN m.sender_id = ? THEN m.recipient_id
        ELSE m.sender_id
    END
    WHERE m.sender_id = ? OR m.recipient_id = ?
    ORDER BY last_message_date DESC
');
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

// Récupération des messages d'une conversation spécifique
$selected_user_id = $_GET['user'] ?? null;
$messages = [];
$selected_user = null;

if ($selected_user_id) {
    // Marquer les messages comme lus
    $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE sender_id = ? AND recipient_id = ? AND is_read = 0');
    $stmt->execute([$selected_user_id, $user_id]);

    // Récupérer les messages
    $stmt = $pdo->prepare('
        SELECT m.*, u.nom, u.prenom, u.username 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.recipient_id = ?) 
           OR (m.sender_id = ? AND m.recipient_id = ?)
        ORDER BY m.created_at ASC
    ');
    $stmt->execute([$user_id, $selected_user_id, $selected_user_id, $user_id]);
    $messages = $stmt->fetchAll();

    // Obtenir les informations de l'utilisateur sélectionné
    foreach ($users as $u) {
        if ($u['id'] == $selected_user_id) {
            $selected_user = $u;
            break;
        }
    }
}

// Calculer nombre total de messages non lus
$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM messages WHERE recipient_id = ? AND is_read = 0');
$stmt->execute([$user_id]);
$unread_total = $stmt->fetch()['count'];
?>
<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Mes messages</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                    <i class="bi bi-plus-circle"></i> Nouveau message
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Liste des conversations -->
        <div class="col-md-4">
            <div class="card">
                <div class="list-group list-group-flush">
                    <?php if (empty($conversations)): ?>
                        <div class="list-group-item text-center text-muted py-5">
                            <i class="bi bi-chat-dots display-4 mb-3"></i>
                            <p>Aucune conversation</p>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                                Commencer une conversation
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="?user=<?= $conv['other_user_id'] ?>" class="list-group-item list-group-item-action d-flex align-items-center position-relative <?= $selected_user_id == $conv['other_user_id'] ? 'active bg-primary text-white' : '' ?>">
                                <div class="me-2">
                                    <div class="avatar bg-light rounded-circle text-center d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; <?= $selected_user_id == $conv['other_user_id'] ? 'background-color: rgba(255,255,255,0.2) !important;' : '' ?>">
                                        <span class="fw-bold"><?= strtoupper(substr($conv['prenom'], 0, 1) . substr($conv['nom'], 0, 1)) ?></span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-truncate"><?= htmlspecialchars($conv['username']) ?></h6>
                                        <small class="text-nowrap ms-2 <?= $selected_user_id == $conv['other_user_id'] ? 'text-white-50' : 'text-muted' ?>">
                                            <?= date('d/m/Y H:i', strtotime($conv['last_message_date'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-0 text-truncate small <?= $selected_user_id == $conv['other_user_id'] ? 'text-white-50' : 'text-muted' ?>">
                                        <?= htmlspecialchars($conv['last_message']) ?>
                                    </p>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger">
                                        <?= $conv['unread_count'] ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Conversation -->
        <div class="col-md-8">
            <div class="card h-100 d-flex flex-column">
                <?php if ($selected_user): ?>
                    <!-- Entête conversation -->
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle text-center d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <span class="fw-bold"><?= strtoupper(substr($selected_user['prenom'], 0, 1) . substr($selected_user['nom'], 0, 1)) ?></span>
                            </div>
                            <div>
                                <h5 class="mb-0">Conversation avec <?= htmlspecialchars($selected_user['prenom'] . ' ' . $selected_user['nom']) ?></h5>
                                <small class="text-muted"><?= $selected_user['role'] === 'teacher' ? 'Enseignant' : 'Étudiant' ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <div class="card-body p-4 overflow-auto" style="max-height: 500px; flex: 1 1 auto;" id="messagesContainer">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-muted my-5">
                                <i class="bi bi-chat-dots display-4 mb-3"></i>
                                <p>Aucun message dans cette conversation</p>
                                <p>Envoyez le premier message !</p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $current_date = null;
                            foreach ($messages as $message): 
                                $message_date = date('Y-m-d', strtotime($message['created_at']));
                                if ($message_date != $current_date):
                                    $current_date = $message_date;
                                    $date_display = date('d/m/Y', strtotime($message['created_at']));
                                    if ($message_date == date('Y-m-d')) {
                                        $date_display = "Aujourd'hui";
                                    } elseif ($message_date == date('Y-m-d', strtotime('-1 day'))) {
                                        $date_display = "Hier";
                                    }
                            ?>
                                <div class="text-center my-3">
                                    <span class="badge bg-light text-dark"><?= $date_display ?></span>
                                </div>
                            <?php endif; ?>
                                <div class="message <?= $message['sender_id'] == $user_id ? 'sent' : 'received' ?> mb-3">
                                    <div class="message-content p-3 rounded <?= $message['sender_id'] == $user_id ? 'bg-primary text-white' : 'bg-light' ?>" 
                                         style="max-width: 80%; <?= $message['sender_id'] == $user_id ? 'margin-left: auto;' : 'margin-right: auto;' ?>">
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                        <small class="<?= $message['sender_id'] == $user_id ? 'text-white-50' : 'text-muted' ?>">
                                            <?= date('H:i', strtotime($message['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Formulaire envoi de message -->
                    <div class="card-footer bg-white p-3">
                        <form id="messageForm" method="post" class="d-flex align-items-center">
                            <input type="hidden" name="recipient_id" value="<?= $selected_user_id ?>">
                            <div class="flex-grow-1 me-2">
                                <input type="text" class="form-control" id="message" name="message" placeholder="Écrivez votre message..." required autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-5">
                        <i class="bi bi-chat-dots display-1 text-muted mb-4"></i>
                        <h4>Sélectionnez une conversation</h4>
                        <p class="text-muted">Ou commencez une nouvelle conversation</p>
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                            <i class="bi bi-plus-circle me-2"></i> Nouveau message
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouveau Message -->
<div class="modal fade" id="newMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label for="recipient" class="form-label">Destinataire</label>
                        <select class="form-select" id="recipient" name="recipient_id" required>
                            <option value="">Choisir un destinataire</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?> 
                                    (<?= $user['role'] === 'teacher' ? 'Enseignant' : 'Étudiant' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modalMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="modalMessage" name="message" rows="4" required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.messages-container {
    display: flex;
    flex-direction: column;
}
.message.sent .message-content {
    border-radius: 18px 18px 0 18px;
}
.message.received .message-content {
    border-radius: 18px 18px 18px 0;
}
.avatar {
    color: #333;
    background-color: #f1f1f1;
}
</style>

<script>
// Scroll to bottom of messages container
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Submit form with Enter key
    const messageInput = document.getElementById('message');
    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('messageForm').submit();
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?> 