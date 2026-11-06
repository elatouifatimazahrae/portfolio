<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: /ecomerce/connexion.php');
    exit;
}

require_once "include/data.php";

$message = '';
$messageType = '';
$categorieAModifier = null;

/*
|--------------------------------------------------------------------------
| Supprimer une catégorie
|--------------------------------------------------------------------------
*/
if (isset($_POST['supprimer'])) {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            $sql = $pdo->prepare(
                "DELETE FROM Categorie WHERE Id = ?"
            );

            $sql->execute([$id]);

            $message = "Catégorie supprimée avec succès.";
            $messageType = "success";

        } catch (PDOException $e) {
            /*
             * La catégorie ne peut pas être supprimée
             * si elle est utilisée par un produit
             */
            if (
                isset($e->errorInfo[1]) &&
                $e->errorInfo[1] == 1451
            ) {
                $message = "Impossible de supprimer cette catégorie : elle est utilisée par un produit.";
                $messageType = "danger";
            } else {
                $message = "Une erreur est survenue lors de la suppression.";
                $messageType = "danger";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Ajouter une catégorie
|--------------------------------------------------------------------------
*/
if (isset($_POST['ajouter'])) {
    $libelle = trim($_POST['libelle'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($libelle)) {
        $message = "Le libellé de la catégorie est obligatoire.";
        $messageType = "danger";
    } else {
        $sql = $pdo->prepare(
            "INSERT INTO Categorie (Libelle, Description)
             VALUES (?, ?)"
        );

        $sql->execute([
            $libelle,
            $description
        ]);

        $message = "Catégorie ajoutée avec succès.";
        $messageType = "success";
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
            "SELECT * FROM Categorie WHERE Id = ?"
        );

        $sql->execute([$id]);
        $categorieAModifier = $sql->fetch(PDO::FETCH_ASSOC);
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
    $description = trim($_POST['description'] ?? '');

    if ($id <= 0) {
        $message = "Catégorie invalide.";
        $messageType = "danger";
    } elseif (empty($libelle)) {
        $message = "Le libellé de la catégorie est obligatoire.";
        $messageType = "danger";

        $categorieAModifier = [
            'Id' => $id,
            'Libelle' => $libelle,
            'Description' => $description
        ];
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

/*
|--------------------------------------------------------------------------
| Récupérer toutes les catégories
|--------------------------------------------------------------------------
*/
$sqlCategories = $pdo->query(
    "SELECT *
     FROM Categorie
     ORDER BY Id DESC"
);

$categories = $sqlCategories->fetchAll(PDO::FETCH_ASSOC);
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

    <title>Gestion des catégories</title>

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
            max-width: 1150px;
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
            grid-template-columns: 380px 1fr;
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

        .form-control {
            width: 100%;
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid #d8d8c8;
            border-radius: 8px;
            outline: none;
            transition: 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--olive);
            box-shadow: 0 0 0 4px rgba(104, 116, 63, 0.15);
        }

        textarea.form-control {
            min-height: 130px;
            resize: vertical;
        }

        .btn-add,
        .btn-save {
            width: 100%;
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

        .table tbody td {
            color: #555;
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
        }

        .btn-delete:hover {
            background-color: #8f3030;
        }

        .empty-message {
            margin-top: 25px;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 900px) {
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
        Gestion des catégories
    </h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="content-grid">

        <!-- Formulaire -->
        <section class="form-card">

            <?php if ($categorieAModifier): ?>

                <h2 class="card-title">
                    Modifier la catégorie
                </h2>

                <p class="card-subtitle">
                    Modifiez les informations de la catégorie.
                </p>

                <form method="post">

                    <input
                        type="hidden"
                        name="id"
                        value="<?= htmlspecialchars($categorieAModifier['Id']) ?>"
                    >

                    <label class="form-label" for="libelle">
                        Libellé
                    </label>

                    <input
                        type="text"
                        id="libelle"
                        name="libelle"
                        class="form-control"
                        value="<?= htmlspecialchars($categorieAModifier['Libelle']) ?>"
                        required
                    >

                    <label class="form-label" for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                    ><?= htmlspecialchars($categorieAModifier['Description'] ?? '') ?></textarea>

                    <button
                        type="submit"
                        name="enregistrer"
                        class="btn-save"
                    >
                        Enregistrer les modifications
                    </button>

                    <a
                        href="/ecomerce/categori.php"
                        class="btn-cancel"
                    >
                        Annuler
                    </a>

                </form>

            <?php else: ?>

                <h2 class="card-title">
                    Ajouter une catégorie
                </h2>

                <p class="card-subtitle">
                    Créez une nouvelle catégorie pour vos produits.
                </p>

                <form method="post">

                    <label class="form-label" for="libelle">
                        Libellé
                    </label>

                    <input
                        type="text"
                        id="libelle"
                        name="libelle"
                        class="form-control"
                        placeholder="Exemple : Ordinateurs"
                        required
                    >

                    <label class="form-label" for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        placeholder="Description de la catégorie..."
                    ></textarea>

                    <button
                        type="submit"
                        name="ajouter"
                        class="btn-add"
                    >
                        Ajouter la catégorie
                    </button>

                </form>

            <?php endif; ?>

        </section>

        <!-- Tableau -->
        <section class="table-card">

            <h2 class="card-title">
                Liste des catégories
            </h2>

            <div class="table-wrapper">

                <?php if (count($categories) > 0): ?>

                    <table class="table table-bordered">

                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Libellé</th>
                            <th>Description</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($categories as $categorie): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars($categorie['Id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($categorie['Libelle']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $categorie['Description'] ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $categorie['date_creation']
                                    ) ?>
                                </td>

                                <td>
                                    <div class="actions">

                                        <a
                                            href="/ecomerce/categori.php?modifier=<?= $categorie['Id'] ?>"
                                            class="btn-edit"
                                        >
                                            Modifier
                                        </a>

                                        <form
                                            method="post"
                                            onsubmit="return confirm('Voulez-vous supprimer cette catégorie ?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= $categorie['Id'] ?>"
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
                        Aucune catégorie enregistrée pour le moment.
                    </p>

                <?php endif; ?>

            </div>
        </section>

    </div>
</main>

</body>
</html>
