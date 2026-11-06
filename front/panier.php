<?php
session_start();

require_once "../include/data.php";

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message = '';
$messageType = '';

/*
|--------------------------------------------------------------------------
| Modifier les quantités
|--------------------------------------------------------------------------
*/
if (isset($_POST['modifier_panier'])) {
    $quantites = $_POST['quantite'] ?? [];

    foreach ($quantites as $idProduit => $quantite) {
        $idProduit = (int) $idProduit;
        $quantite = (int) $quantite;

        if ($idProduit <= 0) {
            continue;
        }

        if ($quantite <= 0) {
            unset($_SESSION['panier'][$idProduit]);
        } else {
            $_SESSION['panier'][$idProduit] = $quantite;
        }
    }

    $message = "Panier mis à jour.";
    $messageType = "success";
}

/*
|--------------------------------------------------------------------------
| Supprimer un produit
|--------------------------------------------------------------------------
*/
if (isset($_POST['supprimer_produit'])) {
    $idProduit = (int) $_POST['supprimer_produit'];

    if ($idProduit > 0) {
        unset($_SESSION['panier'][$idProduit]);

        $message = "Produit supprimé du panier.";
        $messageType = "success";
    }
}

/*
|--------------------------------------------------------------------------
| Vider le panier
|--------------------------------------------------------------------------
*/
if (isset($_POST['vider_panier'])) {
    $_SESSION['panier'] = [];

    $message = "Votre panier a été vidé.";
    $messageType = "success";
}

/*
|--------------------------------------------------------------------------
| Récupérer les produits du panier
|--------------------------------------------------------------------------
*/
$produitsPanier = [];
$total = 0;

if (!empty($_SESSION['panier'])) {
    $ids = array_keys($_SESSION['panier']);

    $placeholders = implode(
        ',',
        array_fill(0, count($ids), '?')
    );

    $sql = $pdo->prepare(
        "SELECT
            Produit.Id,
            Produit.Libelle,
            Produit.Prix,
            Produit.Discount,
            Categorie.Libelle AS categorie
         FROM Produit
         INNER JOIN Categorie
            ON Produit.id_categorie = Categorie.Id
         WHERE Produit.Id IN ($placeholders)
         ORDER BY Produit.Id DESC"
    );

    $sql->execute($ids);

    $produitsPanier = $sql->fetchAll(PDO::FETCH_ASSOC);
}

