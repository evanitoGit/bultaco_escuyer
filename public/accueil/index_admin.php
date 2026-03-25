<?php

session_start();
require_once realpath(__DIR__ . '/../../config.php');

$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'presentation'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'presentation'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte mis à jour avec succès !";

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
            <h2 class="admin-title">Modification du texte de présentation</h2>

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
            <div class="links">
                <p>Autres pages à modifier</p>
                <ul>
                    <li><a href="#">Accueil</a></li>
                    <li><a href="../restauration/admin_restauration.php">Restauration</a></li>
                    <li><a href="../pieces/admin_pieces.php">Pièces détachées</a></li>
                    <li><a href="../album/admin_album.php">Album photos</a></li>
                    <li><a href="../logos/logos_admin.php">Logos</a></li>
                    <li><a href="../pilotes/pilotes_admin.php">Pilotes de légende</a></li>
                    <li><a href="../press/pressbook_admin.php">Pressbook</a></li>
                </ul>
            </div>
            <div class="admin-links">
                <a href="../../logout.php" class="admin-link">Déconnexion</a>
            </div>
        </div>
    <?php endif; ?>
</body>

</html>