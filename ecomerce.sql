-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 06 nov. 2026 à 22:25
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ecomerce`
--

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `Id` int(11) NOT NULL,
  `Libelle` varchar(255) NOT NULL,
  `Description` varchar(500) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`Id`, `Libelle`, `Description`, `date_creation`) VALUES
(3, 'Fruits', 'Fresh fruits, handpicked every morning for their taste and freshness. Natural, colorful and full of vitamins, for healthy and delicious moments.', '2026-10-08 02:56:21'),
(6, 'LÃ©gumes', 'Fresh vegetables selected for quality and taste.', '2026-11-03 12:43:08'),
(8, 'Lait et Oeufs', 'Fresh dairy products and quality eggs.', '2026-11-03 12:43:08'),
(13, 'Boulangerie', 'Fresh bread, pastries, and baked products.', '2026-11-03 12:56:48'),
(14, 'Produits Laitiers', 'Milk, cheese, yogurt, and other dairy products.', '2026-11-03 12:56:48'),
(15, 'Boissons', 'Refreshing drinks, juices, water, and hot beverages.', '2026-11-03 12:56:48');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `Id` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `nom_client` varchar(150) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `adresse` varchar(500) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `statut` varchar(30) DEFAULT 'En attente',
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`Id`, `id_utilisateur`, `nom_client`, `telephone`, `adresse`, `total`, `statut`, `date_creation`) VALUES
(1, 11, 'fatimazahrae elatoui', '0618962594', '82 HAY WAHDA BENSOUDA FES', 112.38, 'En attente', '2026-10-17 15:55:39'),
(2, NULL, 'hada enhari', '0618962594', '82 HAY WAHDA BENSOUDA FES', 24.52, 'En attente', '2026-10-18 13:59:35'),
(3, 11, 'FATIMA ZAHRAE ELATOUI', '0618962594', '82 HAY WAHDA BENSOUDA FES', 37.92, 'En attente', '2026-11-06 21:40:08'),
(4, NULL, 'FATIMA ZAHRAE ELATOUI', '0618962594', '82 HAY WAHDA BENSOUDA FES', 35.02, 'En attente', '2026-11-06 21:54:15');

-- --------------------------------------------------------

--
-- Structure de la table `detail_commande`
--

CREATE TABLE `detail_commande` (
  `Id` int(11) NOT NULL,
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) DEFAULT NULL,
  `libelle_produit` varchar(255) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `quantite` int(11) NOT NULL,
  `sous_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `detail_commande`
--

INSERT INTO `detail_commande` (`Id`, `id_commande`, `id_produit`, `libelle_produit`, `prix`, `quantite`, `sous_total`) VALUES
(1, 1, 2, 'bananes', 17.02, 3, 51.05),
(2, 1, 3, 'carrots', 61.34, 1, 61.34),
(3, 2, 2, 'bananes', 17.02, 1, 17.02),
(4, 2, 4, 'bread', 7.50, 1, 7.50),
(5, 3, 2, 'Bananes', 17.02, 1, 17.02),
(6, 3, 62, 'Pain Complet', 11.40, 1, 11.40),
(7, 3, 63, 'Lait Frais', 9.50, 1, 9.50),
(8, 4, 2, 'Bananes', 17.02, 1, 17.02),
(9, 4, 64, 'Jus d\'Orange', 18.00, 1, 18.00);

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `Id` int(11) NOT NULL,
  `Libelle` varchar(255) NOT NULL,
  `Prix` decimal(10,2) NOT NULL,
  `Discount` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`Id`, `Libelle`, `Prix`, `Discount`, `image`, `id_categorie`, `date_creation`) VALUES
(2, 'Bananes', 20.50, 17, 'produit_6aee44d629a252.19698697.jpg', 3, '2026-10-08 02:56:56'),
(62, 'Pain Complet', 12.00, 5, 'produit_6aee44c9924013.89003574.jpg', 13, '2026-11-03 12:57:02'),
(63, 'Lait Frais', 9.50, 0, 'produit_6aee44b755f700.88707405.webp', 14, '2026-11-03 12:57:02'),
(64, 'Jus d\'Orange', 18.00, 0, 'produit_6aee44a957d030.43716516.jpg', 15, '2026-11-03 12:57:02'),
(65, 'Pommes de Terre', 7.00, 0, 'produit_6aee4493081b77.29740223.jpg', 6, '2026-11-06 21:05:35'),
(66, 'Å’ufs Frais', 1.00, 0, 'produit_6aee446f69a0b7.83945210.jpg', 8, '2026-11-06 21:06:12'),
(67, 'Tomates', 10.00, 0, 'produit_6aee445d31e3d7.20602286.jpg', 6, '2026-11-06 21:06:41'),
(68, 'Carottes', 5.00, 0, 'produit_6aee444fb435e7.66402927.webp', 6, '2026-11-06 21:07:25');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `Id` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`Id`, `login`, `password`, `date_creation`) VALUES
(11, 'fati', '$2y$10$5gxGR3sZr.sr8aH1XJX78.tV7lBPMbfD9ZGhc7J7AHViYoEjg96oy', '2026-10-08 00:00:00'),
(12, 'sana', '$2y$10$spb9XQ.inU8OEmOeukMfH.ex4lBTcHGWCjd9kAcDVKwwwzxufoH5q', '2026-10-08 00:00:00');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_commande_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `detail_commande`
--
ALTER TABLE `detail_commande`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_detail_commande` (`id_commande`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_produit_categorie` (`id_categorie`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `detail_commande`
--
ALTER TABLE `detail_commande`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `fk_commande_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`Id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `detail_commande`
--
ALTER TABLE `detail_commande`
  ADD CONSTRAINT `fk_detail_commande` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `fk_produit_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`Id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
