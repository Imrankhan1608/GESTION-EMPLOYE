<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = "";
$success = "";

// Connexion à la base
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur BD : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le mot de passe de confirmation
    if (empty($_POST['cpass'])) {
        $error = "Veuillez confirmer avec votre mot de passe.";
    } else {
        $cpass = $_POST['cpass'];
        $mail = $_SESSION['mail'] ?? ''; // récupère le mail de la session

        // Récupérer le mot de passe hashé depuis la BD
        $stmt = $conn->prepare("SELECT pass FROM employes WHERE mail = :mail");
        $stmt->execute(['mail' => $mail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($cpass, $user['pass'])) {
            $error = "Mot de passe incorrect.";
        } else {
            // Tous les champs du formulaire
            $direction = $_POST['direction'] ?? '';
            $service = $_POST['service'] ?? '';
            $matricule = $_POST['matricule'] ?? '';
            $sexe = $_POST['sexe'] ?? '';
            $motif = $_POST['motif'] ?? '';
            $date_debut = $_POST['date_debut'] ?? '';
            $date_fin = $_POST['date_fin'] ?? '';
            $nombre_jours = $_POST['nombre_jours'] ?? '';
            $date_reprise = $_POST['date_reprise'] ?? '';
            $interim = $_POST['interim'] ?? '';
            $autres = $_POST['presi'] ?? '';
            $mission = $_POST['mission'] ?? '';
            $cause = $_POST['cause'] ?? '';
            $transport_aller = $_POST['aller'] ?? '';
            $transport_retour = $_POST['retour'] ?? '';

            // Insérer le bordereau dans la table absences
            $sql = "INSERT INTO absences 
                (direction, service, matricule, sexe, motif, date_debut, date_fin, nombre_jours, date_reprise, interim, autres, mission, cause, transport_aller, transport_retour, statut) 
                VALUES 
                (:direction, :service, :matricule, :sexe, :motif, :date_debut, :date_fin, :nombre_jours, :date_reprise, :interim, :autres, :mission, :cause, :transport_aller, :transport_retour, 'en_attente')";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'direction' => $direction,
                'service' => $service,
                'matricule' => $matricule,
                'sexe' => $sexe,
                'motif' => $motif,
                'date_debut' => $date_debut,
                'date_fin' => $date_fin,
                'nombre_jours' => $nombre_jours,
                'date_reprise' => $date_reprise,
                'interim' => $interim,
                'autres' => $autres,
                'mission' => $mission,
                'cause' => $cause,
                'transport_aller' => $transport_aller,
                'transport_retour' => $transport_retour
            ]);

            $success = "Bordereau envoyé avec succès. Statut : en attente.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bordereau d'Absence - PNB</title>
  <link rel="stylesheet" href="demande.css">
</head>
<body>
  <header>
    <h1>GROUPE UNIMA - MADAGASCAR</h1>
    <h3>BORDEREAU D'ABSENCE</h3>
  </header>

  <form method="post" class="absence-form">
    <!-- Informations générales -->
    <section class="section-group">
      <div class="form-group">
      </div>

      <div class="form-group">
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
      </div>

      <div class="form-group">
        <label for="matricule">Matricule :</label>
        <input type="number" id="matricule" name="matricule" required>
      </div>
    </section>

    <!-- Sexe -->
    <div class="form-inline">
      <label>Sexe :</label>
      <label><input type="radio" name="sexe" value="Mr" required> Mr</label>
      <label><input type="radio" name="sexe" value="Mme"> Mme</label>
    </div>

    <!-- Motif d'absence -->
    <div class="form-group">
      <label for="motif">Motif :</label>
      <select name="motif" id="motif" onchange="afficherSection()" required>
        <option value="">-- Sélectionnez un motif --</option>
        <option value="mission">Mission</option>
        <option value="repos_normal">Repos Normal</option>
        <option value="conge_annuel">Congé Annuel</option>
        <option value="permission_remunere">Permission Rémunérée</option>
        <option value="permission_nonremunere">Permission Non Rémunérée</option>
        <option value="formation">Formation</option>
        <option value="autre">Autre Motif</option>
      </select>
    </div>

    <!-- Sections dynamiques -->
    <div id="section-dynamique">
      <!-- Mission -->
      <div id="mission" class="hidden">
        <label for="lieu_mission">Mission à :</label>
        <input type="text" id="lieu_mission" name="mission">

        <label for="cause_mission">Pour :</label>
        <input type="text" id="cause_mission" name="cause">

        <div class="form-inline">
          <label for="transport_aller">Transport aller :</label>
          <select id="transport_aller" name="aller">
            <option value="avion">Avion</option>
            <option value="voiture">Voiture</option>
          </select>

          <label for="transport_retour">Transport retour :</label>
          <select id="transport_retour" name="retour">
            <option value="avion">Avion</option>
            <option value="voiture">Voiture</option>
          </select>
        </div>
      </div>

      <!-- Repos Normal -->
      <div id="repos_normal" class="hidden">
        <label for="solde_repos">Solde repos à date :</label>
        <input type="text" id="solde_repos" name="solde_repos">
      </div>

      <!-- Congé Annuel -->
      <div id="conge_annuel" class="hidden">
        <label for="solde_conge">Solde congé à date :</label>
        <input type="text" id="solde_conge" name="solde_conge">
      </div>

      <!-- Permission Rémunérée -->
      <div id="permission_remunere" class="hidden">
        <label for="motif_remunere">Motif :</label>
        <input type="text" id="motif_remunere" name="permission_remunere">
      </div>

      <!-- Permission Non Rémunérée -->
      <div id="permission_nonremunere" class="hidden">
        <label for="motif_nonremunere">Motif :</label>
        <input type="text" id="motif_nonremunere" name="permission_nonremunere">
      </div>

      <!-- Formation -->
      <div id="formation" class="hidden">
        <label for="lieu_formation">Formation à :</label>
        <input type="text" id="lieu_formation" name="lieux">

        <label for="titre_formation">Titre :</label>
        <input type="text" id="titre_formation" name="titre">

        <div class="form-inline">
          <label for="transport_aller_formation">Transport aller :</label>
          <select id="transport_aller_formation" name="aller">
            <option value="avion">Avion</option>
            <option value="voiture">Voiture</option>
          </select>

          <label for="transport_retour_formation">Transport retour :</label>
          <select id="transport_retour_formation" name="retour">
            <option value="avion">Avion</option>
            <option value="voiture">Voiture</option>
          </select>
        </div>
      </div>

      <!-- Autre Motif -->
      <div id="autre" class="hidden">
        <label for="motif_autre">Repos récupération du :</label>
        <input type="text" id="motif_autre" name="autre">
      </div>
    </div>

    <!-- Dates et détails -->
    <div class="form-inline">
      <div class="form-group">
        <label for="date_debut">Date début :</label>
        <input type="date" id="date_debut" name="date_debut" required>
      </div>

      <div class="form-group">
        <label for="date_fin">Date fin :</label>
        <input type="date" id="date_fin" name="date_fin" required>
      </div>
    </div>

    <div class="form-group">
      <label for="nombre_jours">Nombre de jours :</label>
      <input type="number" id="nombre_jours" name="nombre_jours" required>
    </div>

    <div class="form-group">
      <label for="date_reprise">Date de reprise de travail :</label>
      <input type="date" id="date_reprise" name="date_reprise" required>
    </div>

    <div class="form-group">
      <label for="presi">Autre à préciser :</label>
      <input type="text" id="presi" name="presi">
    </div>

    <p class="note">
      <strong>Note :</strong> Pour toute absence de plus de 2 jours (Directeur ou Chef de département), un délégué de pouvoir doit être désigné.
    </p>

    <div class="form-group">
      <label for="interim">Intérim :</label>
      <input type="text" id="interim" name="interim" >
    </div>

    <a onclick="adver()" id="add">Valider</a>
    <div id="adver">
  <!-- Nouvelle phrase -->
  <p><strong>Confirmation :</strong> Veuillez entrer votre mot de passe pour valider l'envoi de votre bordereau d'absence.</p>

  <input type="password" style="margin:2%;" required name="cpass" placeholder="Mot de passe">
  <button type="submit" class="btn-submitone">J'accepte</button>
</div>
  </form>

  <script src="index.js"></script>
</body>
</html>
