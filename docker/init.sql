-- Initialisation de la base de données boutique
CREATE DATABASE IF NOT EXISTS `boutique` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `boutique`;

-- Table : categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table : user
CREATE TABLE IF NOT EXISTS `user` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table : adresses
CREATE TABLE IF NOT EXISTS `adresses` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `rue` varchar(255) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(100) NOT NULL DEFAULT 'Haïti',
  `telephone` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_adresses_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table : produits
CREATE TABLE IF NOT EXISTS `produits` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `categorie_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_produits_categorie` (`categorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table : commandes
CREATE TABLE IF NOT EXISTS `commandes` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `adresse_id` bigint DEFAULT NULL,
  `statut` enum('en_attente','payee','expediee','livree','annulee') NOT NULL DEFAULT 'en_attente',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_commandes_user` (`user_id`),
  KEY `idx_commandes_adresse` (`adresse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table : lignes_commande
CREATE TABLE IF NOT EXISTS `lignes_commande` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `commande_id` bigint NOT NULL,
  `produit_id` bigint UNSIGNED NOT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  `prix_unitaire` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lignes_commande` (`commande_id`),
  KEY `idx_lignes_produit` (`produit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table : panier
CREATE TABLE IF NOT EXISTS `panier` (
  `user_id` bigint NOT NULL,
  `produit_id` bigint UNSIGNED NOT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`produit_id`),
  KEY `idx_panier_produit` (`produit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Données initiales pour les produits
INSERT INTO `produits` (`id`, `nom`, `description`, `prix`, `stock`, `image_url`, `categorie_id`) VALUES
(1, 'charbon', 'Charbon de bois traditionnel de haute qualité', 35.00, 20, NULL, NULL),
(2, 'huile', 'Huile végétale de cuisine 100% pure', 34.00, 10, NULL, NULL),
(6, 'coupe du monde', 'Ballon officiel de football Coupe du Monde', 89.00, 9, NULL, NULL)
ON DUPLICATE KEY UPDATE `nom` = VALUES(`nom`);

