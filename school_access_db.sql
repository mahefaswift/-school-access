-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 20 août 2026 à 09:04
-- Version du serveur : 8.0.46-0ubuntu0.24.04.3
-- Version de PHP : 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `school_access_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

CREATE TABLE `administrateurs` (
  `id` int NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('grand_admin','sous_admin') NOT NULL DEFAULT 'sous_admin',
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `administrateurs`
--

INSERT INTO `administrateurs` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `statut`) VALUES
(1, 'RASOANAIVO', 'Alicia', 'aliciarasoanaivo1@gmail.com', '$2y$10$LbnffezD0jRNYPjKHriWoe73htBFkladWZ90rXv6Udugo.Wt57UNi', 'grand_admin', 'actif'),
(2, 'RAKOTO', 'Tovo', 'gardien@test.com', '$2y$10$LbnffezD0jRNYPjKHriWoe73htBFkladWZ90rXv6Udugo.Wt57UNi', 'sous_admin', 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `passages`
--

CREATE TABLE `passages` (
  `id` int NOT NULL,
  `code_badge` varchar(50) NOT NULL,
  `type_mouvement` enum('entree','sortie') NOT NULL,
  `date_heure` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `passages`
--

INSERT INTO `passages` (`id`, `code_badge`, `type_mouvement`, `date_heure`) VALUES
(1, 'SA-2026-4954', 'entree', '2026-04-14 20:17:12'),
(2, 'SA-2026-4954', 'sortie', '2026-04-14 20:17:23'),
(3, 'SA-2026-4954', 'entree', '2026-04-14 20:33:34'),
(4, 'SA-2026-8582', 'entree', '2026-04-14 20:36:25'),
(5, 'SA-2026-8582', 'sortie', '2026-04-14 20:36:37'),
(6, 'SA-2026-6630', 'entree', '2026-04-14 20:43:24'),
(7, 'SA-2026-6630', 'sortie', '2026-04-14 20:43:29'),
(8, 'SA-2026-7442', 'entree', '2026-04-15 17:44:13'),
(9, 'SA-2026-7442', 'sortie', '2026-04-15 17:44:24'),
(10, 'SA-2026-6681', 'entree', '2026-04-25 11:19:56'),
(16, 'SA-2026-7984', 'entree', '2026-05-04 14:20:09'),
(17, 'SA-2026-7984', 'sortie', '2026-05-04 14:20:14'),
(18, 'SA-2026-7984', 'entree', '2026-05-04 14:50:50'),
(19, 'SA-2026-7984', 'sortie', '2026-05-04 14:51:11'),
(20, 'SA-2026-2444', 'entree', '2026-05-11 12:49:16'),
(21, 'SA-2026-2444', 'sortie', '2026-05-11 12:49:21'),
(22, 'SA-2026-9340', 'entree', '2026-05-20 06:32:43'),
(23, 'SA-2026-9340', 'sortie', '2026-05-20 06:32:46'),
(24, 'SA-2026-5076', 'entree', '2026-05-20 07:34:45'),
(25, 'SA-2026-2493', 'entree', '2026-05-20 09:05:42'),
(26, 'SA-2026-2493', 'sortie', '2026-05-20 09:05:46'),
(27, 'SA-2026-2493', 'entree', '2026-05-20 09:09:58'),
(28, 'SA-2026-2493', 'sortie', '2026-05-20 09:10:06'),
(29, 'SA-2026-7327', 'entree', '2026-05-20 09:13:00'),
(30, 'SA-2026-7327', 'sortie', '2026-05-20 09:13:05'),
(31, 'SA-2026-7327', 'entree', '2026-05-20 09:16:00'),
(32, 'SA-2026-7327', 'sortie', '2026-05-20 09:16:05'),
(33, 'SA-2026-7327', 'entree', '2026-05-20 09:16:37'),
(34, 'SA-2026-3023', 'entree', '2026-05-20 09:45:26'),
(35, 'SA-2026-3023', 'sortie', '2026-05-20 09:46:22'),
(36, 'SA-2026-6102', 'entree', '2026-05-20 09:55:27'),
(37, 'SA-2026-6102', 'sortie', '2026-05-20 09:55:46'),
(38, 'SA-2026-6503', 'entree', '2026-05-20 09:58:48'),
(39, 'SA-2026-6503', 'sortie', '2026-05-20 09:58:53'),
(40, 'SA-2026-4883', 'entree', '2026-05-20 10:03:18'),
(41, 'SA-2026-4883', 'sortie', '2026-05-20 10:03:28'),
(42, 'SA-2026-5341', 'entree', '2026-05-20 10:08:51'),
(43, 'SA-2026-5341', 'sortie', '2026-05-20 10:09:14'),
(44, 'SA-2026-4786', 'entree', '2026-05-20 10:18:36'),
(45, 'SA-2026-4786', 'sortie', '2026-05-20 10:18:54'),
(46, 'SA-2026-9671', 'entree', '2026-05-20 10:41:21'),
(47, 'SA-2026-9671', 'sortie', '2026-05-20 10:41:48'),
(48, 'SA-2026-9671', 'entree', '2026-05-20 10:56:01'),
(49, 'SA-2026-9671', 'sortie', '2026-05-20 10:56:13'),
(50, 'SA-2026-9251', 'entree', '2026-05-20 11:31:32'),
(51, 'SA-2026-9251', 'sortie', '2026-05-20 11:31:53'),
(52, 'SA-2026-6778', 'entree', '2026-07-24 05:59:18'),
(53, 'SA-2026-3383', 'entree', '2026-07-24 08:14:14'),
(54, 'SA-2026-3383', 'sortie', '2026-07-24 08:14:31'),
(55, 'SA-2026-3383', 'entree', '2026-07-24 08:15:52'),
(56, 'SA-2026-3383', 'sortie', '2026-07-24 08:16:06'),
(57, 'SA-2026-2427', 'entree', '2026-07-24 08:18:30'),
(58, 'SA-2026-2427', 'sortie', '2026-07-24 08:19:00'),
(59, 'SA-2026-9966', 'entree', '2026-07-24 08:36:55'),
(60, 'SA-2026-3383', 'entree', '2026-07-28 07:56:26'),
(61, 'SA-2026-3383', 'sortie', '2026-07-28 07:56:38'),
(62, 'SA-2026-7820', 'entree', '2026-07-28 07:58:42'),
(63, 'SA-2026-7820', 'sortie', '2026-07-28 07:58:50'),
(64, 'SA-2026-3383', 'entree', '2026-08-19 19:09:22'),
(65, 'SA-2026-3383', 'sortie', '2026-08-19 19:13:35'),
(66, 'SA-2026-3383', 'entree', '2026-08-20 08:37:18'),
(67, 'SA-2026-3383', 'sortie', '2026-08-20 09:22:25'),
(68, 'SA-2026-3383', 'entree', '2026-08-20 09:22:49');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `classe` varchar(50) DEFAULT NULL,
  `marque_vehicule` varchar(100) DEFAULT NULL,
  `code_badge` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `classe`, `marque_vehicule`, `code_badge`, `created_at`) VALUES
(10, 'Paul', 'DUPONT', 'GCA', 'Yamaha', 'SA-2026-7406', '2026-04-14 15:53:21'),
(11, 'RABE', 'Sarobidy', 'GCA', 'Jbubu', 'SA-2026-8582', '2026-04-14 17:35:32'),
(12, 'RAFITIAMALALA', 'Rindra', 'GCA 1', 'Yamaha', 'SA-2026-6630', '2026-04-14 17:42:59'),
(15, 'Naina', 'RAKOTOVAO', 'ISAIA2', 'Yamaha', 'SA-2026-9510', '2026-04-29 13:21:15'),
(16, 'Soa', 'RATOVO', 'TEH3', 'C4', 'SA-2026-7984', '2026-05-01 10:02:08'),
(17, 'Nofy', 'RABE', 'GCA', 'Jbubu', 'SA-2026-7073', '2026-05-02 11:07:47'),
(19, 'Nofy', 'KOTO', 'ISAIA2', 'Jbubu', 'SA-2026-2961', '2026-05-02 11:12:36'),
(20, 'Nirina', 'TOVO', 'EMP1', 'Yamaha', 'SA-2026-6783', '2026-05-02 16:43:12'),
(25, 'Soa', 'TOVO', 'TEH3', 'C4', 'SA-2026-5093', '2026-05-04 11:18:08'),
(26, 'Rivo', 'RAKOTO', 'IGGLIA 1B', 'Jbubu', 'SA-2026-3397', '2026-05-04 11:54:28'),
(27, 'Nivo', 'RAKOTO', 'GCA 1', 'RSZ', 'SA-2026-7100', '2026-05-07 04:15:51'),
(28, 'Niaina', 'RAKOTO ', 'GCA', 'Yamaha', 'SA-2026-2444', '2026-05-11 09:47:25'),
(30, 'Alicia', 'Rasoanaivo', 'GCA', 'Jbubu', 'SA-2026-9340', '2026-05-20 03:32:18'),
(31, 'Alicia', 'Rasoanaivo', 'GCA', 'Jbubu', 'SA-2026-5076', '2026-05-20 04:34:14'),
(32, 'Kasy Harinoro Ampokatiana', 'RANDRIANASOA', 'IGGLIA 1B', 'tmax', 'SA-2026-8735', '2026-05-20 06:02:44'),
(33, 'Kasy Harinoro Ampokatiana', 'RANDRIANASOA', 'IGGLIA 1B', 'tmax', 'SA-2026-2493', '2026-05-20 06:03:57'),
(34, 'Fanantena', 'Rakoto', 'GCA', 'Jbubu', 'SA-2026-5099', '2026-05-20 06:09:07'),
(35, 'Johan', 'Rabe', 'IGGLIA 1C', 'Jbubu', 'SA-2026-7327', '2026-05-20 06:12:06'),
(36, 'Rederica', 'RABE', 'BIO1', 'C4', 'SA-2026-9006', '2026-05-20 06:14:56'),
(37, 'faniry', 'Nomena', 'GCA4', 'tuctuc', 'SA-2026-3023', '2026-05-20 06:44:17'),
(38, 'Fitia', 'RAMANAMPISOA', 'ESIIA1A', 'Yamaha', 'SA-2026-6102', '2026-05-20 06:54:40'),
(39, 'tina', 'onja', 'IGGLIA 1B', 'Yamaha', 'SA-2026-6503', '2026-05-20 06:58:16'),
(40, 'Miary', 'RAKOTO', 'IGGLIA 1B', 'Jbubu', 'SA-2026-4883', '2026-05-20 07:00:42'),
(41, 'Kheroune', 'RALAY', 'BIO1', 'Yamaha', 'SA-2026-5341', '2026-05-20 07:07:46'),
(42, 'Jonah', 'RAKOTO', 'ESIIA', 'Yamaha', 'SA-2026-4786', '2026-05-20 07:17:12'),
(43, 'Mira', 'ANDRY', 'IGGLIA 1B', 'C4', 'SA-2026-9671', '2026-05-20 07:40:16'),
(44, 'Stephane', 'RAKOTO', 'BIO1', 'Yamaha', 'SA-2026-4557', '2026-05-20 07:52:12'),
(45, 'vhatra', 'najaina', 'IGGLIA 1B', 'Jbubu', 'SA-2026-8586', '2026-05-20 07:55:11'),
(46, 'Alicia', 'Rasoanaivo', 'IGGLIA 1B', 'Yamaha', 'SA-2026-6371', '2026-05-20 07:55:43'),
(47, 'Tendry', 'RANDRIANIRINA', 'IGGLIA 1B', 'Ducati', 'SA-2026-9251', '2026-05-20 08:31:01'),
(48, 'Mino', 'RIVO', 'GCA', 'Yamaha', 'SA-2026-1389', '2026-06-17 03:09:07'),
(51, 'Liantsoa', 'RAJAOMANANA', 'IGGLIA 1B', 'Yamaha', 'SA-2026-3383', '2026-07-24 04:50:07'),
(52, 'Ny Onjatiana ', 'RASOLONIAINA', 'GCA', 'Jbubu', 'SA-2026-2427', '2026-07-24 05:17:34'),
(53, 'Rivo', 'RAKOTO', 'GCA', 'Jbubu', 'SA-2026-9966', '2026-07-24 05:36:31'),
(54, 'Mathieu', 'RASOLONIRINA', 'IGGLIA 1B', 'KYMCO', 'SA-2026-7820', '2026-07-28 04:57:30'),
(55, 'Koto', 'RANDRIA', 'IGGLIA 4', 'Jbubu', 'SA-2026-6157', '2026-08-18 22:02:35'),
(56, 'Niaina ', 'RIVO', 'GCA', 'Jbubu', 'SA-2026-1253', '2026-08-20 05:43:02'),
(57, 'Nivo', 'RASOA', 'GCA', 'C4', 'SA-2026-5898', '2026-08-20 06:28:56'),
(58, 'Miora', 'RANDRIA', 'TEH3', 'Yamaha', 'SA-2026-1243', '2026-08-20 08:08:29');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `passages`
--
ALTER TABLE `passages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_badge` (`code_badge`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `passages`
--
ALTER TABLE `passages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
