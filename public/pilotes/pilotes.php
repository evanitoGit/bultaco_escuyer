<?php
require_once realpath(__DIR__ . '/../../config.php');

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pilotes_legende'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$texte) {
    $stmt = $pdo->prepare("INSERT INTO textes (section, contenu) VALUES ('pilotes_legende', 'Découvrez les pilotes légendaires qui ont marqué l''histoire de Bultaco et écrit les plus belles pages du trial et du motocross.')");
    $stmt->execute();

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pilotes_legende'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmtPilotes = $pdo->prepare("SELECT * FROM pilotes_emblematiques ORDER BY ordre ASC");
$stmtPilotes->execute();
$pilotes = $stmtPilotes->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Pilotes de Légende - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_pilotes.css">
</head>

<body>
    <header class="nav">
        <nav class="header-nav">
            <ul>
                <li><a href="../accueil/index.php"><img src="../../img/logo_bultaco.png" alt="logo"></a></li>
                <li><a href="../restauration/restauration.php">Restaurations</a></li>
                <li><a href="../pieces/pieces.php">Pièces détachées</a></li>
                <li><a href="../album/album.php">Album photos</a></li>
                <li><a href="../logos/logos.php">Logos</a></li>
                <li><a href="#">Pilotes de légende</a></li>
                <li><a href="../press/pressbook.php">Pressbook</a></li>
            </ul>
        </nav>
    </header>
    <div class="head">
        <h2>PILOTES DE LÉGENDE</h1>
    </div>

    <div class="container">
        <section class="hero">
            <p><?php echo htmlspecialchars($texte['contenu']); ?></p>

            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Rechercher un pilote" autocomplete="off">
                <div id="searchResults" class="search-results"></div>
            </div>
        </section>

        <section class="emblemes-section">
            <div class="album-grid">
                <?php if (empty($pilotes)): ?>
                    <p class="no-items">Aucun pilote pour le moment</p>
                <?php else: ?>
                    <?php foreach ($pilotes as $pilote): ?>
                        <div class="album-card pilote-item" data-id="<?php echo $pilote['id']; ?>"
                            data-nom="<?php echo strtolower(htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom'])); ?>"
                            onclick="openPiloteModal(<?php echo $pilote['id']; ?>)">
                            <?php if ($pilote['image_path']): ?>
                                <div class="album-photo"
                                    style="background-image: url('<?php echo htmlspecialchars($pilote['image_path']); ?>')"></div>
                            <?php else: ?>
                                <div class="album-photo no-photo"></div>
                            <?php endif; ?>
                            <div class="album-info">
                                <h3><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></h3>
                                <p class="album-type"><?php echo htmlspecialchars($pilote['nationalite']); ?></p>
                                <?php if ($pilote['date_naissance'] || $pilote['date_deces']): ?>
                                    <p class="album-dates"><?php echo htmlspecialchars($pilote['date_naissance'] ?: '?'); ?> -
                                        <?php echo htmlspecialchars($pilote['date_deces'] ?: ''); ?>
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

    <div id="piloteModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-body" id="piloteModalContent"></div>
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
        const pilotes = <?php echo json_encode($pilotes); ?>;

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query === '') {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            const resultats = pilotes.filter(item =>
                (item.prenom + ' ' + item.nom).toLowerCase().includes(query) ||
                (item.nationalite && item.nationalite.toLowerCase().includes(query)) ||
                (item.description && item.description.toLowerCase().includes(query))
            );

            if (resultats.length > 0) {
                searchResults.innerHTML = resultats.map(item => `
                    <div class="search-result-item" data-id="${item.id}">
                        <div class="search-result-content">
                            <strong>${highlightText(item.prenom + ' ' + item.nom, query)}</strong>
                            <div class="search-result-meta">
                                <span class="search-category">${item.nationalite || 'N/A'}</span>
                                <span class="search-dates">${item.date_naissance || '?'} - ${item.date_deces || ''}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
                searchResults.style.display = 'block';

                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const id = parseInt(this.getAttribute('data-id'));
                        openPiloteModal(id);
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

        function openPiloteModal(id) {
            const pilote = pilotes.find(p => p.id == id);
            if (!pilote) return;

            const content = `
                <div class="carte-identite">
                    <div class="carte-header">
                        <h2>${pilote.prenom} ${pilote.nom}</h2>
                        <span class="badge-pilote">PILOTE</span>
                    </div>

                    ${pilote.image_path ?
                    `<img src="${pilote.image_path}" alt="${pilote.prenom} ${pilote.nom}" class="carte-photo">` :
                    '<div class="carte-photo-placeholder"></div>'
                }

                    <div class="carte-section">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Naissance :</span>
                                <span class="info-value">${pilote.date_naissance || 'N/A'}</span>
                            </div>
                            ${pilote.date_deces ?
                    `<div class="info-item">
                                    <span class="info-label">Décès :</span>
                                    <span class="info-value">${pilote.date_deces}</span>
                                </div>` : ''
                }
                            <div class="info-item">
                                <span class="info-label">Nationalité :</span>
                                <span class="info-value">${pilote.nationalite || 'N/A'}</span>
                            </div>
                        </div>
                    </div>

                    ${pilote.description ?
                    `<div class="carte-section">
                            <p class="description-text">${pilote.description}</p>
                        </div>` : ''
                }

                    ${pilote.palmares ?
                    `<div class="carte-section">
                            <div class="palmares-text">${pilote.palmares.replace(/\n/g, '<br>')}</div>
                        </div>` : ''
                }
                </div>
            `;

            document.getElementById('piloteModalContent').innerHTML = content;
            document.getElementById('piloteModal').classList.add('active');
            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('piloteModal').classList.remove('active');
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