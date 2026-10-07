# 🛒 Boutique E-Commerce - PHP 8.3 MVC

Application e-commerce moderne conçue en **PHP 8.3 strict** selon le patron d'architecture **MVC**, entièrement testée avec **PHPUnit**, observée avec **Monolog**, conteneurisée avec **Docker** et automatisée via un pipeline **CI/CD GitHub Actions**.

---

## 📚 Hub de Documentation

L'ensemble de la documentation technique est découpé par domaine d'expertise :

| Guide | Description |
| :--- | :--- |
| 🛡️ [**Sécurité (SECURITE.md)**](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/SECURITE.md) | CSRF, Injections SQL (PDO), XSS, Hachage BCrypt, Uploads stricts, sessions. |
| ⚡ [**Performances (PERFORMANCE.md)**](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/PERFORMANCE.md) | PHP 8.3, OPcache, indexation MySQL, autoloading Composer, mise en cache. |
| 🏛️ [**Architecture (ARCHITECTURE.md)**](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/ARCHITECTURE.md) | Patron MVC, routeur regex, injection de dépendances par réflexion, MCD/MLD. |
| 📊 [**Observabilité (OBSERVABILITE.md)**](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/OBSERVABILITE.md) | Centralisation Monolog 3, niveaux de logs, format JSON, rotation des journaux. |
| 🚀 [**Déploiement & CI/CD (DEPLOIEMENT.md)**](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/DEPLOIEMENT.md) | Pipeline GitHub Actions, gestion des environnements, procédures de mise en prod. |
| 🐳 [**Docker & Conteneurs (DOCKER.md)**](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/DOCKER.md) | Dockerfile multi-étapes, docker-compose, réseau bridge, healthcheck MySQL. |

---

## ✨ Fonctionnalités Principales

* **Authentification & Profils** : Inscription avec validation rigoureuse, connexion sécurisée, téléversement de photo de profil, régénération d'identifiant de session anti-fixation.
* **Catalogue de Produits** : Affichage, ajout, modification et suppression de produits avec gestion d'images et contrôle de stock.
* **Gestion du Panier** : Ajout d'articles, modification dynamique des quantités, suppression et vidage.
* **Passation de Commandes** : Conversion du panier en commande avec sélection de l'adresse de livraison et calcul du total.
* **Gestion des Adresses** : Carnet d'adresses multiples par utilisateur.
* **Protection CSRF Globale** : Validation systématique sur toutes les actions POST.
* **Observabilité Monolog** : Journalisation d'audit temps réel dans `logs/app.log`.
* **Typage Strict à 100%** : Déclaration `declare(strict_types=1);` sur l'intégralité du code source.

---

## 🛠️ Stack Technique

* **Langage** : PHP 8.3 (Typage strict activé)
* **Serveur Web** : Apache 2.4 avec `mod_rewrite`
* **Base de Données** : MySQL 8.0 (Encodage `utf8mb4`)
* **Administration BDD** : phpMyAdmin
* **Tests** : PHPUnit 12 (54 tests automatisés unitaires et fonctionnels)
* **Observabilité** : Monolog 3
* **Conteneurisation** : Docker Engine & Docker Compose v2
* **Intégration Continue** : GitHub Actions

---

## 🚀 Démarrage Rapide

### Méthode 1 : Avec Docker Compose (Recommandé)

Aucune installation préalable de PHP ou MySQL n'est nécessaire sur votre machine hôte (seul Docker Desktop est requis).

```bash
# 1. Cloner le projet
git clone https://github.com/votre-compte/votre-projet.git
cd votre-projet

# 2. Démarrer l'ensemble des conteneurs
docker compose up -d --build
```

**Accès aux services :**
* 🌐 **Application Web** : [http://localhost:8080](http://localhost:8080)
* 🗄️ **phpMyAdmin** : [http://localhost:8081](http://localhost:8081) (Serveur: `db`, Utilisateur: `root`, Mot de passe: `rootpassword`)
* 🐬 **Port MySQL hôte** : `localhost:3307` (Évite tout conflit avec WampServer/XAMPP local)

---

### Méthode 2 : Environnement Local (WampServer / PHP CLI)

1. **Installer les dépendances Composer :**
   ```bash
   composer install
   ```

2. **Configurer l'environnement :**
   Créer un fichier `.env` à la racine :
   ```ini
   DB_HOST=127.0.0.1
   DB_NAME=boutique
   DB_USER=root
   DB_PASSWORD=
   LOG_LEVEL=DEBUG
   ```

3. **Créer et importer la base de données :**
   Créer une base `boutique` dans MySQL et importer le fichier [`docker/init.sql`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/docker/init.sql).

4. **Lancer le serveur de développement :**
   ```bash
   php -S localhost:8000 -t public
   ```
   Rendez-vous sur [http://localhost:8000](http://localhost:8000).

---

## 🧪 Exécution des Tests Automatisés

Le projet comprend une suite complète de **54 tests** (tests unitaires et fonctionnels de bout en bout) :

```bash
# En local :
vendor/bin/phpunit --testdox

# Ou à l'intérieur du conteneur Docker :
docker compose exec app vendor/bin/phpunit --testdox
```

**Résultat attendu :**
```text
OK (54 tests, 123 assertions) - 100% de succès
```

---

## 🔄 Pipeline CI/CD

Chaque `push` ou `pull_request` sur les branches `master` ou `main` déclenche le workflow [`.github/workflows/ci.yml`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/.github/workflows/ci.yml) :
1. **Linting** de l'ensemble des fichiers PHP (`php -l`).
2. **Exécution des 54 tests PHPUnit** sur un conteneur MySQL 8.0 dédié avec vérification du schéma [`docker/init.sql`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/docker/init.sql).
3. **Validation de la configuration Docker Compose** et construction de l'image de production.
