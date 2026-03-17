<?php

session_start();
require_once realpath(__DIR__ . '/../../config.php');

// Vérification simple (à améliorer avec une vraie authentification)
$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

// Récupérer le texte actuel
$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'presentation'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'presentation'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte mis à jour avec succès !";

    // Récupérer le nouveau texte
    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'presentation'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>
<body>
<?php if (!$isAdmin): ?>
    <div class="admin-container">
        <h2 class="admin-title">Connexion Admin</h2>
        <form method="POST" action="../../login.php">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="btn-update">Se connecter</button>
        </form>
    </div>
<?php else: ?>
    <div class="admin-container">
        <h2 class="admin-title">Administration - Modifier le texte de présentation</h2>

        <?php if (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="contenu" style="color: var(--carbone); font-weight: bold; display: block; margin-bottom: 10px;">
                Texte de présentation :
            </label>
            <textarea name="contenu" id="contenu" required><?php echo htmlspecialchars($texte['contenu']); ?></textarea>

            <div style="text-align: center;">
                <button type="submit" class="btn-update">Valider les modifications</button>
            </div>
        </form>
    </div>
    <div class="admin-links">
        <a href="../../logout.php" class="admin-link">Déconnexion</a>
    </div>
<?php endif; ?>
</body>
</html>