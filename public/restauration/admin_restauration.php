<?php
session_start();
require_once realpath(__DIR__ . '/../../config.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'restauration'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_texte'])) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'restauration'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte mis à jour avec succès !";

    // Récupérer le nouveau texte
    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'restauration'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_photo'])) {
    $nomModele = $_POST['nom_modele'];
    $colonne = $_POST['colonne'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5 Mo

        if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid() . '.' . $extension;
            $uploadPath = 'uploads/' . $newFileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $stmtOrdre = $pdo->prepare("SELECT MAX(ordre) as max_ordre FROM restauration_photos WHERE colonne = :colonne");
                $stmtOrdre->execute(['colonne' => $colonne]);
                $maxOrdre = $stmtOrdre->fetch(PDO::FETCH_ASSOC);
                $nouvelOrdre = ($maxOrdre['max_ordre'] ?? 0) + 1;

                $stmt = $pdo->prepare("INSERT INTO restauration_photos (nom_modele, image_path, colonne, ordre) VALUES (:nom, :path, :colonne, :ordre)");
                $stmt->execute([
                    'nom' => $nomModele,
                    'path' => $uploadPath,
                    'colonne' => $colonne,
                    'ordre' => $nouvelOrdre
                ]);

                $message = "Photo ajoutée avec succès !";
            } else {
                $error = "Erreur lors de l'upload du fichier.";
            }
        } else {
            $error = "Type de fichier non autorisé ou fichier trop volumineux (max 5 Mo).";
        }
    } else {
        $error = "Aucun fichier sélectionné ou erreur d'upload.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_photo'])) {
    $photoId = $_POST['photo_id'];

    $stmt = $pdo->prepare("SELECT image_path FROM restauration_photos WHERE id = :id");
    $stmt->execute(['id' => $photoId]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($photo) {
        if (file_exists($photo['image_path'])) {
            unlink($photo['image_path']);
        }

        $stmt = $pdo->prepare("DELETE FROM restauration_photos WHERE id = :id");
        $stmt->execute(['id' => $photoId]);

        $message = "Photo supprimée avec succès !";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_photo'])) {
    $photoId = $_POST['photo_id'];
    $nouveauNom = $_POST['nouveau_nom'];

    $stmt = $pdo->prepare("UPDATE restauration_photos SET nom_modele = :nom WHERE id = :id");
    $stmt->execute(['nom' => $nouveauNom, 'id' => $photoId]);

    $message = "Nom du modèle modifié avec succès !";
}

// Récupérer toutes les photos
$stmtGauche = $pdo->prepare("SELECT * FROM restauration_photos WHERE colonne = 'gauche' ORDER BY ordre ASC");
$stmtGauche->execute();
$photosGauche = $stmtGauche->fetchAll(PDO::FETCH_ASSOC);

$stmtDroite = $pdo->prepare("SELECT * FROM restauration_photos WHERE colonne = 'droite' ORDER BY ordre ASC");
$stmtDroite->execute();
$photosDroite = $stmtDroite->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Restauration - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>
<body>
<div class="admin-container">
    <h1 class="admin-title">📝 Administration - Restauration</h1>

    <?php if ($message): ?>
        <div class="message">✅ <?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="admin-section">
        <h2 class="section-title">Modifier le texte de présentation</h2>
        <form method="POST">
            <div class="form-group">
                <label for="contenu" class="form-label">Texte de présentation :</label>
                <textarea name="contenu" id="contenu" required><?php echo htmlspecialchars($texte['contenu']); ?></textarea>
            </div>
            <button type="submit" name="update_texte" class="btn">Mettre à jour le texte</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Ajouter une nouvelle photo</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom_modele" class="form-label">Nom du modèle :</label>
                <input type="text" name="nom_modele" id="nom_modele" required placeholder="Ex: Bultaco Sherpa T">
            </div>

            <div class="form-group">
                <label for="colonne" class="form-label">Colonne :</label>
                <select name="colonne" id="colonne" required>
                    <option value="gauche">Avant</option>
                    <option value="droite">Après</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Image (JPG, PNG, GIF - Max 5 Mo) :</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>

            <button type="submit" name="ajouter_photo" class="btn">Ajouter la photo</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Gérer les photos existantes</h2>
        <div class="photos-admin-container">
            <div class="colonne-admin">
                <h3>Colonne Avant</h3>
                <?php foreach ($photosGauche as $photo): ?>
                    <div class="photo-admin-item">
                        <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['nom_modele']); ?>">
                        <p><strong><?php echo htmlspecialchars($photo['nom_modele']); ?></strong></p>

                        <div class="photo-admin-actions">
                            <button onclick="toggleEdit(<?php echo $photo['id']; ?>)" class="btn">Modifier</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?');">
                                <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                <button type="submit" name="supprimer_photo" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>

                        <div class="edit-form" id="edit-<?php echo $photo['id']; ?>">
                            <form method="POST">
                                <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                <div class="form-group">
                                    <label class="form-label">Nouveau nom :</label>
                                    <input type="text" name="nouveau_nom" value="<?php echo htmlspecialchars($photo['nom_modele']); ?>" required>
                                </div>
                                <button type="submit" name="modifier_photo" class="btn">Enregistrer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($photosGauche)): ?>
                    <p style="text-align: center; color: #999;">Aucune photo dans cette colonne</p>
                <?php endif; ?>
            </div>

            <div class="colonne-admin">
                <h3>Colonne Après</h3>
                <?php foreach ($photosDroite as $photo): ?>
                    <div class="photo-admin-item">
                        <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['nom_modele']); ?>">
                        <p><strong><?php echo htmlspecialchars($photo['nom_modele']); ?></strong></p>

                        <div class="photo-admin-actions">
                            <button onclick="toggleEdit(<?php echo $photo['id']; ?>)" class="btn">Modifier</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?');">
                                <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                <button type="submit" name="supprimer_photo" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>

                        <div class="edit-form" id="edit-<?php echo $photo['id']; ?>">
                            <form method="POST">
                                <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                <div class="form-group">
                                    <label class="form-label">Nouveau nom :</label>
                                    <input type="text" name="nouveau_nom" value="<?php echo htmlspecialchars($photo['nom_modele']); ?>" required>
                                </div>
                                <button type="submit" name="modifier_photo" class="btn">Enregistrer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($photosDroite)): ?>
                    <p style="text-align: center; color: #999;">Aucune photo dans cette colonne</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="admin-links">
        <a href="../../logout.php" class="admin-link">Déconnexion</a>
    </div>
</div>

<script>
    function toggleEdit(photoId) {
        const editForm = document.getElementById('edit-' + photoId);
        if (editForm.style.display === 'none' || editForm.style.display === '') {
            editForm.style.display = 'block';
        } else {
            editForm.style.display = 'none';
        }
    }
</script>
</body>
</html>