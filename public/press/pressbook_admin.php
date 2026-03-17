<?php
session_start();
require_once realpath(__DIR__ . '/../../config.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pressbook'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_texte'])) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'pressbook'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte mis à jour avec succès !";

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pressbook'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_item'])) {
    $titre = $_POST['titre'];
    $typeContenu = $_POST['type_contenu'];
    $datePublication = $_POST['date_publication'];
    $source = $_POST['source'];
    $description = $_POST['description'];
    $lienExterne = $_POST['lien_externe'];

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

        if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
            $uploadDir = 'uploads/';
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
        $stmt = $pdo->prepare("INSERT INTO pressbook (titre, type_contenu, date_publication, source, description, image_path, lien_externe) VALUES (:titre, :type, :date_pub, :source, :desc, :img, :lien)");
        $stmt->execute([
            'titre' => $titre,
            'type' => $typeContenu,
            'date_pub' => $datePublication,
            'source' => $source,
            'desc' => $description,
            'img' => $imagePath,
            'lien' => $lienExterne
        ]);

        $message = "Élément ajouté avec succès !";
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

    $message = "Élément supprimé avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_item'])) {
    $itemId = $_POST['item_id'];
    $titre = $_POST['nouveau_titre'];
    $typeContenu = $_POST['nouveau_type'];
    $datePublication = $_POST['nouvelle_date'];
    $source = $_POST['nouvelle_source'];
    $description = $_POST['nouvelle_description'];
    $lienExterne = $_POST['nouveau_lien'];

    $stmt = $pdo->prepare("UPDATE pressbook SET titre = :titre, type_contenu = :type, date_publication = :date_pub, source = :source, description = :desc, lien_externe = :lien WHERE id = :id");
    $stmt->execute([
        'titre' => $titre,
        'type' => $typeContenu,
        'date_pub' => $datePublication,
        'source' => $source,
        'desc' => $description,
        'lien' => $lienExterne,
        'id' => $itemId
    ]);

    $message = "Élément modifié avec succès !";
}

$stmtArticles = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = 'article' ORDER BY date_publication DESC");
$stmtArticles->execute();
$articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);

$stmtMagazines = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = 'magazine' ORDER BY date_publication DESC");
$stmtMagazines->execute();
$magazines = $stmtMagazines->fetchAll(PDO::FETCH_ASSOC);

$stmtPhotos = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = 'photo' ORDER BY date_publication DESC");
$stmtPhotos->execute();
$photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

$stmtLogos = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = 'logo' ORDER BY date_publication DESC");
$stmtLogos->execute();
$logos = $stmtLogos->fetchAll(PDO::FETCH_ASSOC);

$stmtIllustrations = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = 'illustration' ORDER BY date_publication DESC");
$stmtIllustrations->execute();
$illustrations = $stmtIllustrations->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Pressbook - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>
<body>
<div class="admin-container">
    <h1 class="admin-title">Administration - Pressbook</h1>

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
        <h2 class="section-title">Ajouter un élément au pressbook</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="type_contenu" class="form-label">Type de contenu :</label>
                <select name="type_contenu" id="type_contenu" required>
                    <option value="">-- Choisir un type --</option>
                    <option value="article">Article de presse</option>
                    <option value="magazine">Couverture de magazine</option>
                    <option value="photo">Photographie</option>
                    <option value="logo">Logo</option>
                    <option value="illustration">Illustration</option>
                </select>
            </div>

            <div class="form-group">
                <label for="titre" class="form-label">Titre :</label>
                <input type="text" name="titre" id="titre" required placeholder="Ex: Champion du monde 1975">
            </div>

            <div class="form-group">
                <label for="date_publication" class="form-label">Date de publication :</label>
                <input type="text" name="date_publication" id="date_publication" placeholder="Ex: 1975 ou Juin 1972">
            </div>

            <div class="form-group">
                <label for="source" class="form-label">Source :</label>
                <input type="text" name="source" id="source" placeholder="Ex: Moto Revue">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description :</label>
                <textarea name="description" id="description" style="min-height: 100px;" placeholder="Description de l'élément"></textarea>
            </div>

            <div class="form-group">
                <label for="lien_externe" class="form-label">Lien externe (optionnel) :</label>
                <input type="url" name="lien_externe" id="lien_externe" placeholder="https://...">
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Image (JPG, PNG, GIF - Max 5 Mo) :</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>

            <button type="submit" name="ajouter_item" class="btn">Ajouter l'élément</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Articles de presse (<?php echo count($articles); ?>)</h2>
        <?php if (empty($articles)): ?>
            <p class="no-items-admin">Aucun article</p>
        <?php else: ?>
            <div class="pressbook-admin-grid">
                <?php foreach ($articles as $item): ?>
                    <?php include 'pressbook_item_template.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Magazines (<?php echo count($magazines); ?>)</h2>
        <?php if (empty($magazines)): ?>
            <p class="no-items-admin">Aucun magazine</p>
        <?php else: ?>
            <div class="pressbook-admin-grid">
                <?php foreach ($magazines as $item): ?>
                    <?php include 'pressbook_item_template.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Photographies (<?php echo count($photos); ?>)</h2>
        <?php if (empty($photos)): ?>
            <p class="no-items-admin">Aucune photo</p>
        <?php else: ?>
            <div class="pressbook-admin-grid">
                <?php foreach ($photos as $item): ?>
                    <?php include 'pressbook_item_template.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Logos (<?php echo count($logos); ?>)</h2>
        <?php if (empty($logos)): ?>
            <p class="no-items-admin">Aucun logo</p>
        <?php else: ?>
            <div class="pressbook-admin-grid">
                <?php foreach ($logos as $item): ?>
                    <?php include 'pressbook_item_template.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Illustrations (<?php echo count($illustrations); ?>)</h2>
        <?php if (empty($illustrations)): ?>
            <p class="no-items-admin">Aucune illustration</p>
        <?php else: ?>
            <div class="pressbook-admin-grid">
                <?php foreach ($illustrations as $item): ?>
                    <?php include 'pressbook_item_template.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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