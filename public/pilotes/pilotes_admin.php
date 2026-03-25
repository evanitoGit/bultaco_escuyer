<?php
session_start();
require_once realpath(__DIR__ . '/../../config.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pilotes'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$texte) {
    $stmt = $pdo->prepare("INSERT INTO textes (section, contenu) VALUES ('pilotes', 'Découvrez les pilotes de légende qui ont marqué l''histoire de Bultaco.')");
    $stmt->execute();

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pilotes'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_texte'])) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'pilotes'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte de présentation mis à jour avec succès !";

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pilotes'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_pilote'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $dateNaissance = $_POST['date_naissance'] ?? null;
    $dateDeces = $_POST['date_deces'] ?? null;
    $nationalite = $_POST['nationalite'] ?? null;
    $description = $_POST['description'] ?? null;
    $palmares = $_POST['palmares'] ?? null;
    $ordre = $_POST['ordre'] ?? 0;

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
            $uploadDir = 'uploads/pilotes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imagePath = $uploadPath;
            } else {
                $error = "Erreur lors de l'upload de l'image.";
            }
        } else {
            $error = "Type de fichier non autorisé ou fichier trop volumineux (max 5 Mo).";
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO pilotes_emblematiques (nom, prenom, date_naissance, date_deces, nationalite, description, palmares, image_path, ordre) VALUES (:nom, :prenom, :date_naissance, :date_deces, :nationalite, :description, :palmares, :image_path, :ordre)");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $dateNaissance,
            'date_deces' => $dateDeces,
            'nationalite' => $nationalite,
            'description' => $description,
            'palmares' => $palmares,
            'image_path' => $imagePath,
            'ordre' => $ordre
        ]);

        $message = "Pilote ajouté avec succès !";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_pilote'])) {
    $piloteId = $_POST['pilote_id'];

    $stmt = $pdo->prepare("SELECT image_path FROM pilotes_emblematiques WHERE id = :id");
    $stmt->execute(['id' => $piloteId]);
    $pilote = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pilote && $pilote['image_path'] && file_exists($pilote['image_path'])) {
        unlink($pilote['image_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM pilotes_emblematiques WHERE id = :id");
    $stmt->execute(['id' => $piloteId]);

    $message = "Pilote supprimé avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_pilote'])) {
    $piloteId = $_POST['pilote_id'];
    $nom = $_POST['nouveau_nom'];
    $prenom = $_POST['nouveau_prenom'];
    $dateNaissance = $_POST['nouvelle_date_naissance'];
    $dateDeces = $_POST['nouvelle_date_deces'];
    $nationalite = $_POST['nouvelle_nationalite'];
    $description = $_POST['nouvelle_description'];
    $palmares = $_POST['nouveau_palmares'];
    $ordre = $_POST['nouveau_ordre'];

    $stmt = $pdo->prepare("UPDATE pilotes_emblematiques SET nom = :nom, prenom = :prenom, date_naissance = :date_naissance, date_deces = :date_deces, nationalite = :nationalite, description = :description, palmares = :palmares, ordre = :ordre WHERE id = :id");
    $stmt->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'date_naissance' => $dateNaissance,
        'date_deces' => $dateDeces,
        'nationalite' => $nationalite,
        'description' => $description,
        'palmares' => $palmares,
        'ordre' => $ordre,
        'id' => $piloteId
    ]);

    $message = "Informations du pilote modifiées avec succès !";
}

$stmtPilotes = $pdo->prepare("SELECT * FROM pilotes_emblematiques ORDER BY ordre ASC, nom ASC");
$stmtPilotes->execute();
$pilotes = $stmtPilotes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin Pilotes - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>

