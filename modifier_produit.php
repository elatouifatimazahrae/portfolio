<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: /ecomerce/connexion.php');
    exit;
}

require_once "include/data.php";

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: /ecomerce/produit.php');
    exit;
}

$message = '';
$messageType = '';

/*
|--------------------------------------------------------------------------
| Récupérer le produit
|--------------------------------------------------------------------------
*/
$sqlProduit = $pdo->prepare(
    "SELECT * FROM Produit WHERE Id = ?"
);

$sqlProduit->execute([$id]);

$produit = $sqlProduit->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    header('Location: /ecomerce/produit.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Récupérer les catégories
|--------------------------------------------------------------------------
*/
$sqlCategories = $pdo->query(
    "SELECT Id, Libelle
     FROM Categorie
     ORDER BY Libelle ASC"
);

$categories = $sqlCategories->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Modifier le produit
|--------------------------------------------------------------------------
*/
if (isset($_POST['modifier'])) {
    $libelle = trim($_POST['libelle'] ?? '');
    $prix = $_POST['prix'] ?? '';
    $discount = $_POST['discount'] ?? 0;
    $idCategorie = $_POST['id_categorie'] ?? '';

    if (
        empty($libelle) ||
        $prix === '' ||
        empty($idCategorie)
    ) {
        $message = "Tous les champs sont obligatoires.";
        $messageType = "danger";
    } elseif (!is_numeric($prix) || $prix < 0) {
        $message = "Le prix doit être un nombre positif.";
        $messageType = "danger";
    } elseif (
        !is_numeric($discount) ||
        $discount < 0 ||
        $discount > 100
    ) {
        $message = "La remise doit être comprise entre 0 et 100.";
        $messageType = "danger";
    } else {
        $sqlUpdate = $pdo->prepare(
            "UPDATE Produit
             SET Libelle = ?,
                 Prix = ?,
                 Discount = ?,
                 id_categorie = ?
             WHERE Id = ?"
        );

        $sqlUpdate->execute([
            $libelle,
            $prix,
            $discount,
            $idCategorie,
            $id
        ]);

        header('Location: /ecomerce/produit.php');
        exit;
    }

    /*
     * Garder les valeurs saisies dans le formulaire
     * si une erreur survient
     */
    $produit['Libelle'] = $libelle;
    $produit['Prix'] = $prix;
    $produit['Discount'] = $discount;
    $produit['id_categorie'] = $idCategorie;
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

    <title>Modifier le produit</title>

    <style>
        :root {
            --olive: #68743f;
            --olive-dark: #4f592f;
            --gold: #c9a227;
            --text: #333;
            --muted: #777;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: var(--text );
            background: linear-gradient(
                    135deg,
                    rgba(250, 249, 243, 0.96),
                    rgba(244, 231, 173, 0.5)
            );
        }

        .navbar {
            width: 100%;
            min-height: 70px;
            background-color: white !important;
            border-bottom: 3px solid var(--gold);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand {
            color: var(--olive-dark) !important;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .navbar .nav-link {
            margin: 0 5px;
            color: var(--olive-dark) !important;
            font-weight: 600;
            border-radius: 7px;
            transition: 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: white !important;
            background-color: var(--olive);
        }

        .edit-card {
            width: 92%;
            max-width: 600px;
            margin: 55px auto;
            padding: 38px;
            background-color: white;
            border-left: 6px solid var(--gold);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(80, 80, 40, 0.14);
        }

        .page-title {
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 30px;
            font-weight: bold;
            text-align: center;
        }

        .page-subtitle {
            margin-bottom: 30px;
            color: var(--muted);
            font-size: 15px;
            text-align: center;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-weight: bold;
        }

        .form-control,
        .form-select {
            width: 100%;
            min-height: 47px;
            margin-bottom: 20px;
            padding: 11px 13px;
            border: 1px solid #d8d8c8;
            border-radius: 8px;
            outline: none;
            transition: 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--olive);
            box-shadow: 0 0 0 4px rgba(104, 116, 63, 0.15);
        }

        .btn-save {
            width: 100%;
            min-height: 48px;
            padding: 12px;
            color: white;
            background: linear-gradient(
                    135deg,
                    var(--olive),
                    var(--olive-dark)
            );
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-save:hover {
            background: linear-gradient(
                    135deg,
                    var(--gold),
                    #a98418
            );
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(201, 162, 39, 0.3);
        }

        .btn-cancel {
            display: block;
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            color: var(--olive-dark);
            background-color: white;
            border: 1px solid var(--olive);
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .btn-cancel:hover {
            color: white;
            background-color: var(--olive);
        }

        .alert {
            border-radius: 8px;
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .edit-card {
                width: 90%;
                margin: 30px auto;
                padding: 25px 20px;
            }

            .page-title {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<?php include "include/nav.php"; ?>

<main class="edit-card">

    <h1 class="page-title">
        Modifier le produit
    </h1>

    <p class="page-subtitle">
        Modifiez les informations de votre produit.
    </p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <input
            type="hidden"
            name="id"
            value="<?= htmlspecialchars($produit['Id']) ?>"
        >

        <label class="form-label" for="libelle">
            Libellé du produit
        </label>

        <input
            type="text"
            id="libelle"
            name="libelle"
            class="form-control"
            value="<?= htmlspecialchars($produit['Libelle']) ?>"
            required
        >

        <label class="form-label" for="prix">
            Prix
        </label>

        <input
            type="number"
            id="prix"
            name="prix"
            class="form-control"
            value="<?= htmlspecialchars($produit['Prix']) ?>"
            min="0"
            step="0.01"
            required
        >

        <label class="form-label" for="discount">
            Remise (%)
        </label>

        <input
            type="number"
            id="discount"
            name="discount"
            class="form-control"
            value="<?= htmlspecialchars($produit['Discount']) ?>"
            min="0"
            max="100"
            required
        >

        <label class="form-label" for="id_categorie">
            Catégorie
        </label>

        <select
            id="id_categorie"
            name="id_categorie"
            class="form-select"
            required
        >
            <option value="">
                Sélectionner une catégorie
            </option>

            <?php foreach ($categories as $categorie): ?>

                <option
                    value="<?= htmlspecialchars($categorie['Id']) ?>"
                    <?= (
                        $categorie['Id'] ==
                        $produit['id_categorie']
                    ) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($categorie['Libelle']) ?>
                </option>

            <?php endforeach; ?>
        </select>

        <button
            type="submit"
            name="modifier"
            class="btn-save"
        >
            Enregistrer les modifications
        </button>

        <a
            href="/ecomerce/produit.php"
            class="btn-cancel"
        >
            Annuler
        </a>

    </form>

</main>

</body>
</html>
