<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    // Vérifie si les champs existent
    if (!empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['matricule']) && 
        !empty($_POST['service']) && !empty($_POST['mail']) && !empty($_POST['pass']) && !empty($_POST['cpass'])) {

        // Récupération et nettoyage des données
        $nom = trim(htmlspecialchars($_POST['nom']));
        $prenom = trim(htmlspecialchars($_POST['prenom']));
        $matricule = trim(htmlspecialchars($_POST['matricule']));
        $service = trim(htmlspecialchars($_POST['service']));
        $mail = trim(htmlspecialchars($_POST['mail']));
        $pass = $_POST['pass'];
        $cpass = $_POST['cpass'];

        // Vérification que les deux mots de passe correspondent
        if ($pass !== $cpass) {
            echo "⚠️ Les mots de passe ne correspondent pas.";
            exit();
        }

        // Hash du mot de passe
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        try {
            // Connexion à la base de données
            $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérification si l'email existe déjà
            $checkEmail = $conn->prepare("SELECT id FROM employes WHERE mail = ?");
            $checkEmail->execute([$mail]);

            if ($checkEmail->rowCount() > 0) {
                echo "⚠️ Cet email est déjà utilisé.";
                exit();
            }

            // Insertion dans la base
            $requette = $conn->prepare("INSERT INTO employes (nom, prenom, matricule, service, mail, pass) VALUES (?, ?, ?, ?, ?, ?)");
            $requette->execute([$nom, $prenom, $matricule, $service, $mail, $hashedPass]);

            // Création de la session
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;

            // Redirection vers le tableau de bord
            header("Location: dash.php");
            exit();

        } catch (PDOException $e) {
            echo "Erreur SQL : " . $e->getMessage();
        }

    } else {
        echo "⚠️ Veuillez remplir tous les champs.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="signin.css">
    <title>INSCRIPTION</title>
</head>
<body>
    <form method="POST">
      <h1>Créer un compte</h1>

      <div id="flex"><label for="nom">Nom :</label>
      <input type="text" id="nom" name="nom" required>

      <label for="prenom">Prénom :</label>
      <input type="text" id="prenom" name="prenom" required>

      <label for="matricule">Matricule :</label>
      <input type="number" name="matricule" required>
      </div>

      <label for="service">Service :</label>
      <select name="service" id="service" required>
          <option value="">-- Sélectionnez un service --</option>
          <option value="armement">Armement</option>
          <option value="controle qualité">Contrôle Qualité</option>
          <option value="direction">Direction</option>
          <option value="finance & comptabilité">Finance & Comptabilité</option>
          <option value="magasin">Magasin</option>
          <option value="production">Production</option>
          <option value="ressource humaine">Ressources Humaines</option>
          <option value="techniques">Service Techniques</option>
          <option value="transit">Transit</option>
        </select>

      <label for="mail">Email :</label>
      <input type="email" id="mail" name="mail" required>

      <label for="pass">Mot de passe :</label>
      <input type="password" id="pass" name="pass" required>

      <label for="cpass">Confirmer le mot de passe :</label>
      <input type="password" id="cpass" name="cpass" required>


      

      <!-- Bouton d'inscription -->
      <button type="submit" id="submitBtn" name="submit" disabled>
        S'inscrire
      </button>
<br>
      <div class="form-footer">
        Déjà un compte ? <a href="login.php">Se connecter</a>
      </div>
  </form>

  <script>
    const pass = document.getElementById('pass');
    const cpass = document.getElementById('cpass');
    const submitBtn = document.getElementById('submitBtn');

    function checkPasswords() {
      if (cpass.value === "") {
        cpass.classList.remove('valid', 'invalid');
        submitBtn.disabled = true;
        return;
      }

      if (pass.value === cpass.value) {
        cpass.classList.add('valid');
        cpass.classList.remove('invalid');
        submitBtn.disabled = false;
      } else {
        cpass.classList.add('invalid');
        cpass.classList.remove('valid');
        submitBtn.disabled = true;
      }
    }

    pass.addEventListener('input', checkPasswords);
    cpass.addEventListener('input', checkPasswords);
  </script>

</body>
</html>