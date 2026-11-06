<?php
session_start();

require_once "../include/data.php";

/*
|--------------------------------------------------------------------------
| Initialiser le panier
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message = '';

/*
|--------------------------------------------------------------------------
| Ajouter un produit au panier
|--------------------------------------------------------------------------
*/
if (isset($_POST['ajouter_panier'])) {
    $idProduit = (int) ($_POST['id_produit'] ?? 0);

    if ($idProduit > 0) {
        if (isset($_SESSION['panier'][$idProduit])) {
            $_SESSION['panier'][$idProduit]++;
        } else {
            $_SESSION['panier'][$idProduit] = 1;
        }

        $message = "Produit ajouté au panier.";
    }
}

/*
|--------------------------------------------------------------------------
| Nombre d'articles dans le panier
|--------------------------------------------------------------------------
*/
$nombrePanier = array_sum($_SESSION['panier']);

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

    <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
    >

    <title>ECOMERCE - Accueil</title>

    <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
    >

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
            --white: #ffffff;
            --danger: #b54747;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            color: var(--text );
            background-color: var(--cream);
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
            gap: 7px;
        }

        .nav-links a {
            padding: 9px 13px;
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

        .nav-login {
            color: white !important;
            background-color: var(--olive);
        }

        .nav-login:hover {
            background-color: var(--gold) !important;
        }

        .cart-link {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .cart-count {
            min-width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 6px;
            color: white;
            background-color: var(--danger);
            border-radius: 50%;
            font-size: 12px;
            font-weight: bold;
        }

        .menu-button {
            display: none;
            padding: 8px 12px;
            color: var(--olive-dark);
            background-color: white;
            border: 1px solid var(--olive);
            border-radius: 7px;
            font-size: 20px;
        }

        /* Message */
        .shop-message {
            width: 92%;
            max-width: 1250px;
            margin: 18px auto -8px;
            padding: 13px 18px;
            color: var(--olive-dark);
            background-color: var(--olive-light);
            border-left: 4px solid var(--olive);
            border-radius: 8px;
            font-weight: bold;
        }

        /* Hero */
        .hero {
            width: 92%;
            max-width: 1250px;
            min-height: 390px;
            margin: 35px auto 0;
            padding: 55px 65px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            overflow: hidden;
            background:
                    linear-gradient(
                            115deg,
                            rgba(79, 89, 47, 0.98),
                            rgba(104, 116, 63, 0.91)
                    );
            border-radius: 24px;
            box-shadow: 0 12px 30px rgba(70, 80, 40, 0.2);
        }

        .hero-content {
            max-width: 650px;
            color: white;
        }

        .hero-label {
            margin-bottom: 15px;
            color: var(--gold-light);
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .hero h1 {
            margin-bottom: 18px;
            font-size: 48px;
            line-height: 1.12;
        }

        .hero h1 span {
            color: var(--gold-light);
        }

        .hero p {
            max-width: 550px;
            margin-bottom: 28px;
            color: rgba(255, 255, 255, 0.84);
            font-size: 17px;
            line-height: 1.7;
        }

        .hero-button {
            display: inline-block;
            padding: 13px 23px;
            color: var(--olive-dark);
            background-color: white;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .hero-button:hover {
            color: white;
            background-color: var(--gold);
            transform: translateY(-2px);
        }

        .hero-shape {
            width: 270px;
            height: 270px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--gold-light);
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            font-size: 70px;
            font-weight: bold;
        }

        /* Sections */
        .section {
            width: 92%;
            max-width: 1250px;
            margin: 55px auto 0;
        }

        .section-heading {
            margin-bottom: 22px;
        }

        .section-heading h2 {
            margin-bottom: 6px;
            color: var(--olive-dark);
            font-size: 29px;
        }

        .section-heading p {
            color: var(--muted);
        }

        /* Categories */
        .categories-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .category-pill {
            display: inline-block;
            padding: 12px 19px;
            color: var(--olive-dark);
            background-color: white;
            border: 1px solid #dce2c8;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .category-pill:hover {
            color: white;
            background-color: var(--olive);
            border-color: var(--olive);
            transform: translateY(-2px);
        }

        /* Products */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }

        .product-card {
            overflow: hidden;
            background-color: white;
            border: 1px solid #edf0e4;
            border-radius: 15px;
            box-shadow: 0 7px 20px rgba(70, 80, 40, 0.09);
            transition: 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 13px 28px rgba(70, 80, 40, 0.17);
        }

        .product-visual {
            position: relative;
            height: 220px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--olive-dark);
            background:
                    linear-gradient(
                            135deg,
                            var(--olive-light),
                            var(--gold-light)
                    );
            font-size: 20px;
            font-weight: bold;
        }

        .product-image {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .no-image {
            padding: 17px 14px;
            color: var(--olive-dark);
            border: 2px solid rgba(79, 89, 47, 0.3);
            border-radius: 10px;
            letter-spacing: 1px;
        }

        .discount-badge {
            position: absolute;
            top: 13px;
            right: 13px;
            padding: 6px 9px;
            color: white;
            background-color: var(--danger);
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .product-body {
            padding: 19px;
        }

        .product-category {
            margin-bottom: 8px;
            color: var(--gold);
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .product-name {
            min-height: 48px;
            margin-bottom: 13px;
            color: var(--olive-dark);
            font-size: 18px;
            font-weight: bold;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 17px;
        }

        .current-price {
            color: var(--olive-dark);
            font-size: 20px;
            font-weight: bold;
        }

        .old-price {
            color: #999;
            font-size: 13px;
            text-decoration: line-through;
        }

        .add-cart-form {
            margin: 0;
        }

        .product-button {
            width: 100%;
            padding: 10px;
            color: white;
            background-color: var(--olive);
            border: none;
            border-radius: 7px;
            font-family: inherit;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .product-button:hover {
            color: white;
            background-color: var(--gold);
        }

        /* Empty */
        .empty-state {
            padding: 45px 20px;
            color: var(--muted);
            background-color: white;
            border-radius: 14px;
            text-align: center;
        }

        /* Features */
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 65px;
        }

        .feature {
            padding: 24px;
            background-color: white;
            border-radius: 13px;
            text-align: center;
            box-shadow: 0 5px 17px rgba(70, 80, 40, 0.07);
        }

        .feature-icon {
            margin-bottom: 12px;
            color: var(--gold);
            font-size: 32px;
            font-weight: bold;
        }

        .feature h3 {
            margin-bottom: 8px;
            color: var(--olive-dark);
            font-size: 17px;
        }

        .feature p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        /* Footer */
        footer {
            padding: 28px 20px;
            color: rgba(255, 255, 255, 0.78);
            background-color: var(--olive-dark);
            text-align: center;
        }

        footer strong {
            color: var(--gold-light);
        }

        /* Responsive */
        @media (max-width: 1050px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 800px) {
            .navbar-content {
                min-height: 65px;
            }

            .menu-button {
                display: block;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 68px;
                left: 0;
                right: 0;
                padding: 12px 4%;
                flex-direction: column;
                align-items: stretch;
                background-color: white;
                border-bottom: 2px solid var(--gold);
            }

            .nav-links.show {
                display: flex;
            }

            .nav-links a {
                text-align: center;
            }

            .hero {
                padding: 40px 28px;
                text-align: center;
            }

            .hero-content {
                max-width: none;
            }

            .hero h1 {
                font-size: 37px;
            }

            .hero p {
                margin-right: auto;
                margin-left: auto;
            }

            .hero-shape {
                display: none;
            }

            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {
            .hero {
                width: 94%;
                margin-top: 22px;
                padding: 35px 20px;
                border-radius: 17px;
            }

            .section {
                width: 94%;
                margin-top: 40px;
            }

            .hero h1 {
                font-size: 31px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="front-navbar">

    <div class="navbar-content">

        <a href="index.php" class="brand">
            VELORA
        </a>

        <button
                type="button"
                class="menu-button"
                onclick="toggleMenu()"
        >
            ☰
        </button>

        <nav class="nav-links" id="navLinks">

            <a href="index.php">
                Accueil
            </a>

            <a href="#categories">
                Catégories
            </a>

            <a href="#produits">
                Produits
            </a>

            <a href="panier.php" class="cart-link">
                Panier
                <span class="cart-count">
                    <?= $nombrePanier ?>
                </span>
            </a>




        </nav>

    </div>

</header>

<?php if (!empty($message)): ?>

    <div class="shop-message">
        <?= htmlspecialchars($message) ?>
    </div>

<?php endif; ?>

<section class="hero">

    <div class="hero-content">

        <div class="hero-label">
            Bienvenue chez ECOMERCE
        </div>

        <h1>
            Découvrez des produits
            <span>qui vous ressemblent.</span>
        </h1>

        <p>
            Une sélection soigneusement choisie pour vous offrir
            qualité, simplicité et une belle expérience d'achat.
        </p>

        <a href="#produits" class="hero-button">
            Découvrir les produits
        </a>

    </div>

    <div class="hero-shape">
        E
    </div>

</section>

<section class="section" id="categories">

    <div class="section-heading">
        <h2>Nos catégories</h2>
        <p>
            Explorez nos différentes collections.
        </p>
    </div>

    <?php if (count($categories) > 0): ?>

        <div class="categories-list">

            <?php foreach ($categories as $categorie): ?>

                <a
                        href="#produits"
                        class="category-pill"
                >
                    <?= htmlspecialchars(
                            $categorie['Libelle']
                    ) ?>
                </a>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">
            Aucune catégorie disponible pour le moment.
        </div>

    <?php endif; ?>

</section>

<section class="section" id="produits">

    <div class="section-heading">
        <h2>Nos produits</h2>
        <p>
            Les dernières nouveautés disponibles.
        </p>
    </div>

    <?php if (count($produits) > 0): ?>

        <div class="products-grid">

            <?php foreach ($produits as $produit): ?>

                <?php
                $prix = (float) $produit['Prix'];
                $discount = (int) $produit['Discount'];

                $prixFinal = $prix;

                if ($discount > 0) {
                    $prixFinal = $prix - (
                                    $prix * $discount / 100
                            );
                }
                ?>

                <article class="product-card">

                    <div class="product-visual">

                        <?php if (!empty($produit['image'])): ?>

                            <img
                                    src="../uploads/<?= htmlspecialchars($produit['image']) ?>"
                                    alt="<?= htmlspecialchars($produit['Libelle']) ?>"
                                    class="product-image"
                            >

                        <?php else: ?>

                            <span class="no-image">
                                Pas d'image
                            </span>

                        <?php endif; ?>

                        <?php if ($discount > 0): ?>

                            <span class="discount-badge">
                                -<?= htmlspecialchars($discount) ?>%
                            </span>

                        <?php endif; ?>

                    </div>

                    <div class="product-body">

                        <div class="product-category">
                            <?= htmlspecialchars(
                                    $produit['categorie']
                            ) ?>
                        </div>

                        <h3 class="product-name">
                            <?= htmlspecialchars(
                                    $produit['Libelle']
                            ) ?>
                        </h3>

                        <div class="product-price">

                            <span class="current-price">
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

                        </div>

                        <form
                                method="post"
                                class="add-cart-form"
                        >

                            <input
                                    type="hidden"
                                    name="id_produit"
                                    value="<?= htmlspecialchars(
                                            $produit['Id']
                                    ) ?>"
                            >

                            <button
                                    type="submit"
                                    name="ajouter_panier"
                                    class="product-button"
                            >
                                Ajouter au panier
                            </button>

                        </form>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">
            Aucun produit disponible pour le moment.
        </div>

    <?php endif; ?>

</section>

<section class="section">

    <div class="features">

        <div class="feature">
            <div class="feature-icon">✓</div>

            <h3>
                Produits sélectionnés
            </h3>

            <p>
                Une sélection pensée pour répondre à vos besoins.
            </p>
        </div>

        <div class="feature">
            <div class="feature-icon">◆</div>

            <h3>
                Qualité et confiance
            </h3>

            <p>
                Une expérience simple, claire et agréable.
            </p>
        </div>

        <div class="feature">
            <div class="feature-icon">+</div>

            <h3>
                À votre service
            </h3>

            <p>
                Nous sommes là pour vous accompagner.
            </p>
        </div>

    </div>

</section>

<footer>
    © <?= date('Y') ?>
    <strong>VELORA</strong>
    — Tous droits réservés.
</footer>

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('navLinks');

        navLinks.classList.toggle('show');
    }
</script>

</body>
</html>
