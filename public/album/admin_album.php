<?php
session_start();
require_once realpath(__DIR__ . '/../../config.php');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$error = '';

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'emblemes'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_texte'])) {
    $nouveauTexte = $_POST['contenu'];

    $stmt = $pdo->prepare("UPDATE textes SET contenu = :contenu WHERE section = 'emblemes'");
    $stmt->execute(['contenu' => $nouveauTexte]);

    $message = "Texte mis à jour avec succès !";

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'emblemes'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_pilote'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $dateNaissance = $_POST['date_naissance'];
    $dateDeces = $_POST['date_deces'];
    $nationalite = $_POST['nationalite'];
    $description = $_POST['description'];
    $palmares = $_POST['palmares'];

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
            }
        }
    }

    $stmtOrdre = $pdo->prepare("SELECT MAX(ordre) as max_ordre FROM pilotes_emblematiques");
    $stmtOrdre->execute();
    $maxOrdre = $stmtOrdre->fetch(PDO::FETCH_ASSOC);
    $nouvelOrdre = ($maxOrdre['max_ordre'] ?? 0) + 1;

    $stmt = $pdo->prepare("INSERT INTO pilotes_emblematiques (nom, prenom, date_naissance, date_deces, nationalite, description, palmares, image_path, ordre) VALUES (:nom, :prenom, :dn, :dd, :nat, :desc, :palm, :img, :ordre)");
    $stmt->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'dn' => $dateNaissance,
        'dd' => $dateDeces,
        'nat' => $nationalite,
        'desc' => $description,
        'palm' => $palmares,
        'img' => $imagePath,
        'ordre' => $nouvelOrdre
    ]);

    $message = "Pilote ajouté avec succès !";
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

    $stmt = $pdo->prepare("UPDATE pilotes_emblematiques SET nom = :nom, prenom = :prenom, date_naissance = :dn, date_deces = :dd, nationalite = :nat, description = :desc, palmares = :palm WHERE id = :id");
    $stmt->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'dn' => $dateNaissance,
        'dd' => $dateDeces,
        'nat' => $nationalite,
        'desc' => $description,
        'palm' => $palmares,
        'id' => $piloteId
    ]);

    $message = "Pilote modifié avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_modele'])) {
    $nom = $_POST['nom'];
    $anneeDebut = $_POST['annee_debut'];
    $anneeFin = $_POST['annee_fin'];
    $cylindree = $_POST['cylindree'];
    $typeMoto = $_POST['type_moto'];
    $description = $_POST['description'];
    $caracteristiques = $_POST['caracteristiques'];

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
            }
        }
    }

    $stmtOrdre = $pdo->prepare("SELECT MAX(ordre) as max_ordre FROM modeles_emblematiques");
    $stmtOrdre->execute();
    $maxOrdre = $stmtOrdre->fetch(PDO::FETCH_ASSOC);
    $nouvelOrdre = ($maxOrdre['max_ordre'] ?? 0) + 1;

    $stmt = $pdo->prepare("INSERT INTO modeles_emblematiques (nom, annee_debut, annee_fin, cylindree, type_moto, description, caracteristiques, image_path, ordre) VALUES (:nom, :ad, :af, :cyl, :type, :desc, :carac, :img, :ordre)");
    $stmt->execute([
        'nom' => $nom,
        'ad' => $anneeDebut,
        'af' => $anneeFin,
        'cyl' => $cylindree,
        'type' => $typeMoto,
        'desc' => $description,
        'carac' => $caracteristiques,
        'img' => $imagePath,
        'ordre' => $nouvelOrdre
    ]);

    $message = "Modèle ajouté avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_modele'])) {
    $modeleId = $_POST['modele_id'];

    $stmt = $pdo->prepare("SELECT image_path FROM modeles_emblematiques WHERE id = :id");
    $stmt->execute(['id' => $modeleId]);
    $modele = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($modele && $modele['image_path'] && file_exists($modele['image_path'])) {
        unlink($modele['image_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM modeles_emblematiques WHERE id = :id");
    $stmt->execute(['id' => $modeleId]);

    $message = "Modèle supprimé avec succès !";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_modele'])) {
    $modeleId = $_POST['modele_id'];
    $nom = $_POST['nouveau_nom'];
    $anneeDebut = $_POST['nouvelle_annee_debut'];
    $anneeFin = $_POST['nouvelle_annee_fin'];
    $cylindree = $_POST['nouvelle_cylindree'];
    $typeMoto = $_POST['nouveau_type_moto'];
    $description = $_POST['nouvelle_description'];
    $caracteristiques = $_POST['nouvelles_caracteristiques'];

    $stmt = $pdo->prepare("UPDATE modeles_emblematiques SET nom = :nom, annee_debut = :ad, annee_fin = :af, cylindree = :cyl, type_moto = :type, description = :desc, caracteristiques = :carac WHERE id = :id");
    $stmt->execute([
        'nom' => $nom,
        'ad' => $anneeDebut,
        'af' => $anneeFin,
        'cyl' => $cylindree,
        'type' => $typeMoto,
        'desc' => $description,
        'carac' => $caracteristiques,
        'id' => $modeleId
    ]);

    $message = "Modèle modifié avec succès !";
}

$stmtPilotes = $pdo->prepare("SELECT * FROM pilotes_emblematiques ORDER BY ordre ASC");
$stmtPilotes->execute();
$pilotes = $stmtPilotes->fetchAll(PDO::FETCH_ASSOC);

$stmtModeles = $pdo->prepare("SELECT * FROM modeles_emblematiques ORDER BY ordre ASC");
$stmtModeles->execute();
$modeles = $stmtModeles->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Emblèmes - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_admin.css">
</head>
<body>
<div class="admin-container">
    <h1 class="admin-title">Administration - Emblèmes</h1>

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
        <h2 class="section-title">Ajouter un pilote emblématique</h2>
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
                <label for="date_naissance" class="form-label">Date de naissance :</label>
                <input type="text" name="date_naissance" id="date_naissance" placeholder="Ex: 1945 ou 12 mars 1945">
            </div>

            <div class="form-group">
                <label for="date_deces" class="form-label">Date de décès (si applicable) :</label>
                <input type="text" name="date_deces" id="date_deces" placeholder="Ex: 2020 ou Laisser vide">
            </div>

            <div class="form-group">
                <label for="nationalite" class="form-label">Nationalité :</label>
                <input type="text" name="nationalite" id="nationalite" placeholder="Ex: Espagnol">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description :</label>
                <textarea name="description" id="description" style="min-height: 100px;" placeholder="Biographie du pilote"></textarea>
            </div>

            <div class="form-group">
                <label for="palmares" class="form-label">Palmarès :</label>
                <textarea name="palmares" id="palmares" style="min-height: 120px;" placeholder="Champion du monde 1975&#10;Vainqueur Scottish Six Days 1972, 1973&#10;etc."></textarea>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Photo (JPG, PNG, GIF - Max 5 Mo) :</label>
                <input type="file" name="image" id="image" accept="image/*">
            </div>

            <button type="submit" name="ajouter_pilote" class="btn">Ajouter le pilote</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Ajouter un modèle emblématique</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom_modele" class="form-label">Nom du modèle :</label>
                <input type="text" name="nom" id="nom_modele" required placeholder="Ex: Sherpa T">
            </div>

            <div class="form-group">
                <label for="annee_debut" class="form-label">Année de début :</label>
                <input type="text" name="annee_debut" id="annee_debut" placeholder="Ex: 1965">
            </div>

            <div class="form-group">
                <label for="annee_fin" class="form-label">Année de fin :</label>
                <input type="text" name="annee_fin" id="annee_fin" placeholder="Ex: 1980">
            </div>

            <div class="form-group">
                <label for="cylindree" class="form-label">Cylindrée :</label>
                <input type="text" name="cylindree" id="cylindree" placeholder="Ex: 250cc / 350cc">
            </div>

            <div class="form-group">
                <label for="type_moto" class="form-label">Type de moto :</label>
                <input type="text" name="type_moto" id="type_moto" placeholder="Ex: Trial, Motocross, Enduro">
            </div>

            <div class="form-group">
                <label for="description_modele" class="form-label">Description :</label>
                <textarea name="description" id="description_modele" style="min-height: 100px;" placeholder="Histoire et particularités du modèle"></textarea>
            </div>

            <div class="form-group">
                <label for="caracteristiques" class="form-label">Caractéristiques techniques :</label>
                <textarea name="caracteristiques" id="caracteristiques" style="min-height: 120px;" placeholder="Poids: 90 kg&#10;Moteur: 2 temps monocylindre&#10;Puissance: 25 ch&#10;etc."></textarea>
            </div>

            <div class="form-group">
                <label for="image_modele" class="form-label">Photo (JPG, PNG, GIF - Max 5 Mo) :</label>
                <input type="file" name="image" id="image_modele" accept="image/*">
            </div>

            <button type="submit" name="ajouter_modele" class="btn">Ajouter le modèle</button>
        </form>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Gérer les pilotes</h2>
        <?php if (empty($pilotes)): ?>
            <p class="no-items-admin">Aucun pilote pour le moment</p>
        <?php else: ?>
            <div class="emblemes-admin-grid">
                <?php foreach ($pilotes as $pilote): ?>
                    <div class="embleme-admin-card">
                        <?php if ($pilote['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($pilote['image_path']); ?>" alt="<?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?>">
                        <?php else: ?>
                            <div class="no-image-admin-embleme"></div>
                        <?php endif; ?>

                        <div class="embleme-admin-info">
                            <h3><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></h3>
                            <p class="embleme-meta"><?php echo htmlspecialchars($pilote['nationalite'] ?: 'N/A'); ?></p>
                            <p class="embleme-dates"><?php echo htmlspecialchars($pilote['date_naissance']); ?><?php echo $pilote['date_deces'] ? ' - ' . htmlspecialchars($pilote['date_deces']) : ''; ?></p>

                            <div class="embleme-admin-actions">
                                <button onclick="toggleEditPilote(<?php echo $pilote['id']; ?>)" class="btn">Modifier</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce pilote ?');">
                                    <input type="hidden" name="pilote_id" value="<?php echo $pilote['id']; ?>">
                                    <button type="submit" name="supprimer_pilote" class="btn btn-danger">Supprimer</button>
                                </form>
                            </div>

                            <div class="edit-form-embleme" id="edit-pilote-<?php echo $pilote['id']; ?>" style="display: none;">
                                <form method="POST">
                                    <input type="hidden" name="pilote_id" value="<?php echo $pilote['id']; ?>">
                                    <div class="form-group">
                                        <label class="form-label">Prénom :</label>
                                        <input type="text" name="nouveau_prenom" value="<?php echo htmlspecialchars($pilote['prenom']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Nom :</label>
                                        <input type="text" name="nouveau_nom" value="<?php echo htmlspecialchars($pilote['nom']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Date naissance :</label>
                                        <input type="text" name="nouvelle_date_naissance" value="<?php echo htmlspecialchars($pilote['date_naissance']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Date décès :</label>
                                        <input type="text" name="nouvelle_date_deces" value="<?php echo htmlspecialchars($pilote['date_deces']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Nationalité :</label>
                                        <input type="text" name="nouvelle_nationalite" value="<?php echo htmlspecialchars($pilote['nationalite']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Description :</label>
                                        <textarea name="nouvelle_description"><?php echo htmlspecialchars($pilote['description']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Palmarès :</label>
                                        <textarea name="nouveau_palmares"><?php echo htmlspecialchars($pilote['palmares']); ?></textarea>
                                    </div>
                                    <button type="submit" name="modifier_pilote" class="btn">Enregistrer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2 class="section-title">Gérer les modèles</h2>
        <?php if (empty($modeles)): ?>
            <p class="no-items-admin">Aucun modèle pour le moment</p>
        <?php else: ?>
            <div class="emblemes-admin-grid">
                <?php foreach ($modeles as $modele): ?>
                    <div class="embleme-admin-card">
                        <?php if ($modele['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($modele['image_path']); ?>" alt="<?php echo htmlspecialchars($modele['nom']); ?>">
                        <?php else: ?>
                            <div class="no-image-admin-embleme"></div>
                        <?php endif; ?>

                        <div class="embleme-admin-info">
                            <h3><?php echo htmlspecialchars($modele['nom']); ?></h3>
                            <p class="embleme-meta"><?php echo htmlspecialchars($modele['type_moto'] ?: 'N/A'); ?></p>
                            <p class="embleme-dates"><?php echo htmlspecialchars($modele['annee_debut']); ?> - <?php echo htmlspecialchars($modele['annee_fin']); ?></p>

                            <div class="embleme-admin-actions">
                                <button onclick="toggleEditModele(<?php echo $modele['id']; ?>)" class="btn">Modifier</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle ?');">
                                    <input type="hidden" name="modele_id" value="<?php echo $modele['id']; ?>">
                                    <button type="submit" name="supprimer_modele" class="btn btn-danger">Supprimer</button>
                                </form>
                            </div>

                            <div class="edit-form-embleme" id="edit-modele-<?php echo $modele['id']; ?>" style="display: none;">
                                <form method="POST">
                                    <input type="hidden" name="modele_id" value="<?php echo $modele['id']; ?>">
                                    <div class="form-group">
                                        <label class="form-label">Nom :</label>
                                        <input type="text" name="nouveau_nom" value="<?php echo htmlspecialchars($modele['nom']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Année début :</label>
                                        <input type="text" name="nouvelle_annee_debut" value="<?php echo htmlspecialchars($modele['annee_debut']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Année fin :</label>
                                        <input type="text" name="nouvelle_annee_fin" value="<?php echo htmlspecialchars($modele['annee_fin']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Cylindrée :</label>
                                        <input type="text" name="nouvelle_cylindree" value="<?php echo htmlspecialchars($modele['cylindree']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Type :</label>
                                        <input type="text" name="nouveau_type_moto" value="<?php echo htmlspecialchars($modele['type_moto']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Description :</label>
                                        <textarea name="nouvelle_description"><?php echo htmlspecialchars($modele['description']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Caractéristiques :</label>
                                        <textarea name="nouvelles_caracteristiques"><?php echo htmlspecialchars($modele['caracteristiques']); ?></textarea>
                                    </div>
                                    <button type="submit" name="modifier_modele" class="btn">Enregistrer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-links">
        <a href="../../logout.php" class="admin-link">Déconnexion</a>
    </div>
</div>

<script>
    function toggleEditPilote(piloteId) {
        const editForm = document.getElementById('edit-pilote-' + piloteId);
        if (editForm.style.display === 'none' || editForm.style.display === '') {
            editForm.style.display = 'block';
        } else {
            editForm.style.display = 'none';
        }
    }

    function toggleEditModele(modeleId) {
        const editForm = document.getElementById('edit-modele-' + modeleId);
        if (editForm.style.display === 'none' || editForm.style.display === '') {
            editForm.style.display = 'block';
        } else {
            editForm.style.display = 'none';
        }
    }
</script>

<style>

</style>
</body>
</html>