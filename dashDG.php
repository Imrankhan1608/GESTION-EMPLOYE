<?php
session_start();
try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Erreur BD : " . $e->getMessage());
}

if(!isset($_SESSION['dg']) || !$_SESSION['dg']){
    header("Location: login.php");
    exit();
}

// Toutes les demandes
$stmt = $conn->query("
    SELECT a.id, e.nom, e.prenom, a.service, a.motif, a.date_debut, a.date_fin, a.nombre_jours, a.statut
    FROM absences a
    JOIN employes e ON a.employe_id = e.id
    ORDER BY a.date_creation DESC
");
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_GET['action'], $_GET['id'])){
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    if(in_array($action,['accepté','refusé'])){
        $upd = $conn->prepare("UPDATE absences SET statut=:statut WHERE id=:id");
        $upd->execute(['statut'=>$action,'id'=>$id]);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
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
<title>Dashboard DG</title>
<style>
body { font-family: 'Segoe UI', Tahoma, sans-serif; background:#f0f9ff; padding:20px; }
h1 { text-align:center; margin-bottom:30px; color:#1e3a8a; }
table { width:100%; border-collapse: collapse; box-shadow:0 4px 10px rgba(0,0,0,0.1); background:#fff; }
th, td { padding:12px 10px; text-align:center; border-bottom:1px solid #ddd; }
th { background:#1e3a8a; color:#fff; }
tr:hover { background:#f3f4f6; }
.status-en_attente { background:#facc15; color:#333; font-weight:bold; }
.status-accepté { background:#4ade80; color:#fff; font-weight:bold; }
.btndeco:hover { opacity:0.85; background: #c82333; }
.btndeco { padding:10px 15px;background:#dc3545; color:#fff; text-decoration:none; border-radius:6px; margin:5px; display:inline-block; }
.status-refusé { background:#f87171; color:#fff; font-weight:bold; }
a.btn { padding:8px 12px; margin:2px; border-radius:4px; text-decoration:none; color:#fff; background:#2563eb; display:inline-block; }
a.btn:hover { opacity:0.85; }
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

<h1>Dashboard DG</h1>
<div style="text-align:center; margin-bottom:20px;">
    <a class="btn" href="presence_absence.php">Voir Présence / Absence</a>
    <a class="btndeco" href="logout.php">Deconnexion</a>
    <a class="btn" href="dossier_employe.php">Reviews</a>
</div>

<table id="table-demandes">
<tr>
<th>Nom</th><th>Prénom</th><th>Service</th><th>Motif</th><th>Début</th><th>Fin</th><th>Jours</th><th>Statut</th><th>Action</th><th>Supprimer</th>
</tr>

<?php foreach($demandes as $d): ?>
<tr>
<td><?= htmlspecialchars($d['nom']) ?></td>
<td><?= htmlspecialchars($d['prenom']) ?></td>
<td><?= htmlspecialchars($d['service']) ?></td>
<td><?= htmlspecialchars($d['motif']) ?></td>
<td><?= htmlspecialchars($d['date_debut']) ?></td>
<td><?= htmlspecialchars($d['date_fin']) ?></td>
<td><?= htmlspecialchars($d['nombre_jours']) ?></td>
<td class="status-<?= str_replace(' ','_',$d['statut']) ?>"><?= htmlspecialchars($d['statut']) ?></td>
<td>
<?php if($d['statut']=='en attente'): ?>
<a class="btn" href="?action=accepté&id=<?= $d['id'] ?>">Accepter</a>
<a class="btn" href="?action=refusé&id=<?= $d['id'] ?>">Refuser</a>
<?php else: ?> - <?php endif; ?>
</td>
<td><button class="remove-btn" onclick="removeRow(this)">Sup</button></td>
</tr>
<?php endforeach; ?>
</table>

<script>
function removeRow(btn){
    const row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
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
}
</script>


<div id="stats">
    <p><strong>Présents :</strong> <span id="presentCount">0</span></p>
    <p><strong>Absents :</strong> <span id="absentCount">0</span></p>
    <p><strong>Demandes en attente :</strong> <span id="attenteCount">0</span></p>
    <p><strong>Demandes acceptées :</strong> <span id="accepteCount">0</span></p>
    <p><strong>Demandes refusées :</strong> <span id="refuseCount">0</span></p>
</div>



<div id="status" style="font-size:12px; color:gray;">Dernière mise à jour : jamais</div>

</body>
</html>
