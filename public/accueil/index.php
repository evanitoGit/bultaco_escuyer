<?php
require_once realpath(__DIR__ . '/../../config.php');

$stmt = $pdo->prepare("SELECT contenu FROM textes WHERE section = 'presentation'");
$stmt->execute();
$texte = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Club Bultaco - Escuyer</title>
    <link rel="stylesheet" href="../../css/style_index.css">
    <link rel="icon" type="image/png" href="../../img/logo_rond.png" sizes="50x50">
</head>

<body>
    <header class="nav">
        <nav class="header-nav">
            <ul>
                <li><a href="#"><img src="../../img/logo_bultaco.png" alt="logo"></a></li>
                <li><a href="../restauration/restauration.php">Restaurations</a></li>
                <li><a href="../pieces/pieces.php">Pièces détachées</a></li>
                <li><a href="../album/album.php">Album photos</a></li>
                <li><a href="../logos/logos.php">Logos</a></li>
                <li><a href="../pilotes/pilotes.php">Pilotes de légende</a></li>
                <li><a href="../press/pressbook.php">Pressbook</a></li>
            </ul>
        </nav>
    </header>
    <section class="hero">
        <img src="../../img/image-removebg-preview.png" alt="logo">
    </section>
    <section class="presentation">
        <p class="txt_pres"><?php echo htmlspecialchars($texte['contenu']); ?></p <div class="buttons">
        <ul>
            <li><a href="../restauration/restauration.php">Restaurations</a></li>
            <li><a href="../pieces/pieces.php">Pièces détachées</a></li>
            <li><a href="">Album photos</a></li>
            <li><a href="">Logos</a></li>
            <li><a href="">Pilotes de légende</a></li>
            <li><a href="">Pressbook</a></li>
        </ul>
        </div>
    </section>
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
                    <a href="https://www.instagram.com/bultaco_club_france/" class="reseau-item">
                        <img src="../../img/sociale.png" alt="Instagram">
                        <span>@bultaco_club_france</span>
                    </a>
                    <a href="https://www.facebook.com/BultacoAddict/?locale=fr_FR" class="reseau-item">
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
    <script src="../../js/script.js"></script>
</body>

</html>