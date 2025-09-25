<?php
session_start();
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BD : " . $e->getMessage());
}

if (isset($_POST['submit'])) {
    // Récupération des données du formulaire
    $employe_id = $_SESSION['user_id']; // Id de l'utilisateur connecté
    $service = $_POST['service'];
    $matricule = $_POST['matricule'];
    $sexe = $_POST['sexe'];
    $motif = $_POST['motif'];
    
    // Stockage des infos dynamiques selon le motif
    $details = json_encode($_POST); // Encode tout le POST, plus tard on peut parser le JSON

    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $nombre_jours = $_POST['nombre_jours'];
    $date_reprise = $_POST['date_reprise'];
    $interim = $_POST['interim'];

    $stmt = $conn->prepare("INSERT INTO absences 
        (employe_id,service,matricule,sexe,motif,details,date_debut,date_fin,nombre_jours,date_reprise,interim)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([$employe_id,$service,$matricule,$sexe,$motif,$details,
                    $date_debut,$date_fin,$nombre_jours,$date_reprise,$interim]);
    
    echo "Bordereau soumis avec succès !";
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard DG - Absences</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f7fa;
    margin: 0;
    padding: 0;
}

header {
    background: #052539;
    color: #fff;
    padding: 20px;
    text-align: center;
}

header h1 {
    margin-bottom: 5px;
}

.container {
    max-width: 1000px;
    margin: 30px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

h2 {
    text-align: center;
    color: #052539;
    margin-bottom: 20px;
}

.button-add {
    display: inline-block;
    margin-bottom: 20px;
    padding: 12px 25px;
    background: #355C7D;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.3s;
}

.button-add:hover {
    background: #4676a3;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background: #052539;
    color: #fff;
}

tr:hover {
    background: #f1f1f1;
}

.status-en-cours {
    color: #ffc107;
    font-weight: bold;
}

.status-termine {
    color: #28a745;
    font-weight: bold;
}

.actions a {
    margin-right: 8px;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    color: #fff;
    font-size: 0.9rem;
}

.actions a.supprimer { background: #dc3545; }
.actions a.supprimer:hover { background: #c82333; }

.actions a.modifier { background: #355C7D; }
.actions a.modifier:hover { background: #4676a3; }

@media(max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }
    table th {
        display: none;
    }
    table td {
        padding: 10px;
        text-align: right;
        position: relative;
    }
    table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 45%;
        font-weight: bold;
        text-align: left;
    }
}
</style>
</head>
<body>

<header>
    <h1>Bienvenue M. le Directeur</h1>
    <p>Vous pouvez consulter toutes les absences et gérer les bordereaux.</p>
</header>



<div class="container">
    <h2>Liste des Absences</h2>
    <a href="formulaire_absence.php" class="button-add">Ajouter un bordereau</a>
    <a href="presence_absence.php" class="button-link">
    <button>Voir les présences / absences</button>
</a>

    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Service</th>
                <th>Motif</th>
                <th>Dates</th>
                <th>Nombre de jours</th>
                <th>Intérim</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($absences)): ?>
            <tr><td colspan="8" style="text-align:center;">Aucune absence enregistrée.</td></tr>
        <?php else: ?>
            <?php foreach($absences as $abs): ?>
            <tr>
                <td data-label="Employé"><?= htmlspecialchars($abs['nom'].' '.$abs['prenom']); ?></td>
                <td data-label="Service"><?= htmlspecialchars($abs['service']); ?></td>
                <td data-label="Motif"><?= htmlspecialchars($abs['motif']); ?></td>
                <td data-label="Dates"><?= htmlspecialchars($abs['date_debut'].' au '.$abs['date_fin']); ?></td>
                <td data-label="Nombre de jours"><?= htmlspecialchars($abs['nombre_jours']); ?></td>
                <td data-label="Intérim"><?= htmlspecialchars($abs['interim']); ?></td>
                <td data-label="Statut" class="<?= $abs['motif']==='en cours'?'status-en-cours':'status-termine' ?>"><?= htmlspecialchars($abs['motif']); ?></td>
                <td data-label="Actions" class="actions">
                    <a href="modifier_absence.php?id=<?= $abs['id']; ?>" class="modifier">Modifier</a>
                    <a href="supprimer_absence.php?id=<?= $abs['id']; ?>" class="supprimer" onclick="return confirm('Supprimer ce bordereau ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
