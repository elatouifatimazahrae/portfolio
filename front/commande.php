<?php
session_start();

require_once "../include/data.php";

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message = '';
$messageType = '';
$commandeValidee = false;
$numeroCommande = null;

if (empty($_SESSION['panier'])) {
    header('Location: /ecomerce/front/panier.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Récupérer les produits du panier
|--------------------------------------------------------------------------
*/
$ids = array_keys($_SESSION['panier']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = $pdo->prepare(
    "SELECT
        Produit.Id,
        Produit.Libelle,
        Produit.Prix,
        Produit.Discount
     FROM Produit
     WHERE Produit.Id IN ($placeholders)"
);

$sql->execute($ids);

$produits = $sql->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$lignesCommande = [];

foreach ($produits as $produit) {
    $idProduit = (int) $produit['Id'];
    $quantite = (int) ($_SESSION['panier'][$idProduit] ?? 0);

    if ($quantite <= 0) {
        continue;
    }

    $prix = (float) $produit['Prix'];
    $discount = (int) $produit['Discount'];

    $prixFinal = $prix;

    if ($discount > 0) {
        $prixFinal = $prix - ($prix * $discount / 100);
    }

    $sousTotal = $prixFinal * $quantite;
    $total += $sousTotal;

    $lignesCommande[] = [
        'id_produit' => $idProduit,
        'libelle' => $produit['Libelle'],
        'prix' => $prixFinal,
        'quantite' => $quantite,
        'sous_total' => $sousTotal
    ];
}

/*
|--------------------------------------------------------------------------
| Confirmer la commande
|--------------------------------------------------------------------------
*/
if (isset($_POST['confirmer_commande'])) {
    $nomClient = trim($_POST['nom_client'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    if (
        empty($nomClient) ||
        empty($telephone) ||
        empty($adresse)
    ) {
        $message = "Tous les champs sont obligatoires.";
        $messageType = "danger";
    } elseif (empty($lignesCommande)) {
        $message = "Votre panier est vide.";
        $messageType = "danger";
    } else {
        try {
            $pdo->beginTransaction();

            $idUtilisateur = null;

            if (isset($_SESSION['utilisateur'])) {
                $idUtilisateur = (int) (
                    $_SESSION['utilisateur']['Id']
                    ?? $_SESSION['utilisateur']['id']
                    ?? 0
                );

                if ($idUtilisateur <= 0) {
                    $idUtilisateur = null;
                }
            }

            $sqlCommande = $pdo->prepare(
                "INSERT INTO Commande
                (
                    id_utilisateur,
                    nom_client,
                    telephone,
                    adresse,
                    total
                )
                VALUES (?, ?, ?, ?, ?)"
            );

            $sqlCommande->execute([
                $idUtilisateur,
                $nomClient,
                $telephone,
                $adresse,
                $total
            ]);

            $idCommande = $pdo->lastInsertId();

            $sqlDetail = $pdo->prepare(
                "INSERT INTO Detail_Commande
                (
                    id_commande,
                    id_produit,
                    libelle_produit,
                    prix,
                    quantite,
                    sous_total
                )
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($lignesCommande as $ligne) {
                $sqlDetail->execute([
                    $idCommande,
                    $ligne['id_produit'],
                    $ligne['libelle'],
                    $ligne['prix'],
                    $ligne['quantite'],
                    $ligne['sous_total']
                ]);
            }

            $pdo->commit();

            $_SESSION['panier'] = [];

            $commandeValidee = true;
            $numeroCommande = $idCommande;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = "Une erreur est survenue lors de la validation de la commande.";
            $messageType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Passer la commande - ECOMERCE</title>

    <style>
        :root {
            --olive: #68743f;
            --olive-dark: #4f592f;
            --gold: #c9a227;
            --cream: #faf9f3;
            --text: #30352a;
            --muted: #777;
            --danger: #b54747;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 35px 0;
            color: var(--text);
            background: linear-gradient(
                    135deg,
                    var(--cream),
                    #f4e7ad
            );
            font-family: Arial, sans-serif;
        }

        .page-wrapper {
            width: 92%;
            max-width: 1100px;
            margin: auto;
        }

        .page-title {
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 34px;
            font-weight: bold;
        }

        .page-subtitle {
            margin-bottom: 30px;
            color: var(--muted);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 28px;
            align-items: start;
        }

        .card {
            padding: 30px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(80, 80, 40, 0.13);
        }

        .card-title {
            margin-bottom: 22px;
            color: var(--olive-dark);
            font-size: 23px;
            font-weight: bold;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            margin-bottom: 20px;
            padding: 13px;
            border: 1px solid #d8d8c8;
            border-radius: 8px;
            outline: none;
            font-family: inherit;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: var(--olive);
            box-shadow: 0 0 0 4px rgba(104, 116, 63, .15);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn-confirm {
            width: 100%;
            padding: 13px;
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
        }

        .btn-confirm:hover {
            background: var(--gold);
        }

        .btn-back {
            display: block;
            margin-top: 12px;
            padding: 12px;
            color: var(--olive-dark);
            border: 1px solid var(--olive);
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 13px 0;
            border-bottom: 1px solid #edf0e4;
        }

        .summary-line:last-child {
            border-bottom: none;
        }

        .summary-name {
            color: var(--olive-dark);
            font-weight: bold;
        }

        .summary-quantity {
            color: var(--muted);
            font-size: 13px;
        }

        .total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding: 17px;
            color: var(--olive-dark);
            background-color: #edf1df;
            border-radius: 8px;
            font-size: 20px;
            font-weight: bold;
        }

        .alert {
            margin-bottom: 20px;
            padding: 14px;
            border-radius: 8px;
        }

        .alert-danger {
            color: #842029;
            background-color: #fce8e8;
            border: 1px solid #efb8bd;
        }

        .success-card {
            max-width: 650px;
            margin: 60px auto;
            padding: 50px 30px;
            background-color: white;
            border-top: 6px solid var(--gold);
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(80, 80, 40, .13);
        }

        .success-icon {
            margin-bottom: 18px;
            color: var(--olive);
            font-size: 55px;
        }

        .success-card h1 {
            color: var(--olive-dark);
        }

        .success-card p {
            color: var(--muted);
            font-size: 17px;
        }

        .success-card a {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 20px;
            color: white;
            background-color: var(--olive);
            border-radius: 8px;
            text-decoration: none;
        }

        @media (max-width: 800px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {
            .page-wrapper {
                width: 94%;
            }

            .card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<main class="page-wrapper">

    <?php if ($commandeValidee): ?>

        <section class="success-card">

            <div class="success-icon">
                ✓
            </div>

            <h1>
                Commande confirmée
            </h1>

            <p>
                Merci pour votre commande.
            </p>

            <p>
                Votre numéro de commande est :
                <strong>#<?= htmlspecialchars($numeroCommande) ?></strong>
            </p>

            <a href="index.php">
                Retourner à la boutique
            </a>

        </section>

    <?php else: ?>

        <h1 class="page-title">
            Passer la commande
        </h1>

        <p class="page-subtitle">
            Remplissez vos informations pour recevoir votre commande.
        </p>

        <?php if (!empty($message)): ?>

            <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

        <div class="content-grid">

            <section class="card">

                <h2 class="card-title">
                    Informations de livraison
                </h2>

                <form method="post">

                    <label
                        class="form-label"
                        for="nom_client"
                    >
                        Nom complet
                    </label>

                    <input
                        type="text"
                        id="nom_client"
                        name="nom_client"
                        class="form-control"
                        placeholder="Exemple : Fati Zahra"
                        required
                    >

                    <label
                        class="form-label"
                        for="telephone"
                    >
                        Numéro de téléphone
                    </label>

                    <input
                        type="tel"
                        id="telephone"
                        name="telephone"
                        class="form-control"
                        placeholder="Exemple : 0612345678"
                        required
                    >

                    <label
                        class="form-label"
                        for="adresse"
                    >
                        Adresse de livraison
                    </label>

                    <textarea
                        id="adresse"
                        name="adresse"
                        class="form-control"
                        placeholder="Ville, quartier, rue..."
                        required
                    ></textarea>

                    <button
                        type="submit"
                        name="confirmer_commande"
                        class="btn-confirm"
                    >
                        Confirmer la commande
                    </button>

                    <a
                        href="panier.php"
                        class="btn-back"
                    >
                        Retour au panier
                    </a>

                </form>

            </section>

            <section class="card">

                <h2 class="card-title">
                    Résumé de la commande
                </h2>

                <?php foreach ($lignesCommande as $ligne): ?>

                    <div class="summary-line">

                        <div>
                            <div class="summary-name">
                                <?= htmlspecialchars(
                                    $ligne['libelle']
                                ) ?>
                            </div>

                            <div class="summary-quantity">
                                Quantité :
                                <?= $ligne['quantite'] ?>
                            </div>
                        </div>

                        <strong>
                            <?= number_format(
                                $ligne['sous_total'],
                                2,
                                ',',
                                ' '
                            ) ?>
                            DH
                        </strong>

                    </div>

                <?php endforeach; ?>

                <div class="total">
                    <span>Total</span>

                    <span>
                        <?= number_format(
                            $total,
                            2,
                            ',',
                            ' '
                        ) ?>
                        DH
                    </span>
                </div>

            </section>

        </div>

    <?php endif; ?>

</main>

</body>
</html>
