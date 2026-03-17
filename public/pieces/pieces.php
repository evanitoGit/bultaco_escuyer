<?php
require_once realpath(__DIR__ . '/../../config.php');

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'pieces_detachees'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtCategories = $pdo->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM pieces_detachees p 
            INNER JOIN sous_categories sc ON p.sous_categorie_id = sc.id 
            WHERE sc.categorie_id = c.id) as nb_pieces
    FROM categories c 
    ORDER BY c.ordre ASC
");
$stmtCategories->execute();
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

$stmtAllPieces = $pdo->prepare("
    SELECT p.*, sc.nom as sous_categorie, c.nom as categorie
    FROM pieces_detachees p
    INNER JOIN sous_categories sc ON p.sous_categorie_id = sc.id
    INNER JOIN categories c ON sc.categorie_id = c.id
    ORDER BY p.nom ASC
");
$stmtAllPieces->execute();
$toutesLesPieces = $stmtAllPieces->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Club Bultaco - Pièces Détachées</title>
    <link rel="stylesheet" href="../../css/style_pieces.css">
</head>

<body>
    <header class="nav">
        <nav class="header-nav">
            <ul>
                <li><a href="../accueil/index.php"><img src="../../img/logo_bultaco.png" alt="logo"></a></li>
                <li><a href="../restauration/restauration.php">Restaurations</a></li>
                <li><a href="#">Pièces détachées</a></li>
                <li><a href="../album/album.php">Album photos</a></li>
                <li><a href="../logos/logos.php">Logos</a></li>
                <li><a href="../pilotes/pilotes.php">Pilotes de légende</a></li>
                <li><a href="../press/pressbook.php">Pressbook</a></li>
            </ul>
        </nav>
    </header>
    <div class="head">
        <h1>PIÈCES DÉTACHÉES</h1>
    </div>
    <div class="container">
        <section class="hero">
            <p><?php echo htmlspecialchars($texte['contenu']); ?></p>
            <h2>CATALOGUE DES PIÈCES</h2>
            <div class="search-container">
                <label for="searchInput"></label><input type="text" id="searchInput" placeholder="Rechercher une pièce"
                    autocomplete="off">
                <div id="searchResults" class="search-results"></div>
            </div>
        </section>
        <section class="pieces-section">
            <div class="categories-container">
                <?php foreach ($categories as $categorie): ?>
                    <?php
                    $stmtSousCategories = $pdo->prepare("
                        SELECT sc.*, COUNT(p.id) as nb_pieces
                        FROM sous_categories sc
                        LEFT JOIN pieces_detachees p ON sc.id = p.sous_categorie_id
                        WHERE sc.categorie_id = :cat_id
                        GROUP BY sc.id
                        ORDER BY sc.ordre ASC
                    ");
                    $stmtSousCategories->execute(['cat_id' => $categorie['id']]);
                    $sousCategories = $stmtSousCategories->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="categorie-block">
                        <div class="categorie-header" onclick="toggleCategorie(<?php echo $categorie['id']; ?>)">
                            <h3>
                                <span class="arrow" id="arrow-cat-<?php echo $categorie['id']; ?>">▶</span>
                                <?php echo htmlspecialchars($categorie['nom']); ?>
                                <span class="badge"><?php echo $categorie['nb_pieces']; ?> pièce(s)</span>
                            </h3>
                        </div>

                        <div class="categorie-content" id="cat-<?php echo $categorie['id']; ?>" style="display: none;">
                            <?php foreach ($sousCategories as $sousCategorie): ?>
                                <?php
                                $stmtPieces = $pdo->prepare("
                                    SELECT * FROM pieces_detachees 
                                    WHERE sous_categorie_id = :sous_cat_id 
                                    ORDER BY nom ASC
                                ");
                                $stmtPieces->execute(['sous_cat_id' => $sousCategorie['id']]);
                                $pieces = $stmtPieces->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                                <div class="sous-categorie-block">
                                    <div class="sous-categorie-header"
                                        onclick="toggleSousCategorie(<?php echo $sousCategorie['id']; ?>)">
                                        <h4>
                                            <span class="arrow-small"
                                                id="arrow-sous-<?php echo $sousCategorie['id']; ?>">▶</span>
                                            <?php echo htmlspecialchars($sousCategorie['nom']); ?>
                                            <span class="badge-small"><?php echo $sousCategorie['nb_pieces']; ?></span>
                                        </h4>
                                    </div>

                                    <div class="sous-categorie-content" id="sous-cat-<?php echo $sousCategorie['id']; ?>"
                                        style="display: none;">
                                        <?php if (empty($pieces)): ?>
                                            <p class="no-pieces">Aucune pièce disponible pour le moment</p>
                                        <?php else: ?>
                                            <div class="pieces-grid">
                                                <?php foreach ($pieces as $piece): ?>
                                                    <div class="piece-card" data-piece-id="<?php echo $piece['id']; ?>"
                                                        data-piece-nom="<?php echo strtolower(htmlspecialchars($piece['nom'])); ?>">
                                                        <?php if ($piece['image_path']): ?>
                                                            <img src="<?php echo htmlspecialchars($piece['image_path']); ?>"
                                                                alt="<?php echo htmlspecialchars($piece['nom']); ?>">
                                                        <?php else: ?>
                                                            <div class="no-image">📦</div>
                                                        <?php endif; ?>

                                                        <div class="piece-info">
                                                            <h5><?php echo htmlspecialchars($piece['nom']); ?></h5>
                                                            <p class="piece-description">
                                                                <?php echo htmlspecialchars($piece['description']); ?>
                                                            </p>
                                                            <div class="piece-footer">
                                                                <span
                                                                    class="piece-prix"><?php echo number_format($piece['prix'], 2, ',', ' '); ?>
                                                                    €</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <script>
        const pieces = <?php echo json_encode($toutesLesPieces); ?>;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query === '') {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            const resultats = pieces.filter(piece =>
                piece.nom.toLowerCase().includes(query) ||
                piece.description.toLowerCase().includes(query) ||
                piece.categorie.toLowerCase().includes(query) ||
                piece.sous_categorie.toLowerCase().includes(query)
            );

            if (resultats.length > 0) {
                searchResults.innerHTML = resultats.map(piece => `
                    <div class="search-result-item" data-piece-id="${piece.id}">
                        <div class="search-result-content">
                            <strong>${highlightText(piece.nom, query)}</strong>
                            <div class="search-result-meta">
                                <span class="search-category">${piece.categorie} > ${piece.sous_categorie}</span>
                                <span class="search-price">${parseFloat(piece.prix).toFixed(2).replace('.', ',')} €</span>
                            </div>
                        </div>
                    </div>
                `).join('');
                searchResults.style.display = 'block';

                document.querySelectorAll('.search-result-item').forEach(item => {
                    item.addEventListener('click', function () {
                        const id = this.getAttribute('data-piece-id');
                        scrollToPiece(id);
                        searchResults.style.display = 'none';
                        searchInput.value = '';
                    });
                });
            } else {
                searchResults.innerHTML = '<div class="no-results">Aucune pièce trouvée</div>';
                searchResults.style.display = 'block';
            }
        });

        function highlightText(text, query) {
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }

        function scrollToPiece(id) {
            const pieceCard = document.querySelector(`.piece-card[data-piece-id="${id}"]`);
            if (pieceCard) {
                const sousCategorie = pieceCard.closest('.sous-categorie-content');
                const categorie = pieceCard.closest('.categorie-content');

                if (sousCategorie) {
                    sousCategorie.style.display = 'block';
                    const sousCatId = sousCategorie.id.replace('sous-cat-', '');
                    const arrow = document.getElementById('arrow-sous-' + sousCatId);
                    if (arrow) arrow.textContent = '▼';
                }

                if (categorie) {
                    categorie.style.display = 'block';
                    const catId = categorie.id.replace('cat-', '');
                    const arrow = document.getElementById('arrow-cat-' + catId);
                    if (arrow) arrow.textContent = '▼';
                }

                setTimeout(() => {
                    pieceCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    pieceCard.classList.add('highlighted');

                    setTimeout(() => {
                        pieceCard.classList.remove('highlighted');
                    }, 3000);
                }, 300);
            }
        }

        function toggleCategorie(id) {
            const content = document.getElementById('cat-' + id);
            const arrow = document.getElementById('arrow-cat-' + id);

            if (content.style.display === 'none') {
                content.style.display = 'block';
                arrow.textContent = '▼';
            } else {
                content.style.display = 'none';
                arrow.textContent = '▶';
            }
        }

        function toggleSousCategorie(id) {
            const content = document.getElementById('sous-cat-' + id);
            const arrow = document.getElementById('arrow-sous-' + id);

            if (content.style.display === 'none') {
                content.style.display = 'block';
                arrow.textContent = '▼';
            } else {
                content.style.display = 'none';
                arrow.textContent = '▶';
            }
        }

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    </script>
</body>

</html>