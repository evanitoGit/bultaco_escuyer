<?php
require_once realpath(__DIR__ . '/../../config.php');

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pressbook'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

$typeFiltre = isset($_GET['type']) ? $_GET['type'] : 'tout';

if ($typeFiltre === 'tout') {
    $stmtPressbook = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu != 'logo' ORDER BY date_publication DESC");
    $stmtPressbook->execute();
} else {
    $stmtPressbook = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu = :type ORDER BY date_publication DESC");
    $stmtPressbook->execute(['type' => $typeFiltre]);
}

$items = $stmtPressbook->fetchAll(PDO::FETCH_ASSOC);

$stmtAll = $pdo->prepare("SELECT * FROM pressbook WHERE type_contenu != 'logo' ORDER BY date_publication DESC");
$stmtAll->execute();
$tousLesItems = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Pressbook - Club Bultaco</title>
    <link rel="stylesheet" href="../../css/style_pressbook.css">
</head>

<body>
    <header class="nav">
        <nav class="header-nav">
            <ul>
                <li><a href=""><img src="../../img/logo_bultaco.png" alt="logo"></a></li>
                <li><a href="../restauration/restauration.php">Restaurations</a></li>
                <li><a href="../pieces/pieces.php">Pièces détachées</a></li>
                <li><a href="../album/album.php">Album photos</a></li>
                <li><a href="../logos/logos.php">Logos</a></li>
                <li><a href="../pilotes/pilotes.php">Pilotes de légende</a></li>
                <li><a href="#">Pressbook</a></li>
            </ul>
        </nav>
    </header>

    <div class="head">
        <h1>PRESSBOOK</h1>
    </div>

    <div class="container">
        <section class="hero">
            <p><?php echo htmlspecialchars($texte['contenu']); ?></p>

            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Rechercher..." autocomplete="off">
                <div id="searchResults" class="search-results"></div>
            </div>
        </section>

        <section class="pressbook-section">
            <?php if (empty($items)): ?>
                <p class="no-items">Aucun élément pour cette catégorie</p>
            <?php else: ?>
                <div class="masonry-grid" id="masonryGrid">
                    <?php foreach ($items as $item): ?>
                        <div class="masonry-item" data-type="<?php echo htmlspecialchars($item['type_contenu']); ?>"
                            data-id="<?php echo $item['id']; ?>"
                            data-titre="<?php echo strtolower(htmlspecialchars($item['titre'])); ?>"
                            onclick="openModal(<?php echo $item['id']; ?>)">
                            <?php if ($item['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($item['titre']); ?>">
                            <?php else: ?>
                                <div class="no-image-press">
                                    <?php
                                    $icons = [
                                        'article' => '📰',
                                        'magazine' => '📔',
                                        'photo' => '📸',
                                        'illustration' => '✏️'
                                    ];
                                    echo $icons[$item['type_contenu']];
                                    ?>
                                </div>
                            <?php endif; ?>

                            <div class="masonry-overlay">
                                <span class="type-badge"><?php echo strtoupper($item['type_contenu']); ?></span>
                                <h3><?php echo htmlspecialchars($item['titre']); ?></h3>
                                <p class="item-date"><?php echo htmlspecialchars($item['date_publication']); ?></p>
                                <?php if ($item['source']): ?>
                                    <p class="item-source"><?php echo htmlspecialchars($item['source']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>

    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div class="modal-body" id="itemModalContent"></div>
        </div>
    </div>

    <script>
        const items = <?php echo json_encode($tousLesItems); ?>;

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query === '') {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            const resultats = items.filter(item =>
                item.titre.toLowerCase().includes(query) ||
                (item.description && item.description.toLowerCase().includes(query)) ||
                (item.source && item.source.toLowerCase().includes(query))
            );

            if (resultats.length > 0) {
                searchResults.innerHTML = resultats.map(item => `
                    <div class="search-result-item" data-id="${item.id}">
                        <div class="search-result-content">
                            <span class="search-type-badge">${item.type_contenu.toUpperCase()}</span>
                            <strong>${highlightText(item.titre, query)}</strong>
                            <div class="search-result-meta">
                                <span class="search-date">${item.date_publication || 'N/A'}</span>
                                ${item.source ? `<span class="search-source">${item.source}</span>` : ''}
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
            const item = items.find(i => i.id == id);
            if (!item) return;

            const typeLabels = {
                'article': '📰 Article de presse',
                'magazine': '📔 Magazine',
                'photo': '📸 Photographie',
                'illustration': '✏️ Illustration'
            };

            const content = `
                <div class="press-detail">
                    <div class="press-header">
                        <h2>${item.titre}</h2>
                        <span class="badge-press">${typeLabels[item.type_contenu]}</span>
                    </div>

                    ${item.image_path ?
                    `<img src="${item.image_path}" alt="${item.titre}" class="press-image">` :
                    '<div class="press-image-placeholder">Aucune image disponible</div>'
                }

                    <div class="press-info-grid">
                        ${item.date_publication ?
                    `<div class="press-info-item">
                                <span class="press-info-label">Date :</span>
                                <span class="press-info-value">${item.date_publication}</span>
                            </div>` : ''
                }
                        ${item.source ?
                    `<div class="press-info-item">
                                <span class="press-info-label">Source :</span>
                                <span class="press-info-value">${item.source}</span>
                            </div>` : ''
                }
                    </div>

                    ${item.description ?
                    `<div class="press-description">
                            <h3>📝 Description</h3>
                            <p>${item.description}</p>
                        </div>` : ''
                }

                    ${item.lien_externe ?
                    `<div class="press-link">
                            <a href="${item.lien_externe}" target="_blank" class="btn-external">🔗 Voir la source originale</a>
                        </div>` : ''
                }
                </div>
            `;

            document.getElementById('itemModalContent').innerHTML = content;
            document.getElementById('itemModal').classList.add('active');
            document.getElementById('modalOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('itemModal').classList.remove('active');
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