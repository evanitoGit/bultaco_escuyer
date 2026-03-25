<?php
require_once realpath(__DIR__ . '/../../config.php');

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'album_photos'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$texte) {
    $stmt = $pdo->prepare("INSERT INTO textes (section, contenu) VALUES ('album_photos', 'Découvrez notre collection de motos Bultaco emblématiques à travers des photos et fiches techniques détaillées.')");
    $stmt->execute();

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'album_photos'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmtModeles = $pdo->prepare("SELECT * FROM modeles_emblematiques ORDER BY ordre ASC");
$stmtModeles->execute();
$modeles = $stmtModeles->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Album Photos - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_album.css">
</head>

<body>
    <header class="nav">
        <nav class="header-nav">
            <ul>
                <li><a href="../accueil/index.php"><img src="../../img/logo_bultaco.png" alt="logo"></a></li>
                <li><a href="../restauration/restauration.php">Restaurations</a></li>
                <li><a href="../pieces/pieces.php">Pièces détachées</a></li>
                <li><a href="#">Album photos</a></li>
                <li><a href="../logos/logos.php">Logos</a></li>
                <li><a href="../pilotes/pilotes.php">Pilotes de légende</a></li>
                <li><a href="../press/pressbook.php">Pressbook</a></li>
            </ul>
        </nav>
    </header>
    <div class="head">
        <h1>ALBUM PHOTOS</h1>
    </div>

    <div class="container">
        <section class="hero">
            <p><?php echo htmlspecialchars($texte['contenu']); ?></p>

            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Rechercher un modèle..." autocomplete="off">
                <div id="searchResults" class="search-results"></div>
            </div>
        </section>

        <section class="emblemes-section">
            <div class="album-grid">
                <?php if (empty($modeles)): ?>
                    <p class="no-items">Aucun modèle pour le moment</p>
                <?php else: ?>
                    <?php foreach ($modeles as $modele): ?>
                        <div class="album-card modele-item" data-id="<?php echo $modele['id']; ?>"
                            data-nom="<?php echo strtolower(htmlspecialchars($modele['nom'])); ?>"
                            onclick="openModeleModal(<?php echo $modele['id']; ?>)">
                            <?php if ($modele['image_path']): ?>
                                <div class="album-photo"
                                    style="background-image: url('<?php echo htmlspecialchars($modele['image_path']); ?>')"></div>
                            <?php else: ?>
                                <div class="album-photo no-photo">🏍️</div>
                            <?php endif; ?>
                            <div class="album-info">
                                <h3><?php echo htmlspecialchars($modele['nom']); ?></h3>
                                <p class="album-type"><?php echo htmlspecialchars($modele['type_moto']); ?></p>
                                <?php if ($modele['annee_debut'] || $modele['annee_fin']): ?>
                                    <p class="album-dates"><?php echo htmlspecialchars($modele['annee_debut'] ?: '?'); ?> -
                                        <?php echo htmlspecialchars($modele['annee_fin'] ?: '?'); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>

    <div id="modeleModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-body" id="modeleModalContent"></div>
        </div>
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
        const modeles = <?php echo json_encode($modeles); ?>;

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query === '') {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            const resultats = modeles.filter(item =>
                item.nom.toLowerCase().includes(query) ||
                (item.type_moto && item.type_moto.toLowerCase().includes(query)) ||
                (item.description && item.description.toLowerCase().includes(query))
            );

            if (resultats.length > 0) {
                searchResults.innerHTML = resultats.map(item => `
                    <div class="search-result-item" data-id="${item.id}">
                        <div class="search-result-content">
                        
                            <strong>${highlightText(item.nom, query)}</strong>
                            <div class="search-result-meta">
                                <span class="search-category">${item.type_moto || 'N/A'}</span>
                                <span class="search-dates">${item.annee_debut || '?'} - ${item.annee_fin || '?'}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
                searchResults.style.display = 'block';

                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const id = parseInt(this.getAttribute('data-id'));
                        openModeleModal(id);
                        searchResults.style.display = 'none';
                        searchInput.value = '';
                    });
                });
            } else {
                searchResults.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
                searchResults.style.display = 'block';
            }
        });

        function highlightText(text, query) {
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        function openModeleModal(id) {
            const modele = modeles.find(m => m.id == id);
            if (!modele) return;

            const content = `
                <div class="carte-identite">
                    <div class="carte-header">
                        <h2>${modele.nom}</h2>
                        <span class="badge-modele">MODÈLE</span>
                    </div>

                    ${modele.image_path ?
                    `<img src="${modele.image_path}" alt="${modele.nom}" class="carte-photo">` :
                    '<div class="carte-photo-placeholder">🏍️</div>'
                }

                    <div class="carte-section">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Période :</span>
                                <span class="info-value">${modele.annee_debut || '?'} - ${modele.annee_fin || '?'}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Cylindrée :</span>
                                <span class="info-value">${modele.cylindree || 'N/A'}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Type :</span>
                                <span class="info-value">${modele.type_moto || 'N/A'}</span>
                            </div>
                        </div>
                    </div>

                    ${modele.description ?
                    `<div class="carte-section">
                            <p class="description-text">${modele.description}</p>
                        </div>` : ''
                }

                    ${modele.caracteristiques ?
                    `<div class="carte-section">
                            <div class="caracteristiques-text">${modele.caracteristiques.replace(/\n/g, '<br>')}</div>
                        </div>` : ''
                }
                </div>
            `;

            document.getElementById('modeleModalContent').innerHTML = content;
            document.getElementById('modeleModal').classList.add('active');
            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modeleModal').classList.remove('active');
            document.getElementById('modalOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>

</html>