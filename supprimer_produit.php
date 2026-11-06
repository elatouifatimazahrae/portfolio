<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: /ecomerce/connexion.php');
    exit;
}

require_once "include/data.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        $sql = $pdo->prepare(
            "DELETE FROM Produit WHERE Id = ?"
        );

        $sql->execute([$id]);
    }
}

header('Location: /ecomerce/produit.php');
exit;
