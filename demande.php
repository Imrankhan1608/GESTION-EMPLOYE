<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$employe_id = $_SESSION['user_id'];
$error = "";

try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur BD : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpass = $_POST['cpass'] ?? '';

    if (empty($cpass)) {
        $error = "Veuillez confirmer avec votre mot de passe.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT pass, matricule, service FROM employes WHERE id = :id");
            $stmt->execute(['id'=>$employe_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "Utilisateur non trouvé.";
            } elseif (!password_verify($cpass, $user['pass'])) {
                $error = "Mot de passe incorrect.";
            } else {
                // Récupérer les champs
                $service = $_POST['service'] ?? '';
                $matricule = $_POST['matricule'] ?? '';
                $sexe = $_POST['sexe'] ?? '';
                $motif = $_POST['motif'] ?? '';
                $date_debut = $_POST['date_debut'] ?? '';
                $date_fin = $_POST['date_fin'] ?? '';
                $date_reprise = $_POST['date_reprise'] ?? '';
                $interim = $_POST['interim'] ?? '';
                $details = $_POST['details'] ?? '';

                if ($matricule != $user['matricule'] || $service != $user['service']) {
                    $error = "Vos informations ne correspondent pas à celles de la base.";
                } else {
                    $start = strtotime($date_debut);
                    $end = strtotime($date_fin);
                    if ($start === false || $end === false) $error = "Dates invalides.";
                    elseif ($start > $end) $error = "La date de début doit être avant la date de fin.";
                    else {
                        $nombre_jours = ($end - $start)/(60*60*24)+1;

                        // Vérifier les dates
$start = strtotime($date_debut);
$end = strtotime($date_fin);

$minDate = strtotime('1900-01-01');
$maxDate = strtotime('2100-12-31');

if ($start === false || $end === false) {
    $error = "Dates invalides.";
} elseif ($start < $minDate || $end < $minDate || $start > $maxDate || $end > $maxDate) {
    $error = "Les dates doivent être comprises entre 1900 et 2100.";
} elseif ($start > $end) {
    $error = "La date de début doit être avant la date de fin.";
} else {
    $nombre_jours = ($end - $start)/(60*60*24) + 1;
}
      
                        // Vérifier absences existantes
                        $stmt = $conn->prepare("SELECT * FROM absences WHERE employe_id = :id AND ((date_debut <= :date_fin AND date_fin >= :date_debut))");
                        $stmt->execute([
                            'id'=>$employe_id,
                            'date_debut'=>$date_debut,
                            'date_fin'=>$date_fin
                        ]);
                        if($stmt->fetch(PDO::FETCH_ASSOC)){
                            $error = "Vous avez déjà une absence enregistrée sur ces dates.";
                        } else {
                            $sql = "INSERT INTO absences (employe_id, service, matricule, sexe, motif, details, date_debut, date_fin, nombre_jours, date_reprise, interim)
                                    VALUES (:employe_id, :service, :matricule, :sexe, :motif, :details, :date_debut, :date_fin, :nombre_jours, :date_reprise, :interim)";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([
                                'employe_id'=>$employe_id,
                                'service'=>$service,
                                'matricule'=>$matricule,
                                'sexe'=>$sexe,
                                'motif'=>$motif,
                                'details'=>$details,
                                'date_debut'=>$date_debut,
                                'date_fin'=>$date_fin,
                                'nombre_jours'=>$nombre_jours,
                                'date_reprise'=>$date_reprise,
                                'interim'=>$interim
                            ]);

                            header("Location: dash.php");
                            exit();
                        }
                    }
                }
            }

        } catch(PDOException $e){
            $error = "Erreur BD : ".$e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="manifest" href="/manifest.json">
<link rel="icon" href="gambas-mada.png" type="image/png">
<meta name="theme-color" content="#007bff">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bordereau d'Absence - PNB</title>
<link rel="stylesheet" href="demande.css">
<style>
.error { color: red; margin: 10px 0; }
.hidden { display: none; }
</style>
</head>
 <script>
    // pour efa hainw //
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/service-worker.js')
    .then(() => console.log('Service Worker enregistré'))
    .catch(err => console.log('Erreur SW:', err));
}

</script>  
<body>

<header>
    <h1>GROUPE UNIMA - MADAGASCAR</h1>
    <h3>BORDEREAU D'ABSENCE</h3>
</header>

<?php if($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="post" class="absence-form">
    <!-- Service et matricule -->
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
        <input type="number" name="matricule" id="matricule" required>
    </div>

    <!-- Sexe -->
    <div class="form-inline">
        <label>Sexe :</label>
        <label><input type="radio" name="sexe" value="Mr" required> Mr</label>
        <label><input type="radio" name="sexe" value="Mme"> Mme</label>
    </div>

    <!-- Motif -->
    <div class="form-group">
        <label for="motif">Motif :</label>
        <select name="motif" id="motif" required>
            <option value="">-- Sélectionnez un motif --</option>
            <option value="mission">Mission</option>
            <option value="repos normal">Repos Normal</option>
            <option value="conge annuel">Congé Annuel</option>
            <option value="permission remunere">Permission Rémunérée</option>
            <option value="permission nonremunere">Permission Non Rémunérée</option>
            <option value="formation">Formation</option>
            <option value="autre">Autre Motif</option>
        </select>
    </div>
       <label for="description">Description :</label>
       <input type="text" name="details" id="description" placeholder="Détaillez le motif" required>
    <!-- Dates et détails -->
    <div class="form-group">
        <label for="date_debut">Date début :</label>
        <input type="date" name="date_debut" required>
    </div>

    <div class="form-group">
        <label for="date_fin">Date fin :</label>
        <input type="date" name="date_fin" required>
    </div>

    <div class="form-group">
        <label for="nombre_jours">Nombre de jours :</label>
        <input type="number" name="nombre_jours" required>
    </div>

    <div class="form-group">
        <label for="date_reprise">Date de reprise :</label>
        <input type="date" name="date_reprise" required>
    </div>

    <div class="form-group">
        <label for="interim">Intérim :</label>
        <input type="text" name="interim">
    </div>

    <!-- Confirmation mot de passe -->
    <p><strong>Confirmation :</strong> Veuillez entrer votre mot de passe pour valider l'envoi.</p>
    <div class="form-group">
        <input type="password" name="cpass" placeholder="Mot de passe" required>
    </div>

    <button type="submit">J'accepte et envoie</button>
</form>
<script src="index.js"></script>
</body>
</html>
