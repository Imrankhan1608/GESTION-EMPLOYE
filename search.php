<?php
header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=localhost;dbname=PNB_EMP", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo json_encode([]);
    exit();
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if($q === ''){
    echo json_encode([]);
    exit();
}

$stmt = $conn->prepare("SELECT nom, prenom FROM employes WHERE nom LIKE :q OR prenom LIKE :q LIMIT 10");
$stmt->execute(['q' => "%$q%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
?>
