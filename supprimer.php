<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: /ecomerce/connexion.php');
    exit;
}

require_once "include/data.php";

$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /ecomerce/produit.php');
    exit;
}

// On récupère d'abord le nom de l'image du produit.
$sql = $pdo->prepare("SELECT image FROM Produit WHERE Id = ?");
$sql->execute([$id]);
$image = $sql->fetchColumn();

// Suppression du produit dans la base de données.
$sql = $pdo->prepare("DELETE FROM Produit WHERE Id = ?");
$sql->execute([$id]);

// Suppression de l'image dans /uploads.
if (!empty($image)) {
    $cheminImage = __DIR__ . "/uploads/" . basename($image);

    if (is_file($cheminImage)) {
        unlink($cheminImage);
    }
}

header('Location: /ecomerce/produit.php');
exit;
?>
