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
        try {
            $sql = $pdo->prepare(
                "DELETE FROM Categorie WHERE Id = ?"
            );

            $sql->execute([$id]);

        } catch (PDOException $e) {
            // السبب غالباً أن catégorie مرتبطة بمنتجات
            if ($e->errorInfo[1] == 1451) {
                header(
                    'Location: /ecomerce/categori.php?error=related'
                );
                exit;
            }

            die("Erreur lors de la suppression.");
        }
    }
}

header('Location: /ecomerce/categori.php');
exit;
