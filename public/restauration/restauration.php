<?php
require_once realpath(__DIR__ . '/../../config.php');

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'restauration'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtGauche = $pdo->prepare("SELECT * FROM restauration_photos WHERE colonne = 'gauche' ORDER BY ordre ASC");
$stmtGauche->execute();
$photosGauche = $stmtGauche->fetchAll(PDO::FETCH_ASSOC);

$stmtDroite = $pdo->prepare("SELECT * FROM restauration_photos WHERE colonne = 'droite' ORDER BY ordre ASC");
$stmtDroite->execute();
$photosDroite = $stmtDroite->fetchAll(PDO::FETCH_ASSOC);

$stmtAll = $pdo->prepare("SELECT * FROM restauration_photos ORDER BY nom_modele ASC");
$stmtAll->execute();
$tousLesModeles = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Club Bultaco - Escuyer</title>
    <link rel="stylesheet" href="../../css/style_restau.css">
</head>

<body>
    <header class="nav">
        <nav class="header-nav">
            <ul>
                <li><a href="../accueil/index.php"><img src="../../img/logo_bultaco.png" alt="logo"></a></li>
                <li><a href="#">Restaurations</a></li>
                <li><a href="../pieces/pieces.php">Pièces détachées</a></li>
                <li><a href="../album/album.php">Album photos</a></li>
                <li><a href="../logos/logos.php">Logos</a></li>
                <li><a href="../pilotes/pilotes.php">Pilotes de légende</a></li>
                <li><a href="../press/pressbook.php">Pressbook</a></li>
            </ul>
        </nav>
    </header>
    <div class="head">
        <h1>RESTAURATIONS</h1>
    </div>
    <div class="container">
        <section class="hero">
            <p><?php echo htmlspecialchars($texte['contenu']); ?></p>
            <h2>QUELQUES EXEMPLES</h2>
            <div class="search-container">
                <label for="searchInput"></label><input type="text" id="searchInput" placeholder="Rechercher un modèle"
                    autocomplete="off">
                <div id="searchResults" class="search-results"></div>
            </div>
        </section>
        <section class="photos">
            <div class="photos-container">
                <div class="colonne-gauche">
                    <?php foreach ($photosGauche as $photo): ?>
                        <div class="photo-item" data-id="<?php echo $photo['id']; ?>"
                            data-nom="<?php echo strtolower(htmlspecialchars($photo['nom_modele'])); ?>">
                            <img src="<?php echo htmlspecialchars($photo['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($photo['nom_modele']); ?>">
                            <div class="photo-label"><?php echo htmlspecialchars($photo['nom_modele']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="colonne-droite">
                    <?php foreach ($photosDroite as $photo): ?>
                        <div class="photo-item" data-id="<?php echo $photo['id']; ?>"
                            data-nom="<?php echo strtolower(htmlspecialchars($photo['nom_modele'])); ?>">
                            <img src="<?php echo htmlspecialchars($photo['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($photo['nom_modele']); ?>">
                            <div class="photo-label"><?php echo htmlspecialchars($photo['nom_modele']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
    <section class="coordonnees">
        <div class="coord-header">
            <h2>CLUB BULTACO TRIAL CLASSIC</h2>
            <div class="separator"></div>
        </div>

        <div class="coord-content">
            <div class="coord-bloc adresse">
                <h3>ADRESSE</h3>
                <p>Pierre Escuyer</p>
                <p>53 Rue Roger Salengro</p>
                <p>51100 Reims</p>
                <p>France</p>
            </div>

            <div class="coord-bloc contact">
                <h3>CONTACT</h3>
                <p>06 08 31 15 65</p>
                <p>03 26 09 28 85</p>
                <p>bultaco.trialclassic@orange.fr</p>
            </div>

            <div class="coord-bloc reseaux-bloc">
                <h3>RÉSEAUX SOCIAUX</h3>
                <div class="reseaux-links">
                    <a href="https://www.instagram.com/bultaco_club_france/" class="reseau-item" target="_blank">
                        <img src="../../img/sociale.png" alt="Instagram">
                        <span>@bultaco_club_france</span>
                    </a>
                    <a href="https://www.facebook.com/BultacoAddict/?locale=fr_FR" class="reseau-item" target="_blank">
                        <img src="../../img/facebook.png" alt="Facebook">
                        <span>Club Bultaco Trial Classic</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="coord-footer">
            <p>Passionnés de motos Bultaco depuis plus de 20 ans</p>
        </div>
        <a href="../../login.php"><img src="../../img/logo_rond.png" alt="logorond" class="logorond"></a>
    </section>
    <script>
        const modeles = <?php echo json_encode($tousLesModeles); ?>;

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query === '') {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                resetHighlight();
                return;
            }

            const resultats = modeles.filter(modele =>
                modele.nom_modele.toLowerCase().includes(query)
            );

            if (resultats.length > 0) {
                searchResults.innerHTML = resultats.map(modele => `
        <div class="search-result-item" data-id="${modele.id}">
            <strong>${highlightText(modele.nom_modele, query)}</strong>
        </div>
        `).join('');
                searchResults.style.display = 'block';

                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        scrollToPhoto(id);
                        searchResults.style.display = 'none';
                        searchInput.value = '';
                    });
                });
            } else {
                searchResults.innerHTML = '<div class="no-results">Aucun modèle trouvé</div>';
                searchResults.style.display = 'block';
            }
        });

        function highlightText(text, query) {
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }

        function scrollToPhoto(id) {
            const photoItem = document.querySelector(`.photo-item[data-id="${id}"]`);
            if (photoItem) {
                resetHighlight();

                photoItem.scrollIntoView({ behavior: 'smooth', block: 'center' });

                photoItem.classList.add('highlighted');

                setTimeout(() => {
                    photoItem.classList.remove('highlighted');
                }, 3000);
            }
        }

        function resetHighlight() {
            document.querySelectorAll('.photo-item.highlighted').forEach(item => {
                item.classList.remove('highlighted');
            });
        }

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>
</body>

</html>