$totalArticles = array_sum($_SESSION['panier']);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mon panier - ECOMERCE</title>

    <style>
        :root {
            --olive: #68743f;
            --olive-dark: #4f592f;
            --olive-light: #edf1df;
            --gold: #c9a227;
            --gold-light: #f8efc9;
            --cream: #faf9f3;
            --text: #30352a;
            --muted: #777;
            --danger: #b54747;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background: linear-gradient(
                    135deg,
                    var(--cream),
                    var(--gold-light)
            );
            font-family: Arial, sans-serif;
        }

        /* Navbar */
        .front-navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: var(--white);
            border-bottom: 3px solid var(--gold);
            box-shadow: 0 3px 18px rgba(70, 80, 40, 0.1);
        }

        .navbar-content {
            width: 92%;
            max-width: 1250px;
            min-height: 72px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .brand {
            color: var(--olive-dark);
            font-size: 25px;
            font-weight: bold;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .brand span {
            color: var(--gold);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a {
            padding: 9px 14px;
            color: var(--olive-dark);
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .nav-links a:hover {
            color: white;
            background-color: var(--olive);
        }

        .cart-count {
            display: inline-flex;
            min-width: 22px;
            height: 22px;
            align-items: center;
            justify-content: center;
            margin-left: 4px;
            padding: 2px 6px;
            color: white;
            background-color: var(--danger);
            border-radius: 50%;
            font-size: 12px;
        }

        /* Page */
        .page-wrapper {
            width: 92%;
            max-width: 1200px;
            margin: 50px auto 70px;
        }

        .page-title {
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 34px;
            font-weight: bold;
        }

        .page-subtitle {
            margin-bottom: 25px;
            color: var(--muted);
            font-size: 16px;
        }

        /* Message */
        .message {
            margin-bottom: 22px;
            padding: 14px 18px;
            color: var(--olive-dark);
            background-color: var(--olive-light);
            border-left: 4px solid var(--olive);
            border-radius: 8px;
            font-weight: bold;
        }

        /* Cart */
        .cart-card {
            overflow: hidden;
            background-color: white;
            border-radius: 17px;
            box-shadow: 0 8px 25px rgba(80, 80, 40, 0.13);
        }

        .cart-table-wrapper {
            overflow-x: auto;
        }

        .cart-table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        .cart-table th {
            padding: 17px;
            color: white;
            background-color: var(--olive);
            text-align: left;
            white-space: nowrap;
        }

        .cart-table td {
            padding: 19px 17px;
            border-bottom: 1px solid #edf0e4;
            vertical-align: middle;
        }

        .cart-table tbody tr:hover {
            background-color: #fcfcf6;
        }

        .product-name {
            color: var(--olive-dark);
            font-weight: bold;
        }

        .product-category {
            margin-top: 5px;
            color: var(--gold);
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .price {
            color: var(--olive-dark);
            font-weight: bold;
            white-space: nowrap;
        }

        .old-price {
            margin-left: 5px;
            color: #999;
            font-size: 12px;
            text-decoration: line-through;
            white-space: nowrap;
        }

        /* Quantité */
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quantity-button {
            width: 30px;
            height: 30px;
            color: white;
            background-color: var(--olive);
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }

        .quantity-button:hover {
            background-color: var(--gold);
        }

        .quantity-input {
            width: 65px;
            padding: 8px;
            border: 1px solid #d8d8c8;
            border-radius: 7px;
            text-align: center;
            outline: none;
        }

        .quantity-input:focus {
            border-color: var(--olive);
            box-shadow: 0 0 0 3px rgba(104, 116, 63, 0.14);
        }

        /* Buttons */
        .btn-delete {
            padding: 8px 11px;
            color: white;
            background-color: var(--danger);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-delete:hover {
            background-color: #8f3030;
        }

        .cart-footer {
            padding: 25px;
            background-color: #fcfcf7;
        }

        .cart-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .left-actions,
        .right-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-main,
        .btn-outline,
        .btn-empty {
            display: inline-block;
            padding: 11px 17px;
            border-radius: 7px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-main {
            color: white;
            background-color: var(--olive);
            border: none;
        }

        .btn-main:hover {
            color: white;
            background-color: var(--gold);
        }

        .btn-outline {
            color: var(--olive-dark);
            background-color: white;
            border: 1px solid var(--olive);
        }

        .btn-outline:hover {
            color: white;
            background-color: var(--olive);
        }

        .btn-empty {
            color: white;
            background-color: var(--danger);
            border: none;
        }

        .btn-empty:hover {
            background-color: #8f3030;
        }

        .total-box {
            margin-top: 22px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--olive-dark);
            background-color: var(--olive-light);
            border-radius: 10px;
            font-size: 22px;
            font-weight: bold;
        }

        .total-value {
            color: var(--gold);
            white-space: nowrap;
        }

        .order-action {
            margin-top: 20px;
        }

        /* Empty panier */
        .empty-cart {
            padding: 75px 25px;
            text-align: center;
        }

        .empty-icon {
            margin-bottom: 18px;
            color: var(--gold);
            font-size: 60px;
        }

        .empty-cart h2 {
            margin-bottom: 10px;
            color: var(--olive-dark);
        }

        .empty-cart p {
            margin-bottom: 25px;
            color: var(--muted);
        }

        @media (max-width: 800px) {
            .nav-links a {
                padding: 7px 8px;
                font-size: 13px;
            }

            .page-title {
                font-size: 29px;
            }
        }

        @media (max-width: 560px) {
            .navbar-content {
                width: 94%;
            }

            .brand {
                font-size: 21px;
            }

            .nav-links {
                gap: 0;
            }

            .nav-links a {
                padding: 6px 5px;
                font-size: 11px;
            }

            .page-wrapper {
                width: 94%;
                margin-top: 28px;
            }

            .cart-actions,
            .left-actions,
            .right-actions {
                width: 100%;
                align-items: stretch;
                flex-direction: column;
            }

            .btn-main,
            .btn-outline,
            .btn-empty {
                width: 100%;
                text-align: center;
            }

            .total-box {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
                font-size: 19px;
            }
        }
    </style>
</head>

<body>

<header class="front-navbar">

    <div class="navbar-content">

        <a href="index.php" class="brand">
            ECO<span>MERCE</span>
        </a>

        <nav class="nav-links">

            <a href="index.php">
                Accueil
            </a>

            <a href="index.php#categories">
                Catégories
            </a>

            <a href="index.php#produits">
                Produits
            </a>

            <a href="panier.php">
                Panier
                <span class="cart-count">
                    <?= $totalArticles ?>
                </span>
            </a>

        </nav>

    </div>

</header>

<main class="page-wrapper">

    <h1 class="page-title">
        Mon panier
    </h1>

    <p class="page-subtitle">
        Vérifiez vos produits avant de confirmer votre commande.
    </p>

    <?php if (!empty($message)): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <?php if (count($produitsPanier) > 0): ?>

        <section class="cart-card">

            <form method="post" id="panierForm">

                <div class="cart-table-wrapper">

                    <table class="cart-table">

                        <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                            <th>Action</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($produitsPanier as $produit): ?>

                            <?php
                            $idProduit = (int) $produit['Id'];

                            $quantite = (int) (
                                $_SESSION['panier'][$idProduit] ?? 1
                            );

                            $prix = (float) $produit['Prix'];
                            $discount = (int) $produit['Discount'];

                            $prixFinal = $prix;

                            if ($discount > 0) {
                                $prixFinal = $prix - (
                                        $prix * $discount / 100
                                    );
                            }

                            $sousTotal = $prixFinal * $quantite;
                            $total += $sousTotal;
                            ?>

                            <tr>

                                <td>
                                    <div class="product-name">
                                        <?= htmlspecialchars(
                                            $produit['Libelle']
                                        ) ?>
                                    </div>

                                    <div class="product-category">
                                        <?= htmlspecialchars(
                                            $produit['categorie']
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="price">
                                        <?= number_format(
                                            $prixFinal,
                                            2,
                                            ',',
                                            ' '
                                        ) ?>
                                        DH
                                    </span>

                                    <?php if ($discount > 0): ?>

                                        <span class="old-price">
                                            <?= number_format(
                                                $prix,
                                                2,
                                                ',',
                                                ' '
                                            ) ?>
                                            DH
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="quantity-control">

                                        <button
                                            type="button"
                                            class="quantity-button"
                                            onclick="changerQuantite(this, -1)"
                                        >
                                            −
                                        </button>

                                        <input
                                            type="number"
                                            name="quantite[<?= $idProduit ?>]"
                                            value="<?= $quantite ?>"
                                            min="1"
                                            class="quantity-input"
                                            onchange="mettreAJourPanier()"
                                        >

                                        <button
                                            type="button"
                                            class="quantity-button"
                                            onclick="changerQuantite(this, 1)"
                                        >
                                            +
                                        </button>

                                    </div>
                                </td>

                                <td>
                                    <span class="price">
                                        <?= number_format(
                                            $sousTotal,
                                            2,
                                            ',',
                                            ' '
                                        ) ?>
                                        DH
                                    </span>
                                </td>

                                <td>
                                    <button
                                        type="submit"
                                        name="supprimer_produit"
                                        value="<?= $idProduit ?>"
                                        class="btn-delete"
                                        onclick="return confirmerSuppression();"
                                    >
                                        Supprimer
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="cart-footer">

                    <div class="cart-actions">

                        <div class="left-actions">

                            <button
                                type="submit"
                                name="modifier_panier"
                                class="btn-main"
                            >
                                Mettre à jour
                            </button>

                            <a
                                href="index.php"
                                class="btn-outline"
                            >
                                Continuer mes achats
                            </a>

                        </div>

                        <div class="right-actions">

                            <button
                                type="submit"
                                name="vider_panier"
                                class="btn-empty"
                                onclick="return confirm(
                                    'Voulez-vous vider le panier ?'
                                );"
                            >
                                Vider le panier
                            </button>

                        </div>

                    </div>

                    <div class="total-box">

                        <span>
                            Total du panier
                        </span>

                        <span class="total-value">
                            <?= number_format(
                                $total,
                                2,
                                ',',
                                ' '
                            ) ?>
                            DH
                        </span>

                    </div>

                    <div class="order-action">

                        <a
                            href="commande.php"
                            class="btn-main"
                        >
                            Passer la commande
                        </a>

                    </div>

                </div>

            </form>

        </section>

    <?php else: ?>

        <section class="cart-card">

            <div class="empty-cart">

                <div class="empty-icon">
                    🛒
                </div>

                <h2>
                    Votre panier est vide
                </h2>

                <p>
                    Ajoutez des produits pour commencer vos achats.
                </p>

                <a
                    href="index.php"
                    class="btn-main"
                >
                    Découvrir les produits
                </a>

            </div>

        </section>

    <?php endif; ?>

</main>

<script>
    function mettreAJourPanier() {
        const form = document.getElementById('panierForm');

        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = 'modifier_panier';
        input.value = '1';

        form.appendChild(input);
        form.submit();
    }

    function changerQuantite(button, valeur) {
        const control = button.parentElement;
        const input = control.querySelector('.quantity-input');

        let quantite = parseInt(input.value) || 1;

        quantite += valeur;

        if (quantite < 1) {
            quantite = 1;
        }

        input.value = quantite;

        mettreAJourPanier();
    }

    function confirmerSuppression() {
        return confirm(
            'Voulez-vous supprimer ce produit du panier ?'
        );
    }
</script>

</body>
</html>
