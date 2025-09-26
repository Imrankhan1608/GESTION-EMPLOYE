<?php
session_start();
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur BD : " . $e->getMessage());
}

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Ses propres demandes
$stmt = $conn->prepare("SELECT * FROM absences WHERE employe_id=:id ORDER BY date_creation DESC");
$stmt->execute(['id'=>$_SESSION['user_id']]);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="manifest" href="/manifest.json">
<link rel="icon" href="gambas-mada.png" type="image/png">
<meta name="theme-color" content="#007bff">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Dashboard</title>
<style>
body { font-family: 'Segoe UI', Tahoma, sans-serif; background:#e2e8f0; padding:20px; }
h1 { text-align:center; margin-bottom:30px; color:#333;text-transform: capitalize; }
.btn { padding:10px 15px; background:#2563eb; color:#fff; text-decoration:none; border-radius:6px; margin:5px; display:inline-block; }
.btndeco { padding:10px 15px;background:#dc3545; color:#fff; text-decoration:none; border-radius:6px; margin:5px; display:inline-block; }
.btn:hover { opacity:0.85; }
.btndeco:hover { opacity:0.85; background: #c82333; }
table { width:100%; border-collapse: collapse; box-shadow:0 4px 10px rgba(0,0,0,0.1); background:#fff; }
th, td { padding:12px 10px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#2563eb; color:#fff; }
tr:hover { background:#f3f4f6; }
.status-en_attente { background:#facc15; color:#333; font-weight:bold; }
.status-accepté { background:#4ade80; color:#fff; font-weight:bold; }
.status-refusé { background:#f87171; color:#fff; font-weight:bold; }
.remove-btn { cursor:pointer; background:#f87171; border:none; padding:5px 10px; border-radius:4px; color:#fff; }
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

<?php echo"<h1>"."Bienvenue"." ".$_SESSION['nom']." ".$_SESSION['prenom']."</h1>" ?>

<div style="text-align:center; margin-bottom:20px;">
   
    <a class="btn" href="demande.php">Nouvelle demande</a>
    <a class="btn" href="presence_absence.php">Voir Présence / Absence</a>
    <a class="btndeco"  href="logout.php">Deconnexion</a>
</div>

<table id="table-demandes">
<tr>
<th>Motif</th><th>Début</th><th>Fin</th><th>Jours</th><th>Statut</th><th>Supprimer</th>
</tr>

<?php foreach($demandes as $d): ?>
<tr>
<td><?= htmlspecialchars($d['motif']) ?></td>
<td><?= htmlspecialchars($d['date_debut']) ?></td>
<td><?= htmlspecialchars($d['date_fin']) ?></td>
<td><?= htmlspecialchars($d['nombre_jours']) ?></td>
<td class="status-<?= str_replace(' ','_',$d['statut']) ?>"><?= htmlspecialchars($d['statut']) ?></td>
<td><button class="remove-btn" onclick="removeRow(this)">Sup</button></td>
</tr>
<?php endforeach; ?>
</table>


<script>
// Supprime une ligne de l'interface uniquement
function removeRow(btn){
    const row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
}
</script>

<div id="stats">
    <p><strong>Présents :</strong> <span id="presentCount">0</span></p>
    <p><strong>Absents :</strong> <span id="absentCount">0</span></p>
    <p><strong>Demandes en attente :</strong> <span id="attenteCount">0</span></p>
    <p><strong>Demandes acceptées :</strong> <span id="accepteCount">0</span></p>
    <p><strong>Demandes refusées :</strong> <span id="refuseCount">0</span></p>
</div>
<script>
function updateDashboard() {
    fetch('update_dashboard.php')
    .then(response => response.json())
    .then(data => {
        if(data.error){
            console.error(data.error);
            return;
        }

        // Mettre à jour les stats
        document.getElementById('presentCount').textContent = data.presence.presents || 0;
        document.getElementById('absentCount').textContent = data.presence.absents || 0;

        document.getElementById('attenteCount').textContent = data.demandes.en_attente || 0;
        document.getElementById('accepteCount').textContent = data.demandes.accepte || 0;
        document.getElementById('refuseCount').textContent = data.demandes.refuse || 0;
    })
    .catch(error => console.error('Erreur fetch :', error));
}

// Actualise toutes les 10 secondes
setInterval(updateDashboard, 3000);

// Lancer la mise à jour dès l'ouverture de la page
updateDashboard();
// pour le maj//
function updateDashboard() {
    const status = document.getElementById('status');
    status.textContent = "Mise à jour en cours...";

    fetch('update_dashboard.php')
    .then(response => response.json())
    .then(data => {
        if(data.error){
            status.textContent = "Erreur lors de la mise à jour";
            return;
        }

        document.getElementById('presentCount').textContent = data.presence.presents || 0;
        document.getElementById('absentCount').textContent = data.presence.absents || 0;
        document.getElementById('attenteCount').textContent = data.demandes.en_attente || 0;
        document.getElementById('accepteCount').textContent = data.demandes.accepte || 0;
        document.getElementById('refuseCount').textContent = data.demandes.refuse || 0;

        // Mettre l'heure de la mise à jour
        const now = new Date();
        status.textContent = "Dernière mise à jour : " + now.toLocaleTimeString();
    })
    .catch(error => {
        console.error('Erreur fetch :', error);
        status.textContent = "Erreur lors de la mise à jour";
    });

    function updateDashboard() {
    fetch('update_dashboard.php')
    .then(response => response.json())
    .then(data => {
        if(data.error){
            console.error(data.error);
            return;
        }

        // Mettre à jour les stats
        document.getElementById('presentCount').textContent = data.presence.presents || 0;
        document.getElementById('absentCount').textContent = data.presence.absents || 0;

        document.getElementById('attenteCount').textContent = data.demandes.en_attente || 0;
        document.getElementById('accepteCount').textContent = data.demandes.accepte || 0;
        document.getElementById('refuseCount').textContent = data.demandes.refuse || 0;
    })
    .catch(error => console.error('Erreur fetch :', error));
}

// Actualise toutes les 10 secondes
setInterval(updateDashboard, 10000);

// Lancer la mise à jour dès l'ouverture de la page
updateDashboard();
}

</script>
<div id="status" style="font-size:12px; color:gray;">Dernière mise à jour : jamais</div>





</body>
</html> 