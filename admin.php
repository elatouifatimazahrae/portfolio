<?php
session_start();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: /ecomerce/index.php');
    exit;
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

    <title>Admin - ECOMERCE</title>

    <style>
        :root {
            --olive: #68743f;
            --olive-dark: #4f592f;
            --gold: #c9a227;
            --gold-light: #f4e7ad;
            --cream: #faf9f3;
            --text: #333;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: var(--text );
            background:
                    linear-gradient(
                            135deg,
                            rgba(250, 249, 243, 0.95),
                            rgba(244, 231, 173, 0.45)
                    );
        }

        /* Navbar */
        .navbar {
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
            transition: 0.3s;
        }

        .navbar .nav-link:hover {
            color: white !important;
            background-color: var(--olive);
        }

        /* Dashboard */
        .admin-wrapper {
            width: 92%;
            max-width: 1100px;
            margin: 55px auto;
        }

        .welcome-card {
            padding: 45px;
            background-color: white;
            border-left: 7px solid var(--gold);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(80, 80, 40, 0.12);
        }

        .welcome-card h1 {
            margin-bottom: 12px;
            color: var(--olive-dark);
            font-size: 34px;
            font-weight: bold;
        }

        .welcome-card h1 span {
            color: var(--gold);
        }

        .welcome-card p {
            margin-bottom: 0;
            color: #777;
            font-size: 17px;
        }

        /* Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-top: 28px;
        }

        .dashboard-card {
            padding: 28px 22px;
            text-align: center;
            background-color: white;
            border-radius: 14px;
            border-top: 4px solid var(--olive);
            box-shadow: 0 5px 18px rgba(80, 80, 40, 0.1);
            transition: 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            border-top-color: var(--gold);
            box-shadow: 0 10px 25px rgba(80, 80, 40, 0.18);
        }

        .dashboard-card h3 {
            margin-bottom: 10px;
            color: var(--olive-dark);
            font-size: 21px;
        }

        .dashboard-card p {
            margin: 0;
            color: #777;
        }

        .card-button {
            display: inline-block;
            margin-top: 18px;
            padding: 10px 18px;
            color: white;
            background-color: var(--olive);
            border-radius: 7px;
            text-decoration: none;
            transition: 0.3s;
        }

        .card-button:hover {
            color: white;
            background-color: var(--gold);
        }

        @media (max-width: 768px) {
            .welcome-card {
                padding: 30px 22px;
            }

            .welcome-card h1 {
                font-size: 27px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<?php include "include/nav.php"; ?>

<main class="admin-wrapper">

    <section class="welcome-card">
        <h1>
            Bonjour,
            <span>
                <?= htmlspecialchars($_SESSION['utilisateur']['login']) ?>
            </span>
        </h1>

        <p>
            Bienvenue dans votre espace d'administration.
        </p>
    </section>

    <section class="dashboard-cards">

        <div class="dashboard-card">
            <h3>Utilisateurs</h3>
            <p>Gérer les utilisateurs du site.</p>
            <a href="/ecomerce/utilisateur.php" class="card-button">
                Gérer
            </a>
        </div>

        <div class="dashboard-card">
            <h3>Produits</h3>
            <p>Ajouter et gérer vos produits.</p>
            <a href="/ecomerce/produit.php" class="card-button">
                Gérer
            </a>
        </div>

        <div class="dashboard-card">
            <h3>Catégories</h3>
            <p>Organiser les catégories.</p>
            <a href="/ecomerce/categori.php" class="card-button">
                Gérer
            </a>
        </div>

    </section>

</main>

</body>
</html>
