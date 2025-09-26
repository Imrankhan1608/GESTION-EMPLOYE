<?php
// ===== Connexion à la base =====
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur de connexion : " . $e->getMessage());
}

// ===== Variables =====
$employe = null;
$absences = [];
$message = "";

// ===== Recherche =====
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);

    // Rechercher l'employé par nom, prénom ou email
    $stmt = $conn->prepare("
        SELECT * FROM employes 
        WHERE nom LIKE ? OR prenom LIKE ? OR mail LIKE ?
    ");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
    $employe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employe) {
        // Récupérer toutes les absences de cet employé
        $stmtAbs = $conn->prepare("
            SELECT * FROM absences 
            WHERE employe_id = ? 
            ORDER BY date_debut DESC
        ");
        $stmtAbs->execute([$employe['id']]);
        $absences = $stmtAbs->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $message = "Aucun employé trouvé pour '$search'.";
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
<title>Dossier Employé</title>
<style>
 
/* ===== Styles généraux ===== */
body {
    font-family: Arial, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 20px;
}
.container {
    max-width: 1000px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
h1 {
    text-align: center;
    color: #2a3840;
}
p{
     text-transform: capitalize;
     display: block;
}
form {
    text-align: center;
    margin-bottom: 20px;
}
input[type="text"] {
    padding: 10px;
    width: 60%;
    border: 1px solid #ccc;
    border-radius: 8px;
}
button {
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
button:hover {
    background: #0056b3;
}
.employe-info {
    background: #eef4ff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
}
th {
    background: #2a3840;
    color: white;
}
.status-attente {
    background: #ffeb99;
    color: #856404;
    font-weight: bold;
}
.status-accepte {
    background: #c3f7c8;
    color: #155724;
    font-weight: bold;
}
.status-refuse {
    background: #f8d7da;
    color: #721c24;
    font-weight: bold;
}
.message {
    text-align: center;
    color: red;
    font-weight: bold;
}
a.button-link {
    text-decoration: none;
}
.return-btn {
    display: inline-block;
    margin-bottom: 20px;
    background: #28a745;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    font-weight: bold;
}
.return-btn:hover {
    opacity: 0.85;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }
    table thead {
        display: none;
    }
    table tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
    }
    table td {
        text-align: right;
        padding: 10px;
        border: none;
        position: relative;
        text-transform: capitalize;
    }
    table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        font-weight: bold;
        text-align: left;
         text-transform: capitalize;
        width: 50%;
    }
    input[type="text"], button, .return-btn {
        width: 100%;
        margin-bottom: 10px;
        box-sizing: border-box;
    }
}
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
<div class="container">
    <h1>Dossier Employé</h1>

    <!-- Bouton retour -->
    <div style="text-align: center; margin-bottom: 20px;">
        <a class="return-btn" href="dashDG.php">Retour au Dashboard</a>
    </div>

    <!-- Formulaire de recherche -->
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Rechercher par nom, prénom ou email" required>
        <button type="submit">Rechercher</button>
    </form>

    <?php if (!empty($message)) : ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($employe) : ?>
        <!-- Informations de l'employé -->
        <div class="employe-info">
            <h2>Informations de l'employé</h2>
            <p><strong>Nom :</strong> <?= "<p>".htmlspecialchars($employe['nom'])."</p>" ?></p>
            <p><strong>Prénom :</strong> <?="<p>". htmlspecialchars($employe['prenom'])."</p>" ?></p>
            <p><strong>Email :</strong> <?= "<p>".htmlspecialchars($employe['mail'])."</p>" ?></p>
            <p><strong>Service :</strong> <?="<p>". htmlspecialchars($employe['service'])."</p>" ?></p>
            <p><strong>Matricule :</strong> <?= "<p>".htmlspecialchars($employe['matricule'])."</p>" ?></p>
        </div>

        <!-- Tableau des absences -->
        <h2>Historique des Absences</h2>
        <?php if (!empty($absences)) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Motif</th>
                        <th>Détails</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Nombre de jours</th>
                        <th>Date Reprise</th>
                        <th>Intérim</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($absences as $abs) : ?>
                        <tr>
                            <td data-label="Motif"><?= "<p>".htmlspecialchars($abs['motif'])."</p>" ?></td>
                            <td data-label="Détails"><?="<p>".htmlspecialchars($abs['details'])."</p>" ?></td>
                            <td data-label="Date Début"><?="<p>".htmlspecialchars($abs['date_debut'])."</p>" ?></td>
                            <td data-label="Date Fin"><?= "<p>".htmlspecialchars($abs['date_fin'])."</p>" ?></td>
                            <td data-label="Nombre de jours"><?= "<p>".htmlspecialchars($abs['nombre_jours'])."</p>" ?></td>
                            <td data-label="Date Reprise"><?= "<p>".htmlspecialchars($abs['date_reprise'])."</p>" ?></td>
                            <td data-label="Intérim"><?= "<p>".htmlspecialchars($abs['interim']) ?></td>
                            <td data-label="Statut" class="<?php 
                                if ($abs['statut'] == 'en attente') echo 'status-attente';
                                elseif ($abs['statut'] == 'accepté') echo 'status-accepte';
                                else echo 'status-refuse';
                            ?>">
                                <?= htmlspecialchars($abs['statut']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Aucune absence trouvée pour cet employé.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
