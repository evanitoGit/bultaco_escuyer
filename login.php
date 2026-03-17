<?php
session_start();
require_once realpath(__DIR__ . '/config.php');

$error = false;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $redirect = $_POST['redirect'];

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = true;
        header("Location: public/accueil/choix_admin.php");
        exit;
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin - Club Bultaco</title>
    <link rel="stylesheet" href="css/style_login.css">
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <h2 class="login-title">Connexion Admin</h2>

        <?php if ($error): ?>
            <div class="error-message">
                ❌ Identifiants incorrects
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Se connecter</button>
        </form>

        <a href="public/accueil/index.php" class="back-link">← Retour au site</a>
    </div>
</div>
</body>
</html>