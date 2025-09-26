<?php
session_start();
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur BD : " . $e->getMessage());
}

if(!isset($_SESSION['dg']) && !isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}


// Liste des employés avec statut
$stmt = $conn->query("
    SELECT e.id, e.nom, e.prenom, 
    CASE 
        WHEN a.statut='accepté' AND CURDATE() BETWEEN a.date_debut AND a.date_fin THEN 'Absent'
        ELSE 'Présent'
    END AS statut
    FROM employes e
    LEFT JOIN absences a 
    ON e.id = a.employe_id 
    AND a.statut='accepté' 
    AND CURDATE() BETWEEN a.date_debut AND a.date_fin
    ORDER BY e.nom
");
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="manifest" href="/manifest.json">
<link rel="icon" href="gambas-mada.png" type="image/png">
<meta name="theme-color" content="#007bff">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Présence / Absence</title>
  <!-- Bouton retour -->
    <div style="text-align: center; margin-bottom: 20px;">
        <a class="return-btn" href="dash.php">Retour au Dashboard</a>
    </div>
    <script>
        // pour efa hainw //
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/service-worker.js')
    .then(() => console.log('Service Worker enregistré'))
    .catch(err => console.log('Erreur SW:', err));
}

</script>

<style>
body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; }
h1 { text-align:center; margin-bottom:20px; }

.search-container {
    max-width:400px;
    margin:0 auto 20px;
    position: relative;
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

.search-container input {
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
}

.suggestions {
    position: absolute;
    background:white;
    width:100%;
    border:1px solid #ccc;
    border-top:none;
    max-height:150px;
    overflow-y:auto;
    display:none;
    z-index:100;
}

.suggestions div {
    padding:10px;
    cursor:pointer;
}

.suggestions div:hover {
    background:#f4f4f4;
}

table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { padding:10px; border:1px solid #ccc; text-align:center; }
th { background:#333; color:#fff; }
tr:nth-child(even){background:#eee;}
.status-Présent { background:#4caf50; color:#fff; }
.status-Absent { background:#f44336; color:#fff; }
</style>
</head>
<body>

<h1>Présence / Absence</h1>

<div class="search-container">
    <input type="text" id="search" placeholder="Rechercher un employé...">
    <div class="suggestions" id="suggestions"></div>
</div>

<table id="presenceTable">
<tr><th>Nom</th><th>Prénom</th><th>Statut</th></tr>
<?php foreach($liste as $l): ?>
<tr>
    <td><?= htmlspecialchars($l['nom']) ?></td>
    <td><?= htmlspecialchars($l['prenom']) ?></td>
    <td class="status-<?= $l['statut'] ?>"><?= $l['statut'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<script>
// Quand on tape dans la barre de recherche
document.getElementById('search').addEventListener('input', function() {
    let query = this.value.trim();
    let suggestionsBox = document.getElementById('suggestions');

    if(query.length < 1) {
        suggestionsBox.style.display = 'none';
        return;
    }

    fetch('search.php?q=' + encodeURIComponent(query))
    .then(response => response.json())
    .then(data => {
        suggestionsBox.innerHTML = '';
        if(data.length > 0) {
            data.forEach(item => {
                let div = document.createElement('div');
                div.textContent = item.nom + ' ' + item.prenom;
                div.onclick = function() {
                    document.getElementById('search').value = div.textContent;
                    suggestionsBox.style.display = 'none';
                    filterTable(div.textContent);
                }
                suggestionsBox.appendChild(div);
            });
            suggestionsBox.style.display = 'block';
        } else {
            suggestionsBox.style.display = 'none';
        }
    });
});

// Filtre le tableau
function filterTable(name) {
    let rows = document.querySelectorAll("#presenceTable tr:not(:first-child)");
    rows.forEach(row => {
        let fullName = row.cells[0].textContent + ' ' + row.cells[1].textContent;
        row.style.display = fullName.includes(name) ? '' : 'none';
    });
}
</script>

</body>
</html>
