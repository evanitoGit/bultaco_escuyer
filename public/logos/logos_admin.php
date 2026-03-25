<?php
session_start();
require_once realpath(__DIR__ . '/../../config.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'logos'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$texte) {
    $stmt = $pdo->prepare("INSERT INTO textes (section, contenu) VALUES ('logos', 'Découvrez l''évolution des logos Bultaco à travers les décennies.')");
    $stmt->execute();

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'logos'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_texte'])) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'logos'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte mis à jour avec succès !";

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'logos'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_item'])) {
    $titre = $_POST['titre'];
    $datePublication = $_POST['date_publication'];
    $source = $_POST['source'];
    $description = $_POST['description'];
    $lienExterne = $_POST['lien_externe'];

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
        $maxSize = 5 * 1024 * 1024;

        if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imagePath = $uploadPath;
            } else {
                $error = "Erreur lors de l'upload du fichier.";
            }
        } else {
            $error = "Type de fichier non autorisé ou fichier trop volumineux (max 5 Mo).";
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO pressbook (titre, type_contenu, date_publication, source, description, image_path, lien_externe) VALUES (:titre, 'logo', :date_pub, :source, :desc, :img, :lien)");
        $stmt->execute([
            'titre' => $titre,
            'date_pub' => $datePublication,
            'source' => $source,
            'desc' => $description,
            'img' => $imagePath,
            'lien' => $lienExterne
        ]);

        $message = "Logo ajouté avec succès !";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_item'])) {
    $itemId = $_POST['item_id'];

    $stmt = $pdo->prepare("SELECT image_path FROM pressbook WHERE id = :id");
    $stmt->execute(['id' => $itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item && $item['image_path'] && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM pressbook WHERE id = :id");
    $stmt->execute(['id' => $itemId]);

    $message = "Logo supprimé avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_item'])) {
    $itemId = $_POST['item_id'];
    $titre = $_POST['nouveau_titre'];
    $datePublication = $_POST['nouvelle_date'];
    $source = $_POST['nouvelle_source'];
    $description = $_POST['nouvelle_description'];
    $lienExterne = $_POST['nouveau_lien'];

    $stmt = $pdo->prepare("UPDATE pressbook SET titre = :titre, date_publication = :date_pub, source = :source, description = :desc, lien_externe = :lien WHERE id = :id");
    $stmt->execute([
        'titre' => $titre,
        'date_pub' => $datePublication,
        'source' => $source,
        'desc' => $description,
        'lien' => $lienExterne,
        'id' => $itemId
    ]);

    $message = "Logo modifié avec succès !";
}

$stmtLogos = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = 'logo' ORDER BY date_publication DESC");
$stmtLogos->execute();
$logos = $stmtLogos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin Logos - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>

<body>
    <div class="admin-container">
        <h1 class="admin-title">Modification des logos</h1>

        <?php if ($message): ?>
            <div class="message">Succès
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">Échec
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <h2 class="section-title">Modifier le texte de présentation</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="contenu" class="form-label">Texte de présentation :</label>
                    <textarea name="contenu" id="contenu"
                        required><?php echo htmlspecialchars($texte['contenu']); ?></textarea>
                </div>
                <button type="submit" name="update_texte" class="btn">Mettre à jour le texte</button>
            </form>
        </div>

        <div class="admin-section">
            <h2 class="section-title">Ajouter un logo</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titre" class="form-label">Titre du logo :</label>
                    <input type="text" name="titre" id="titre" required placeholder="Ex: Logo original 1958">
                </div>

                <div class="form-group">
                    <label for="date_publication" class="form-label">Période / Année :</label>
                    <input type="text" name="date_publication" id="date_publication"
                        placeholder="Ex: 1958-1965 ou Années 60">
                </div>

                <div class="form-group">
                    <label for="source" class="form-label">Source / Variante :</label>
                    <input type="text" name="source" id="source" placeholder="Ex: Version officielle, Catalogue 1970">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description :</label>
                    <textarea name="description" id="description" style="min-height: 100px;"
                        placeholder="Histoire et contexte du logo"></textarea>
                </div>

                <div class="form-group">
                    <label for="lien_externe" class="form-label">Lien externe (optionnel) :</label>
                    <input type="url" name="lien_externe" id="lien_externe" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">Image du logo (JPG, PNG, SVG, GIF - Max 5 Mo) :</label>
                    <input type="file" name="image" id="image" accept="image/*" required>
                </div>

                <button type="submit" name="ajouter_item" class="btn">Ajouter le logo</button>
            </form>
        </div>

        <div class="admin-section">
            <h2 class="section-title">Logos Bultaco (
                <?php echo count($logos); ?>)
            </h2>
            <?php if (empty($logos)): ?>
                <p class="no-items-admin">Aucun logo</p>
            <?php else: ?>
                <div class="pressbook-admin-grid">
                    <?php foreach ($logos as $item): ?>
                        <?php include '../press/pressbook_item_template.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="links">
            <p>Autres pages à modifier</p>
            <ul>
                <li><a href="../accueil/index_admin.php">Accueil</a></li>
                <li><a href="../restauration/admin_restauration.php">Restauration</a></li>
                <li><a href="../pieces/admin_pieces.php">Pièces détachées</a></li>
                <li><a href="../album/admin_album.php">Album photos</a></li>
                <li><a href="#">Logos</a></li>
                <li><a href="../pilotes/pilotes_admin.php">Pilotes de légende</a></li>
                <li><a href="../press/pressbook_admin.php">Pressbook</a></li>
            </ul>
        </div>
        <div class="admin-links">
            <a href="../../logout.php" class="admin-link">Déconnexion</a>
        </div>
    </div>

    <script>
        function toggleEditItem(itemId) {
            const editForm = document.getElementById('edit-item-' + itemId);
            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
            } else {
                editForm.style.display = 'none';
            }
        }
    </script>
</body>

</html>