<body>
    <div class="admin-container">
        <h1 class="admin-title">Modification des pilotes</h1>

        <?php if ($message): ?>
            <div class="message" style="color: green; font-weight: bold; margin-bottom: 15px;">Succès
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error" style="color: red; font-weight: bold; margin-bottom: 15px;">Échec
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <h2 class="section-title">Modifier le texte de présentation</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="contenu" class="form-label">Texte de présentation :</label>
                    <textarea name="contenu" id="contenu" required
                        style="width: 100%; min-height: 80px;"><?php echo htmlspecialchars($texte['contenu']); ?></textarea>
                </div>
                <button type="submit" name="update_texte" class="btn">Mettre à jour le texte</button>
            </form>
        </div>

        <div class="admin-section">
            <h2 class="section-title">Ajouter un pilote</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="prenom" class="form-label">Prénom :</label>
                    <input type="text" name="prenom" id="prenom" required placeholder="Ex: Sammy">
                </div>

                <div class="form-group">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" name="nom" id="nom" required placeholder="Ex: Miller">
                </div>

                <div class="form-group">
                    <label for="nationalite" class="form-label">Nationalité :</label>
                    <input type="text" name="nationalite" id="nationalite" placeholder="Ex: Britannique">
                </div>

                <div class="form-group">
                    <label for="date_naissance" class="form-label">Date de naissance :</label>
                    <input type="text" name="date_naissance" id="date_naissance" placeholder="Ex: 11 novembre 1933">
                </div>

                <div class="form-group">
                    <label for="date_deces" class="form-label">Date de décès (optionnel) :</label>
                    <input type="text" name="date_deces" id="date_deces" placeholder="Ex: Laissez vide si vivant">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description / Biographie :</label>
                    <textarea name="description" id="description" style="width: 100%; min-height: 100px;"></textarea>
                </div>

                <div class="form-group">
                    <label for="palmares" class="form-label">Palmarès :</label>
                    <textarea name="palmares" id="palmares" style="width: 100%; min-height: 100px;"
                        placeholder="Ex: Champion d'Europe de trial 1968..."></textarea>
                </div>

                <div class="form-group">
                    <label for="ordre" class="form-label">Ordre d'affichage :</label>
                    <input type="number" name="ordre" id="ordre" value="0">
                </div>

                <div class="form-group">
                    <label for="image" class="form-label">Photo du pilote (JPG, PNG - Max 5 Mo) :</label>
                    <input type="file" name="image" id="image" accept="image/*">
                </div>

                <button type="submit" name="ajouter_pilote" class="btn">Ajouter le pilote</button>
            </form>
        </div>

        <div class="admin-section">
            <h2 class="section-title">Liste des Pilotes (
                <?php echo count($pilotes); ?>)
            </h2>

            <?php if (empty($pilotes)): ?>
                <p class="no-items-admin">Aucun pilote enregistré.</p>
            <?php else: ?>
                <div class="pilotes-admin-grid">
                    <?php foreach ($pilotes as $pilote): ?>
                        <div class="admin-item" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3>
                                        <?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?>
                                    </h3>
                                    <?php if ($pilote['nationalite']): ?>
                                        <p><strong>Nationalité:</strong>
                                            <?php echo htmlspecialchars($pilote['nationalite']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($pilote['image_path']): ?>
                                    <img src="../../<?php echo htmlspecialchars($pilote['image_path']); ?>"
                                        alt="Photo de <?php echo htmlspecialchars($pilote['nom']); ?>"
                                        style="max-height: 100px; max-width: 100px;">
                                <?php endif; ?>
                            </div>

                            <div class="item-actions" style="margin-top: 15px;">
                                <button type="button" class="btn"
                                    onclick="toggleEditItem(<?php echo $pilote['id']; ?>)">Modifier</button>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce pilote ? Cette action est irréversible.');">
                                    <input type="hidden" name="pilote_id" value="<?php echo $pilote['id']; ?>">
                                    <button type="submit" name="supprimer_pilote" class="btn btn-danger"
                                        style="background-color: red; color: white;">Supprimer</button>
                                </form>
                            </div>

                            <div id="edit-item-<?php echo $pilote['id']; ?>" class="edit-form"
                                style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ccc;">
                                <h4>Modifier
                                    <?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?>
                                </h4>
                                <form method="POST">
                                    <input type="hidden" name="pilote_id" value="<?php echo $pilote['id']; ?>">

                                    <div class="form-group">
                                        <label>Prénom :</label>
                                        <input type="text" name="nouveau_prenom"
                                            value="<?php echo htmlspecialchars($pilote['prenom']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nom :</label>
                                        <input type="text" name="nouveau_nom"
                                            value="<?php echo htmlspecialchars($pilote['nom']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nationalité :</label>
                                        <input type="text" name="nouvelle_nationalite"
                                            value="<?php echo htmlspecialchars($pilote['nationalite'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Date de naissance :</label>
                                        <input type="text" name="nouvelle_date_naissance"
                                            value="<?php echo htmlspecialchars($pilote['date_naissance'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Date de décès :</label>
                                        <input type="text" name="nouvelle_date_deces"
                                            value="<?php echo htmlspecialchars($pilote['date_deces'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Description :</label>
                                        <textarea name="nouvelle_description"
                                            style="width: 100%; min-height: 80px;"><?php echo htmlspecialchars($pilote['description'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Palmarès :</label>
                                        <textarea name="nouveau_palmares"
                                            style="width: 100%; min-height: 80px;"><?php echo htmlspecialchars($pilote['palmares'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Ordre :</label>
                                        <input type="number" name="nouveau_ordre"
                                            value="<?php echo htmlspecialchars($pilote['ordre']); ?>">
                                    </div>
                                    <button type="submit" name="modifier_pilote" class="btn">Enregistrer les
                                        modifications</button>
                                </form>
                            </div>
                        </div>
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
                <li><a href="../logos/logos_admin.php">Logos</a></li>
                <li><a href="#">Pilotes de légende</a></li>
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