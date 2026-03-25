<?php

class WebPage
{
    private $title;
    private $cssFile;
    private $headerActive;
    private $showFooter;
    private $pdo;
    private $head;
    private $body;

    public function __construct($title, $cssFile, $headerActive = '', $showFooter = true)
    {
        $this->title = $title;
        $this->cssFile = $cssFile;
        $this->headerActive = $headerActive;
        $this->showFooter = $showFooter;
        $this->head = '';
        $this->body = '';

        require_once realpath(__DIR__ . '/../config.php');
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getCssFile()
    {
        return $this->cssFile;
    }

    public function getHeaderActive()
    {
        return $this->headerActive;
    }

    public function shouldShowFooter()
    {
        return $this->showFooter;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function appendToHead($content)
    {
        $this->head .= $content;
    }

    public function appendToBody($content)
    {
        $this->body .= $content;
    }

    public function getTexteSection($section)
    {
        $stmt = $this->pdo->prepare("SELECT contenu FROM textes WHERE section = :section");
        $stmt->execute(['section' => $section]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['contenu'] : '';
    }

    private function generateHead()
    {
        $html = '<!doctype html>' . PHP_EOL;
        $html .= '<html lang="fr">' . PHP_EOL;
        $html .= '<head>' . PHP_EOL;
        $html .= '    <meta charset="UTF-8">' . PHP_EOL;
        $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
        $html .= '    <title>' . htmlspecialchars($this->title) . '</title>' . PHP_EOL;
        $html .= '    <link rel="stylesheet" href="../../css/' . htmlspecialchars($this->cssFile) . '">' . PHP_EOL;
        $html .= $this->head;
        $html .= '</head>' . PHP_EOL;
        $html .= '<body>' . PHP_EOL;

        return $html;
    }

    private function generateHeader()
    {
        $menuItems = [
            'accueil' => ['url' => '../../public/accueil/index.php', 'label' => 'Accueil'],
            'restauration' => ['url' => '../../public/restauration/restauration.php', 'label' => 'Restaurations'],
            'pieces' => ['url' => '../../public/pieces/pieces.php', 'label' => 'Pièces détachées'],
            'album' => ['url' => '../../public/album/album.php', 'label' => 'Album photos'],
            'logos' => ['url' => '../../public/logos/logos.php', 'label' => 'Logos'],
            'pilotes' => ['url' => '../../public/pilotes/pilotes.php', 'label' => 'Pilotes de légende'],
            'pressbook' => ['url' => '../../public/press/pressbook.php', 'label' => 'Pressbook'],
            'contact' => ['url' => '../../public/contact/contact.php', 'label' => 'Contact']
        ];

        $html = '<header class="nav">' . PHP_EOL;
        $html .= '    <nav class="header-nav">' . PHP_EOL;
        $html .= '        <ul>' . PHP_EOL;

        $html .= '            <li>' . PHP_EOL;
        $html .= '                <a href="../../public/accueil/index.php">' . PHP_EOL;
        $html .= '                    <img src="../../img/logo_bultaco.png" alt="logo">' . PHP_EOL;
        $html .= '                </a>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;

        $isActive = $this->headerActive === 'accueil';
        $href = $isActive ? '#' : $menuItems['accueil']['url'];
        $html .= '            <li>' . PHP_EOL;
        $html .= '                <a href="' . $href . '">' . $menuItems['accueil']['label'] . '</a>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;

        $html .= '            <li class="dropdown">' . PHP_EOL;
        $html .= '                <a href="#" class="dropdown-toggle">CLUB ▾</a>' . PHP_EOL;
        $html .= '                <ul class="dropdown-menu">' . PHP_EOL;

        $dropdownItems = ['restauration', 'pieces', 'album', 'logos', 'pilotes', 'pressbook'];
        foreach ($dropdownItems as $key) {
            $isActive = $this->headerActive === $key;
            $href = $isActive ? '#' : $menuItems[$key]['url'];
            $html .= '                    <li>' . PHP_EOL;
            $html .= '                        <a href="' . $href . '">' . $menuItems[$key]['label'] . '</a>' . PHP_EOL;
            $html .= '                    </li>' . PHP_EOL;
        }

        $html .= '                </ul>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;

        $isActive = $this->headerActive === 'contact';
        $href = $isActive ? '#' : $menuItems['contact']['url'];
        $html .= '            <li>' . PHP_EOL;
        $html .= '                <a href="' . $href . '">' . $menuItems['contact']['label'] . '</a>' . PHP_EOL;
        $html .= '            </li>' . PHP_EOL;

        $html .= '        </ul>' . PHP_EOL;
        $html .= '    </nav>' . PHP_EOL;
        $html .= '</header>' . PHP_EOL;

        return $html;
    }

    private function generatePageTitle($title)
    {
        $html = '<div class="head">' . PHP_EOL;
        $html .= '    <h1>' . strtoupper(htmlspecialchars($title)) . '</h1>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;

        return $html;
    }

    private function generateFooter()
    {
        if (!$this->showFooter) {
            return '';
        }

        $html = '<section class="coordonnees">' . PHP_EOL;
        $html .= '    <div class="coord-header">' . PHP_EOL;
        $html .= '        <h2>CLUB BULTACO TRIAL CLASSIC</h2>' . PHP_EOL;
        $html .= '        <div class="separator"></div>' . PHP_EOL;
        $html .= '    </div>' . PHP_EOL;
        $html .= PHP_EOL;
        $html .= '    <div class="coord-content">' . PHP_EOL;
        $html .= '        <div class="coord-bloc adresse">' . PHP_EOL;
        $html .= '            <h3>ADRESSE</h3>' . PHP_EOL;
        $html .= '            <p>Pierre Escuyer</p>' . PHP_EOL;
        $html .= '            <p>53 Rue Roger Salengro</p>' . PHP_EOL;
        $html .= '            <p>51100 Reims</p>' . PHP_EOL;
        $html .= '            <p>France</p>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= PHP_EOL;
        $html .= '        <div class="coord-bloc contact">' . PHP_EOL;
        $html .= '            <h3>CONTACT</h3>' . PHP_EOL;
        $html .= '            <p>06 08 31 15 65</p>' . PHP_EOL;
        $html .= '            <p>03 26 09 28 85</p>' . PHP_EOL;
        $html .= '            <p>bultaco.trialclassic@orange.fr</p>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= PHP_EOL;
        $html .= '        <div class="coord-bloc reseaux-bloc">' . PHP_EOL;
        $html .= '            <h3>RÉSEAUX SOCIAUX</h3>' . PHP_EOL;
        $html .= '            <div class="reseaux-links">' . PHP_EOL;
        $html .= '                <a href="https://www.instagram.com/bultaco_club_france/" class="reseau-item" target="_blank">' . PHP_EOL;
        $html .= '                    <img src="../../img/sociale.png" alt="Instagram">' . PHP_EOL;
        $html .= '                    <span>@bultaco_club_france</span>' . PHP_EOL;
        $html .= '                </a>' . PHP_EOL;
        $html .= '                <a href="https://www.facebook.com/BultacoAddict/?locale=fr_FR" class="reseau-item" target="_blank">' . PHP_EOL;
        $html .= '                    <img src="../../img/facebook.png" alt="Facebook">' . PHP_EOL;
        $html .= '                    <span>Club Bultaco Trial Classic</span>' . PHP_EOL;
        $html .= '                </a>' . PHP_EOL;
        $html .= '            </div>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= '    </div>' . PHP_EOL;
        $html .= PHP_EOL;
        $html .= '    <div class="coord-footer">' . PHP_EOL;
        $html .= '        <p>Passionnés de motos Bultaco depuis plus de 20 ans</p>' . PHP_EOL;
        $html .= '    </div>' . PHP_EOL;
        $html .= '    <a href="../../login.php">' . PHP_EOL;
        $html .= '        <img src="../../img/logo_rond.png" alt="logorond" class="logorond">' . PHP_EOL;
        $html .= '    </a>' . PHP_EOL;
        $html .= '</section>' . PHP_EOL;

        return $html;
    }

    private function generateEnd()
    {
        $html = '</body>' . PHP_EOL;
        $html .= '</html>' . PHP_EOL;

        return $html;
    }

    public function toHTML($pageTitle, $content)
    {
        $html = $this->generateHead();
        $html .= $this->generateHeader();
        $html .= $this->generatePageTitle($pageTitle);
        $html .= $content;
        $html .= $this->body;
        $html .= $this->generateFooter();
        $html .= $this->generateEnd();

        return $html;
    }
}