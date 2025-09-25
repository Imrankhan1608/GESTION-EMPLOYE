<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'employé est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['nom'];

// Connexion BD
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BD : " . $e->getMessage());
}

// Récupérer les absences de l'employé
$stmt = $conn->prepare("SELECT * FROM absences WHERE employe_id = ? ORDER BY date_debut DESC");
$stmt->execute([$user_id]);
$absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Employé</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
    padding: 0;
}
header {
    background: #052539;
    color: white;
    padding: 20px;
    text-align: center;
}
header h1 {
    margin: 0;
}
.container {
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}
th {
    background: #052539;
    color: white;
}
.status-attente { color: #ffc107; font-weight: bold; }
.status-accept { color: #28a745; font-weight: bold; }
.status-refuse { color: #dc3545; font-weight: bold; }

button {
    background: #355C7D;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
}
button:hover { background: #4676a3; }
a.button-link { text-decoration: none; }
</style>
</head>
<body>

<header>
    

    <h1>Bienvenue, <?= htmlspecialchars($user_nom); ?> !</h1>
    <p>Suivez vos absences et leur statut</p>
</header>

<div class="container">
    <a href="demande.php" class="button-link"><button>Envoyer un nouveau bordereau</button></a>
    <!-- Bouton "Présence / Absence" -->
<a href="presence_absence.php" class="button-link">
    <button>Voir les présences / absences</button>
</a>

    <h2>Mes Absences</h2>
    <table>
        <tr>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Motif</th>
            <th>Nombre de jours</th>
            <th>Statut</th>
        </tr>
        <?php if(empty($absences)): ?>
        <tr><td colspan="5">Aucune absence pour le moment.</td></tr>
        <?php else: ?>
            <?php foreach($absences as $abs): ?>
                <tr>
                    <td><?= htmlspecialchars($abs['date_debut']); ?></td>
                    <td><?= htmlspecialchars($abs['date_fin']); ?></td>
                    <td><?= htmlspecialchars($abs['motif']); ?></td>
                    <td><?= htmlspecialchars($abs['nombre_jours']); ?></td>
                    <td class="<?= $abs['statut'] === 'accepté' ? 'status-accept' : ($abs['statut'] === 'refusé' ? 'status-refuse' : 'status-attente') ?>">
                        <?= ucfirst($abs['statut']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
