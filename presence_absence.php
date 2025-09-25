<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté (DG ou employé)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['dg'])) {
    header("Location: login.php");
    exit();
}

// Connexion BD
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur BD : " . $e->getMessage());
}

// Si DG : voir tous les employés, sinon juste l'employé connecté
if (isset($_SESSION['dg'])) {
    $stmt = $conn->prepare("SELECT e.nom, e.prenom, a.date_debut, a.date_fin, a.statut 
                            FROM employes e 
                            LEFT JOIN absences a ON e.id = a.employe_id
                            ORDER BY e.nom, a.date_debut DESC");
    $stmt->execute();
} else {
    $stmt = $conn->prepare("SELECT e.nom, e.prenom, a.date_debut, a.date_fin, a.statut 
                            FROM employes e 
                            LEFT JOIN absences a ON e.id = a.employe_id
                            WHERE e.id = ?
                            ORDER BY a.date_debut DESC");
    $stmt->execute([$_SESSION['user_id']]);
}

$absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Présences / Absences</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0; padding: 0;
}
header { background: #052539; color: white; padding: 20px; text-align: center; }
header h1 { margin: 0; }
.container { max-width: 1000px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
th { background: #052539; color: white; }
.status-attente { color: #ffc107; font-weight: bold; }
.status-accept { color: #28a745; font-weight: bold; }
.status-refuse { color: #dc3545; font-weight: bold; }
button { background: #355C7D; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; margin-bottom: 10px; }
button:hover { background: #4676a3; }
</style>
</head>
<body>

<header>
    <h1>Présences / Absences</h1>
    <p>Suivi des employés</p>
</header>

<div class="container">
    <a href="<?= isset($_SESSION['dg']) ? 'dashDG.php' : 'dash.php'; ?>">
        <button>Retour au Dashboard</button>
    </a>

    <table>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Statut</th>
        </tr>
        <?php if(empty($absences)): ?>
        <tr><td colspan="5">Aucune absence pour le moment.</td></tr>
        <?php else: ?>
            <?php foreach($absences as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['nom']); ?></td>
                    <td><?= htmlspecialchars($a['prenom']); ?></td>
                    <td><?= $a['date_debut'] ?? '-'; ?></td>
                    <td><?= $a['date_fin'] ?? '-'; ?></td>
                    <td class="<?= $a['statut']==='accepté'?'status-accept':($a['statut']==='refusé'?'status-refuse':'status-attente') ?>">
                        <?= $a['statut'] ?? 'Présent'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
