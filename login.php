<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = ""; // Toujours initialiser

// Connexion BD
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BD : " . $e->getMessage());
}

/**
 * === Vérification automatique via le cookie "remember_me" ===
 */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $stmt = $conn->prepare("SELECT * FROM employes WHERE remember_token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        header("Location: dash.php");
        exit();
    }
}

/**
 * === Traitement du formulaire de connexion ===
 */
if (isset($_POST['login'])) {
    $mail = trim($_POST['mail']);
    $pass = trim($_POST['pass']);
    $remember = isset($_POST['remember_token']); // Checkbox cochée ou non

    // Connexion DG (Admin)
    if ($mail === 'abc@gmail.com' && $pass === 'root') {
        $_SESSION['dg'] = true;
        $_SESSION['nom'] = 'DG';
        header("Location: dashDG.php");
        exit();
    }

    // Connexion Employé
    if (!empty($mail) && !empty($pass)) {
        $stmt = $conn->prepare("SELECT * FROM employes WHERE mail = :mail");
        $stmt->execute(['mail' => $mail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($pass, $user['pass'])) {
                // Créer la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];

                // Gestion du "Se souvenir de moi"
                if ($remember) {
                    $token = bin2hex(random_bytes(32));

                    // Mettre le token en BD
                    $update = $conn->prepare("UPDATE employes SET remember_token = :token WHERE id = :id");
                    $update->execute([
                        'token' => $token,
                        'id' => $user['id']
                    ]);

                    // Créer le cookie pour 30 jours
                    setcookie('remember_me', $token, time() + (86400 * 30), "/", "", false, true);
                }

                header("Location: dash.php");
                exit();
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Aucun compte trouvé avec cet email.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="login.css">
  <title>Connexion</title>

</head>
<body>
  <form method="POST" action="">
    <h1>Connexion</h1>

    <!-- Message d'erreur -->
    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <input type="email" name="mail" placeholder="Adresse e-mail" required>
    <input type="password" name="pass" placeholder="Mot de passe" required>
     <br>
    <label id="but">
      <input type="checkbox" name="remember_token" id="check"> Se souvenir de moi
    </label>

    <button type="submit" name="login">Se connecter</button>

    <div class="form-footer">
      <p>Pas encore de compte ? <a href="signin.php">S'inscrire</a></p>
    </div>
  </form>
</body>
</html>
      