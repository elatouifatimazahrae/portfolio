<?php
$con = isset($_SESSION['utilisateur']);
?>


<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/ecomerce/index.php">
            VELORA
        </a>

        <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown"
                aria-expanded="false"
                aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">

                <?php if ($con): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="/ecomerce/admin.php">
                            Administration
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/ecomerce/produit.php">
                            Ajouter Produits
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/ecomerce/categori.php">
                            Ajouter catégorie
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-danger" href="/ecomerce/logout.php">
                            Déconnexion
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="/ecomerce/index.php">
                            Ajouter utilisateur
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/ecomerce/connexion.php">
                            Connexion
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>
