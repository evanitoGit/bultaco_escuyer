<?php
session_start();
require_once realpath(__DIR__ . '/../../config.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pieces_detachees'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_texte'])) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'pieces_detachees'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte mis à jour avec succès !";

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pieces_detachees'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_categorie'])) {
    $nomCategorie = $_POST['nom_categorie'];

    $stmtOrdre = $pdo->prepare("SELECT MAX(ordre) as max_ordre FROM categories");
    $stmtOrdre->execute();
    $maxOrdre = $stmtOrdre->fetch(PDO::FETCH_ASSOC);
    $nouvelOrdre = ($maxOrdre['max_ordre'] ?? 0) + 1;

    $stmt = $pdo->prepare("INSERT INTO categories (nom, ordre) VALUES (:nom, :ordre)");
    $stmt->execute(['nom' => $nomCategorie, 'ordre' => $nouvelOrdre]);

    $message = "Catégorie ajoutée avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_categorie'])) {
    $categorieId = $_POST['categorie_id'];

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute(['id' => $categorieId]);

    $message = "Catégorie supprimée avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_sous_categorie'])) {
    $nomSousCategorie = $_POST['nom_sous_categorie'];
    $categorieId = $_POST['categorie_id'];

    $stmtOrdre = $pdo->prepare("SELECT MAX(ordre) as max_ordre FROM sous_categories WHERE categorie_id = :cat_id");
    $stmtOrdre->execute(['cat_id' => $categorieId]);
    $maxOrdre = $stmtOrdre->fetch(PDO::FETCH_ASSOC);
    $nouvelOrdre = ($maxOrdre['max_ordre'] ?? 0) + 1;

    $stmt = $pdo->prepare("INSERT INTO sous_categories (nom, categorie_id, ordre) VALUES (:nom, :cat_id, :ordre)");
    $stmt->execute(['nom' => $nomSousCategorie, 'cat_id' => $categorieId, 'ordre' => $nouvelOrdre]);

    $message = "Sous-catégorie ajoutée avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_sous_categorie'])) {
    $sousCategorieId = $_POST['sous_categorie_id'];

    $stmt = $pdo->prepare("DELETE FROM sous_categories WHERE id = :id");
    $stmt->execute(['id' => $sousCategorieId]);

    $message = "Sous-catégorie supprimée avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_piece'])) {
    $nomPiece = $_POST['nom_piece'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $sousCategorieId = $_POST['sous_categorie_id'];

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
                $stmt = $pdo->prepare("INSERT INTO pieces_detachees (nom, description, prix, image_path, sous_categorie_id) VALUES (:nom, :desc, :prix, :path, :sous_cat_id)");
                $stmt->execute([
                    'nom' => $nomPiece,
                    'desc' => $description,
                    'prix' => $prix,
                    'path' => $uploadPath,
                    'sous_cat_id' => $sousCategorieId
                ]);

                $message = "Pièce ajoutée avec succès !";
            } else {
                $error = "Erreur lors de l'upload du fichier.";
            }
        } else {
            $error = "Type de fichier non autorisé ou fichier trop volumineux (max 5 Mo).";
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO pieces_detachees (nom, description, prix, sous_categorie_id) VALUES (:nom, :desc, :prix, :sous_cat_id)");
        $stmt->execute([
            'nom' => $nomPiece,
            'desc' => $description,
            'prix' => $prix,
            'sous_cat_id' => $sousCategorieId
        ]);

        $message = "Pièce ajoutée avec succès (sans image) !";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_piece'])) {
    $pieceId = $_POST['piece_id'];

    $stmt = $pdo->prepare("SELECT image_path FROM pieces_detachees WHERE id = :id");
    $stmt->execute(['id' => $pieceId]);
    $piece = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($piece && $piece['image_path'] && file_exists($piece['image_path'])) {
        unlink($piece['image_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM pieces_detachees WHERE id = :id");
    $stmt->execute(['id' => $pieceId]);

    $message = "Pièce supprimée avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_piece'])) {
    $pieceId = $_POST['piece_id'];
    $nomPiece = $_POST['nouveau_nom'];
    $description = $_POST['nouvelle_description'];
    $prix = $_POST['nouveau_prix'];

    $stmt = $pdo->prepare("UPDATE pieces_detachees SET nom = :nom, description = :desc, prix = :prix WHERE id = :id");
    $stmt->execute(['nom' => $nomPiece, 'desc' => $description, 'prix' => $prix, 'id' => $pieceId]);

    $message = "Pièce modifiée avec succès !";
}

$stmtCategories = $pdo->prepare("SELECT * FROM categories ORDER BY ordre ASC");
$stmtCategories->execute();
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Pièces Détachées - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>
<body>
<div class="admin-container">
    <h1 class="admin-title">Administration - Pièces Détachées</h1>

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
        <h2 class="section-title">📁 Ajouter une catégorie</h2>
        <form method="POST">
            <div class="form-group">
                <label for="nom_categorie" class="form-label">Nom de la catégorie :</label>
                <input type="text" name="nom_categorie" id="nom_categorie" required placeholder="Ex: Moteur">
            </div>
            <button type="submit" name="ajouter_categorie" class="btn">Ajouter la catégorie</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Ajouter une sous-catégorie</h2>
        <form method="POST">
            <div class="form-group">
                <label for="categorie_id_sc" class="form-label">Catégorie parente :</label>
                <select name="categorie_id" id="categorie_id_sc" required>
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nom_sous_categorie" class="form-label">Nom de la sous-catégorie :</label>
                <input type="text" name="nom_sous_categorie" id="nom_sous_categorie" required placeholder="Ex: Cylindre et piston">
            </div>
            <button type="submit" name="ajouter_sous_categorie" class="btn">Ajouter la sous-catégorie</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Ajouter une pièce</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="categorie_id_piece" class="form-label">Catégorie :</label>
                <select id="categorie_id_piece" required onchange="loadSousCategories(this.value)">
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="sous_categorie_id" class="form-label">Sous-catégorie :</label>
                <select name="sous_categorie_id" id="sous_categorie_id" required>
                    <option value="">-- Choisir d'abord une catégorie --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nom_piece" class="form-label">Nom de la pièce :</label>
                <input type="text" name="nom_piece" id="nom_piece" required placeholder="Ex: Piston 50mm Sherpa T">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description :</label>
                <textarea name="description" id="description" style="min-height: 100px;" required placeholder="Description de la pièce"></textarea>
            </div>

            <div class="form-group">
                <label for="prix" class="form-label">Prix (€) :</label>
                <input type="number" step="0.01" name="prix" id="prix" required placeholder="Ex: 89.90">
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Image (optionnel - JPG, PNG, GIF - Max 5 Mo) :</label>
                <input type="file" name="image" id="image" accept="image/*">
            </div>

            <button type="submit" name="ajouter_piece" class="btn">Ajouter la pièce</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Gérer les catégories, sous-catégories et pièces</h2>

        <?php foreach ($categories as $categorie): ?>
            <?php
            $stmtSousCategories = $pdo->prepare("SELECT * FROM sous_categories WHERE categorie_id = :cat_id ORDER BY ordre ASC");
            $stmtSousCategories->execute(['cat_id' => $categorie['id']]);
            $sousCategories = $stmtSousCategories->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="categorie-admin-block">
                <div class="categorie-admin-header">
                    <h3> <?php echo htmlspecialchars($categorie['nom']); ?></h3>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette catégorie supprimera aussi toutes ses sous-catégories et pièces. Continuer ?');">
                        <input type="hidden" name="categorie_id" value="<?php echo $categorie['id']; ?>">
                        <button type="submit" name="supprimer_categorie" class="btn btn-danger">Supprimer catégorie</button>
                    </form>
                </div>

                <?php foreach ($sousCategories as $sousCategorie): ?>
                    <?php
                    $stmtPieces = $pdo->prepare("SELECT * FROM pieces_detachees WHERE sous_categorie_id = :sous_cat_id ORDER BY nom ASC");
                    $stmtPieces->execute(['sous_cat_id' => $sousCategorie['id']]);
                    $pieces = $stmtPieces->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="sous-categorie-admin-block">
                        <div class="sous-categorie-admin-header">
                            <h4>📂 <?php echo htmlspecialchars($sousCategorie['nom']); ?> (<?php echo count($pieces); ?> pièces)</h4>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette sous-catégorie supprimera aussi toutes ses pièces. Continuer ?');">
                                <input type="hidden" name="sous_categorie_id" value="<?php echo $sousCategorie['id']; ?>">
                                <button type="submit" name="supprimer_sous_categorie" class="btn btn-danger">🗑</button>
                            </form>
                        </div>

                        <?php if (!empty($pieces)): ?>
                            <div class="pieces-admin-grid">
                                <?php foreach ($pieces as $piece): ?>
                                    <div class="piece-admin-card">
                                        <?php if ($piece['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($piece['image_path']); ?>" alt="<?php echo htmlspecialchars($piece['nom']); ?>">
                                        <?php else: ?>
                                            <div class="no-image-admin"></div>
                                        <?php endif; ?>

                                        <div class="piece-admin-info">
                                            <h5><?php echo htmlspecialchars($piece['nom']); ?></h5>
                                            <p class="piece-admin-desc"><?php echo htmlspecialchars($piece['description']); ?></p>
                                            <p class="piece-admin-prix"><?php echo number_format($piece['prix'], 2, ',', ' '); ?> €</p>

                                            <div class="piece-admin-actions">
                                                <button onclick="toggleEditPiece(<?php echo $piece['id']; ?>)" class="btn">Modifier</button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette pièce ?');">
                                                    <input type="hidden" name="piece_id" value="<?php echo $piece['id']; ?>">
                                                    <button type="submit" name="supprimer_piece" class="btn btn-danger">Supprimer</button>
                                                </form>
                                            </div>

                                            <div class="edit-form-piece" id="edit-piece-<?php echo $piece['id']; ?>" style="display: none;">
                                                <form method="POST">
                                                    <input type="hidden" name="piece_id" value="<?php echo $piece['id']; ?>">
                                                    <div class="form-group">
                                                        <label class="form-label">Nom :</label>
                                                        <input type="text" name="nouveau_nom" value="<?php echo htmlspecialchars($piece['nom']); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Description :</label>
                                                        <textarea name="nouvelle_description" required><?php echo htmlspecialchars($piece['description']); ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Prix (€) :</label>
                                                        <input type="number" step="0.01" name="nouveau_prix" value="<?php echo $piece['prix']; ?>" required>
                                                    </div>
                                                    <button type="submit" name="modifier_piece" class="btn">Enregistrer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-pieces-admin">Aucune pièce dans cette sous-catégorie</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-links">
        <a href="../../logout.php" class="admin-link">Déconnexion</a>
    </div>
</div>

<script>
    const categoriesData = <?php
        $catData = [];
        foreach ($categories as $cat) {
            $stmtSC = $pdo->prepare("SELECT * FROM sous_categories WHERE categorie_id = :cat_id ORDER BY ordre ASC");
            $stmtSC->execute(['cat_id' => $cat['id']]);
            $catData[$cat['id']] = $stmtSC->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($catData);
        ?>;

    function loadSousCategories(categorieId) {
        const sousCategorieSelect = document.getElementById('sous_categorie_id');
        sousCategorieSelect.innerHTML = '<option value="">-- Choisir une sous-catégorie --</option>';

        if (categorieId && categoriesData[categorieId]) {
            categoriesData[categorieId].forEach(sc => {
                const option = document.createElement('option');
                option.value = sc.id;
                option.textContent = sc.nom;
                sousCategorieSelect.appendChild(option);
            });
        }
    }

    function toggleEditPiece(pieceId) {
        const editForm = document.getElementById('edit-piece-' + pieceId);
        if (editForm.style.display === 'none' || editForm.style.display === '') {
            editForm.style.display = 'block';
        } else {
            editForm.style.display = 'none';
        }
    }
</script>
</body>
</html>