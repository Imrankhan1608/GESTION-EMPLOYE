<?php
header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo json_encode(["error" => "Erreur BD"]);
    exit();
}

// Compter les présents et absents
$presenceQuery = $conn->query("
    SELECT 
        SUM(CASE WHEN a.statut='accepté' AND CURDATE() BETWEEN a.date_debut AND a.date_fin THEN 1 ELSE 0 END) AS absents,
        SUM(CASE WHEN a.statut IS NULL OR NOT (CURDATE() BETWEEN a.date_debut AND a.date_fin) THEN 1 ELSE 0 END) AS presents
    FROM employes e
    LEFT JOIN absences a ON e.id = a.employe_id
");
$presenceStats = $presenceQuery->fetch(PDO::FETCH_ASSOC);

// Compter les demandes
$demandesQuery = $conn->query("
    SELECT 
        SUM(CASE WHEN statut='en attente' THEN 1 ELSE 0 END) AS en_attente,
        SUM(CASE WHEN statut='accepté' THEN 1 ELSE 0 END) AS accepte,
        SUM(CASE WHEN statut='refusé' THEN 1 ELSE 0 END) AS refuse
    FROM absences
");
$demandesStats = $demandesQuery->fetch(PDO::FETCH_ASSOC);

// Retourner les résultats en JSON
echo json_encode([
    "presence" => $presenceStats,
    "demandes" => $demandesStats
]);
