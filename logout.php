<?php
session_start();
session_destroy();
header('Location: public/accueil/index.php');
exit;