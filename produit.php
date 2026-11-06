<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: /ecomerce/connexion.php');
    exit;
}

require_once "include/data.php";

$uploadDir = __DIR__ . "/uploads/";
$uploadUrl = "uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function enregistrerImage(array $image, string $uploadDir): ?string
{
    if ($image['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($image['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Erreur lors de l'envoi de l'image.");
    }

    if ($image['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException("L'image ne doit pas dépasser 5 Mo.");
    }

    $typesAutorises = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
    ];

    $type = mime_content_type($image['tmp_name']);

    if (!isset($typesAutorises[$type])) {
        throw new RuntimeException("Format accepté : JPG, PNG ou WEBP.");
    }

    $nom = uniqid("produit_", true) . "." . $typesAutorises[$type];

    if (!move_uploaded_file($image['tmp_name'], $uploadDir . $nom)) {
        throw new RuntimeException("Impossible d'enregistrer l'image.");
    }

    return $nom;
}

$message = '';
$messageType = '';
$produitAModifier = null;

/*
|--------------------------------------------------------------------------
| Ajouter un produit
|--------------------------------------------------------------------------
*/
if (isset($_POST['ajouter'])) {
    $libelle = trim($_POST['libelle'] ?? '');
    $prix = $_POST['prix'] ?? '';
    $discount = $_POST['discount'] ?? 0;
    $idCategorie = $_POST['id_categorie'] ?? '';
    $imageNom = null;

    if (
            empty($libelle) ||
            $prix === '' ||
            empty($idCategorie)
    ) {
        $message = "Le libellé, le prix et la catégorie sont obligatoires.";
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
    } elseif (
            !isset($_FILES['image']) ||
            $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        $message = "Veuillez choisir une image pour le produit.";
        $messageType = "danger";
    } else {
        try {
            $imageNom = enregistrerImage($_FILES['image'], $uploadDir);

            $sql = $pdo->prepare(
                    "INSERT INTO Produit
                (Libelle, Prix, Discount, id_categorie, image)
                VALUES (?, ?, ?, ?, ?)"
            );

            $sql->execute([
                    $libelle,
                    $prix,
                    $discount,
                    $idCategorie,
                    $imageNom
            ]);

            $message = "Produit ajouté avec succès.";
            $messageType = "success";
        } catch (RuntimeException $e) {
            $message = $e->getMessage();
            $messageType = "danger";
        }
    }
}

/*
|--------------------------------------------------------------------------
| Préparer la modification
|--------------------------------------------------------------------------
*/
if (isset($_GET['modifier'])) {
    $id = (int) $_GET['modifier'];

    if ($id > 0) {
        $sql = $pdo->prepare(
                "SELECT * FROM Produit WHERE Id = ?"
        );

        $sql->execute([$id]);

        $produitAModifier = $sql->fetch(PDO::FETCH_ASSOC);
    }
}

/*
|--------------------------------------------------------------------------
| Enregistrer la modification
|--------------------------------------------------------------------------
*/
if (isset($_POST['enregistrer'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $libelle = trim($_POST['libelle'] ?? '');
    $prix = $_POST['prix'] ?? '';
    $discount = $_POST['discount'] ?? 0;
    $idCategorie = $_POST['id_categorie'] ?? '';

    if (
            $id <= 0 ||
            empty($libelle) ||
            $prix === '' ||
            empty($idCategorie)
    ) {
        $message = "Tous les champs sont obligatoires.";
        $messageType = "danger";

        $produitAModifier = [
                'Id' => $id,
                'Libelle' => $libelle,
                'Prix' => $prix,
                'Discount' => $discount,
                'id_categorie' => $idCategorie
        ];
    } elseif (!is_numeric($prix) || $prix < 0) {
        $message = "Le prix doit être un nombre positif.";
        $messageType = "danger";

        $produitAModifier = [
                'Id' => $id,
                'Libelle' => $libelle,
                'Prix' => $prix,
                'Discount' => $discount,
                'id_categorie' => $idCategorie
        ];
    } elseif (
            !is_numeric($discount) ||
            $discount < 0 ||
            $discount > 100
    ) {
        $message = "La remise doit être comprise entre 0 et 100.";
        $messageType = "danger";

        $produitAModifier = [
                'Id' => $id,
                'Libelle' => $libelle,
                'Prix' => $prix,
                'Discount' => $discount,
                'id_categorie' => $idCategorie
        ];
    } else {
        $sqlAncienneImage = $pdo->prepare(
                "SELECT image FROM Produit WHERE Id = ?"
        );
        $sqlAncienneImage->execute([$id]);
        $ancienneImage = $sqlAncienneImage->fetchColumn();
        $nouvelleImage = $ancienneImage;

        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $nouvelleImage = enregistrerImage($_FILES['image'], $uploadDir);
            }

            $sql = $pdo->prepare(
                    "UPDATE Produit
                 SET Libelle = ?,
                     Prix = ?,
                     Discount = ?,
                     id_categorie = ?,
                     image = ?
                 WHERE Id = ?"
            );

            $sql->execute([
                    $libelle,
                    $prix,
                    $discount,
                    $idCategorie,
                    $nouvelleImage,
                    $id
            ]);

            if ($nouvelleImage !== $ancienneImage && !empty($ancienneImage)) {
                $ancienChemin = $uploadDir . basename($ancienneImage);
                if (is_file($ancienChemin)) {
                    unlink($ancienChemin);
                }
            }

            header('Location: /ecomerce/produit.php');
            exit;
        } catch (RuntimeException $e) {
            $message = $e->getMessage();
            $messageType = "danger";
        }
    }
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
| Récupérer les produits
|--------------------------------------------------------------------------
*/
$sqlProduits = $pdo->query(
        "SELECT
        Produit.Id,
        Produit.Libelle,
        Produit.Prix,
        Produit.Discount,
        Produit.image,
        Produit.date_creation,
        Produit.id_categorie,
        Categorie.Libelle AS categorie
     FROM Produit
     INNER JOIN Categorie
        ON Produit.id_categorie = Categorie.Id
     ORDER BY Produit.Id DESC"
);

$produits = $sqlProduits->fetchAll(PDO::FETCH_ASSOC);
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

    <title>Gestion des produits</title>

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

        .page-wrapper {
            width: 92%;
            max-width: 1250px;
            margin: 50px auto;
        }

        .page-title {
            margin-bottom: 25px;
            color: var(--olive-dark);
            font-size: 32px;
            font-weight: bold;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 370px 1fr;
            gap: 28px;
            align-items: start;
        }

        .form-card,
        .table-card {
            padding: 30px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(80, 80, 40, 0.14);
        }

        .form-card {
            border-left: 6px solid var(--gold);
        }

        .card-title {
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 23px;
            font-weight: bold;
        }

        .card-subtitle {
            margin-bottom: 25px;
            color: var(--muted);
            font-size: 14px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 15px;
            font-weight: bold;
        }

        .form-control,
        .form-select {
            width: 100%;
            min-height: 46px;
            margin-bottom: 19px;
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

        .btn-add,
        .btn-save {
            width: 100%;
            min-height: 47px;
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

        .btn-add:hover,
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
            margin-top: 10px;
            padding: 11px;
            color: var(--olive-dark);
            background-color: white;
            border: 1px solid var(--olive);
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
        }

        .btn-cancel:hover {
            color: white;
            background-color: var(--olive);
        }

        .table-card {
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            margin-top: 20px;
            vertical-align: middle;
        }

        .table thead th {
            color: white;
            background-color: var(--olive);
            border: none;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: #faf9ed;
        }

        .badge-discount {
            color: #725c12;
            background-color: #fff1ae;
        }

        .badge-normal {
            color: #4f592f;
            background-color: #e4efd0;
        }

        .actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .btn-edit {
            padding: 6px 10px;
            color: white;
            background-color: var(--gold);
            border: none;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-edit:hover {
            color: white;
            background-color: #a98418;
        }

        .btn-delete {
            padding: 6px 10px;
            color: white;
            background-color: #b54747;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-delete:hover {
            background-color: #8f3030;
        }

        .empty-message {
            margin-top: 25px;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 950px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .page-wrapper {
                width: 90%;
                margin: 30px auto;
            }

            .form-card,
            .table-card {
                padding: 22px;
            }

            .page-title {
                font-size: 27px;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-edit,
            .btn-delete {
                display: block;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<?php include "include/nav.php"; ?>

<main class="page-wrapper">

    <h1 class="page-title">
        Gestion des produits
    </h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="content-grid">

        <!-- Formulaire -->
        <section class="form-card">

            <?php if ($produitAModifier): ?>

                <h2 class="card-title">
                    Modifier le produit
                </h2>

                <p class="card-subtitle">
                    Modifiez les informations du produit.
                </p>

                <form method="post" enctype="multipart/form-data">

                    <input
                            type="hidden"
                            name="id"
                            value="<?= htmlspecialchars($produitAModifier['Id']) ?>"
                    >

                    <label class="form-label" for="libelle">
                        Libellé du produit
                    </label>

                    <input
                            type="text"
                            id="libelle"
                            name="libelle"
                            class="form-control"
                            value="<?= htmlspecialchars($produitAModifier['Libelle']) ?>"
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
                            value="<?= htmlspecialchars($produitAModifier['Prix']) ?>"
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
                            value="<?= htmlspecialchars($produitAModifier['Discount']) ?>"
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
                                            $produitAModifier['id_categorie']
                                    ) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($categorie['Libelle']) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <label class="form-label" for="image">
                        Nouvelle image (optionnel)
                    </label>

                    <input
                            type="file"
                            id="image"
                            name="image"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp"
                    >

                    <button
                            type="submit"
                            name="enregistrer"
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

            <?php else: ?>

                <h2 class="card-title">
                    Ajouter un produit
                </h2>

                <p class="card-subtitle">
                    Ajoutez un nouveau produit à votre catalogue.
                </p>

                <?php if (count($categories) > 0): ?>

                    <form method="post" enctype="multipart/form-data">

                        <label class="form-label" for="libelle">
                            Libellé du produit
                        </label>

                        <input
                                type="text"
                                id="libelle"
                                name="libelle"
                                class="form-control"
                                placeholder="Exemple : Ordinateur HP"
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
                                placeholder="Exemple : 5999.99"
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
                                value="0"
                                min="0"
                                max="100"
                                required
                        >

                        <label class="form-label" for="image">
                            Image du produit
                        </label>

                        <input
                                type="file"
                                id="image"
                                name="image"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
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
                                >
                                    <?= htmlspecialchars($categorie['Libelle']) ?>
                                </option>

                            <?php endforeach; ?>
                        </select>

                        <button
                                type="submit"
                                name="ajouter"
                                class="btn-add"
                        >
                            Ajouter le produit
                        </button>

                    </form>

                <?php else: ?>

                    <div class="alert alert-warning">
                        Vous devez d'abord ajouter une catégorie.
                    </div>

                    <a
                            href="/ecomerce/categori.php"
                            class="btn btn-outline-success w-100"
                    >
                        Ajouter une catégorie
                    </a>

                <?php endif; ?>

            <?php endif; ?>

        </section>

        <!-- Liste des produits -->
        <section class="table-card">

            <h2 class="card-title">
                Liste des produits
            </h2>

            <div class="table-wrapper">

                <?php if (count($produits) > 0): ?>

                    <table class="table table-bordered">

                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Remise</th>
                            <th>Catégorie</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($produits as $produit): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars($produit['Id']) ?>
                                </td>

                                <td>
                                    <?php if (!empty($produit['image'])): ?>
                                        <img
                                                src="<?= $uploadUrl . htmlspecialchars($produit['image']) ?>"
                                                alt="<?= htmlspecialchars($produit['Libelle']) ?>"
                                                style="width:70px;height:55px;object-fit:cover;border-radius:6px;"
                                        >
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($produit['Libelle']) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                            $produit['Prix'],
                                            2,
                                            ',',
                                            ' '
                                    ) ?>
                                    DH
                                </td>

                                <td>
                                    <?php if ($produit['Discount'] > 0): ?>

                                        <span class="badge badge-discount">
                                            -<?= htmlspecialchars(
                                                    $produit['Discount']
                                            ) ?>%
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-normal">
                                            Aucune
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                            $produit['categorie']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                            $produit['date_creation']
                                    ) ?>
                                </td>

                                <td>
                                    <div class="actions">

                                        <a
                                                href="/ecomerce/produit.php?modifier=<?= $produit['Id'] ?>"
                                                class="btn-edit"
                                        >
                                            Modifier
                                        </a>

                                        <form
                                                method="post"
                                                action="supprimer.php"
                                                onsubmit="return confirm('Voulez-vous supprimer ce produit ?');"
                                        >
                                            <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $produit['Id'] ?>"
                                            >

                                            <button
                                                    type="submit"
                                                    name="supprimer"
                                                    class="btn-delete"
                                            >
                                                Supprimer
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>

                <?php else: ?>

                    <p class="empty-message">
                        Aucun produit enregistré pour le moment.
                    </p>

                <?php endif; ?>

            </div>
        </section>

    </div>
</main>

</body>
</html>
