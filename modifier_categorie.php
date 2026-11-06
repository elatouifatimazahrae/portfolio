<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: /ecomerce/connexion.php');
    exit;
}

require_once "include/data.php";

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: /ecomerce/categori.php');
    exit;
}

$message = '';

$sql = $pdo->prepare(
    "SELECT * FROM Categorie WHERE Id = ?"
);
$sql->execute([$id]);
$categorie = $sql->fetch(PDO::FETCH_ASSOC);

if (!$categorie) {
    header('Location: /ecomerce/categori.php');
    exit;
}

if (isset($_POST['modifier'])) {
    $libelle = trim($_POST['libelle'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($libelle)) {
        $message = "Le libellé est obligatoire.";
    } else {
        $sql = $pdo->prepare(
            "UPDATE Categorie
             SET Libelle = ?, Description = ?
             WHERE Id = ?"
        );

        $sql->execute([
            $libelle,
            $description,
            $id
        ]);

        header('Location: /ecomerce/categori.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <title>Modifier catégorie</title>

    <style>
        :root {
            --olive: #68743f;
            --olive-dark: #4f592f;
            --gold: #c9a227;
        }

        body {
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(
                    135deg,
                    #faf9f3,
                    #f4e7ad
            );
        }

        .edit-card {
            width: 92%;
            max-width: 520px;
            margin: 60px auto;
            padding: 35px;
            background: white;
            border-left: 6px solid var(--gold);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(80, 80, 40, .14);
        }

        h1 {
            margin-bottom: 25px;
            color: var(--olive-dark);
            text-align: center;
        }

        label {
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid #d8d8c8;
            border-radius: 8px;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        .btn-save {
            width: 100%;
            padding: 12px;
            color: white;
            background: var(--olive);
            border: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn-save:hover {
            background: var(--gold);
        }
    </style>
</head>

<body>

<?php include "include/nav.php"; ?>

<div class="edit-card">
    <h1>Modifier la catégorie</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $categorie['Id'] ?>">

        <label for="libelle">Libellé</label>
        <input
            type="text"
            id="libelle"
            name="libelle"
            value="<?= htmlspecialchars($categorie['Libelle']) ?>"
            required
        >

        <label for="description">Description</label>
        <textarea
            id="description"
            name="description"
        ><?= htmlspecialchars($categorie['Description'] ?? '') ?></textarea>

        <button
            type="submit"
            name="modifier"
            class="btn-save"
        >
            Enregistrer les modifications
        </button>
    </form>
</div>

</body>
</html>
