<?php
require_once realpath(__DIR__ . '/../../config.php');

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'logos'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$texte) {
    $stmt = $pdo->prepare("INSERT INTO textes (section, contenu) VALUES ('logos', 'Découvrez l''évolution des logos Bultaco à travers les décennies. De l''emblème original aux variations modernes, chaque logo raconte une partie de l''histoire de la marque.')");
    $stmt->execute();

    $stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'logos'");
    $stmt->execute();
    $texte = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmtLogos = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = 'logo' ORDER BY date_publication DESC");
$stmtLogos->execute();
$logos = $stmtLogos->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Logos Bultaco - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_logos.css">
</head>

<body>
    <header class="nav">
        <nav class="header-nav">
            <ul>
                <li><a href=""><img src="../../img/logo_bultaco.png" alt="logo"></a></li>
                <li><a href="../restauration/restauration.php">Restaurations</a></li>
                <li><a href="../pieces/pieces.php">Pièces détachées</a></li>
                <li><a href="../album/album.php">Album photos</a></li>
                <li><a href="#">Logos</a></li>
                <li><a href="../pilotes/pilotes.php">Pilotes de légende</a></li>
                <li><a href="../press/pressbook.php">Pressbook</a></li>
            </ul>
        </nav>
    </header>

    <div class="head">
        <h1>LOGOS</h1>
    </div>

    <div class="container">
        <section class="hero">
            <p><?php echo htmlspecialchars($texte['contenu']); ?></p>

            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Rechercher un logo..." autocomplete="off">
                <div id="searchResults" class="search-results"></div>
            </div>
        </section>

        <section class="pressbook-section">
            <?php if (empty($logos)): ?>
                <p class="no-items">Aucun logo pour le moment</p>
            <?php else: ?>
                <div class="masonry-grid logos-grid" id="masonryGrid">
                    <?php foreach ($logos as $logo): ?>
                        <div class="masonry-item logo-item" data-id="<?php echo $logo['id']; ?>"
                            data-titre="<?php echo strtolower(htmlspecialchars($logo['titre'])); ?>"
                            onclick="openModal(<?php echo $logo['id']; ?>)">
                            <?php if ($logo['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($logo['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($logo['titre']); ?>">
                            <?php else: ?>
                                <div class="no-image-press">🎨</div>
                            <?php endif; ?>

                            <div class="masonry-overlay">
                                <span class="type-badge logo-badge">LOGO</span>
                                <h3><?php echo htmlspecialchars($logo['titre']); ?></h3>
                                <?php if ($logo['date_publication']): ?>
                                    <p class="item-date"><?php echo htmlspecialchars($logo['date_publication']); ?></p>
                                <?php endif; ?>
                                <?php if ($logo['source']): ?>
                                    <p class="item-source"><?php echo htmlspecialchars($logo['source']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>

    <div id="logoModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-body" id="logoModalContent"></div>
        </div>
    </div>

    <script>
        const logos = <?php echo json_encode($logos); ?>;

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query === '') {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            const resultats = logos.filter(logo =>
                logo.titre.toLowerCase().includes(query) ||
                (logo.description && logo.description.toLowerCase().includes(query)) ||
                (logo.source && logo.source.toLowerCase().includes(query)) ||
                (logo.date_publication && logo.date_publication.toLowerCase().includes(query))
            );

            if (resultats.length > 0) {
                searchResults.innerHTML = resultats.map(logo => `
                <div class="search-result-item" data-id="${logo.id}">
                    <div class="search-result-content">
                        <span class="search-type-badge logo-search-badge">🎨 LOGO</span>
                        <strong>${highlightText(logo.titre, query)}</strong>
                        <div class="search-result-meta">
                            <span class="search-date">${logo.date_publication || 'N/A'}</span>
                            ${logo.source ? `<span class="search-source">${logo.source}</span>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
                searchResults.style.display = 'block';

                document.querySelectorAll('.search-result-item').forEach(elem => {
                    elem.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        openModal(parseInt(id));
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

        function openModal(id) {
            const logo = logos.find(l => l.id == id);
            if (!logo) return;

            const content = `
            <div class="press-detail">
                <div class="press-header">
                    <h2>${logo.titre}</h2>
                    <span class="badge-press badge-logo">🎨 Logo Bultaco</span>
                </div>

                ${logo.image_path ?
                    `<img src="${logo.image_path}" alt="${logo.titre}" class="press-image logo-image">` :
                    '<div class="press-image-placeholder">Aucune image disponible</div>'
                }

                <div class="press-info-grid">
                    ${logo.date_publication ?
                    `<div class="press-info-item">
                            <span class="press-info-label">Période :</span>
                            <span class="press-info-value">${logo.date_publication}</span>
                        </div>` : ''
                }
                    ${logo.source ?
                    `<div class="press-info-item">
                            <span class="press-info-label">Source :</span>
                            <span class="press-info-value">${logo.source}</span>
                        </div>` : ''
                }
                </div>

                ${logo.description ?
                    `<div class="press-description">
                        <h3>📝 Description</h3>
                        <p>${logo.description}</p>
                    </div>` : ''
                }

                ${logo.lien_externe ?
                    `<div class="press-link">
                        <a href="${logo.lien_externe}" target="_blank" class="btn-external">🔗 Voir la source originale</a>
                    </div>` : ''
                }
            </div>
        `;

            document.getElementById('logoModalContent').innerHTML = content;
            document.getElementById('logoModal').classList.add('active');
            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('logoModal').classList.remove('active');
            document.getElementById('modalOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>
</body>

</html>