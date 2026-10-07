# ⚡ Guide des Performances et Optimisations

Ce document détaille l'ensemble des choix techniques, configurations et bonnes pratiques mis en œuvre pour garantir une vitesse d'exécution élevée et une faible consommation de ressources sur l'application **Boutique E-commerce PHP 8.3**.

---

## 1. Moteur PHP 8.3 et OPcache

L'application repose sur PHP 8.3, qui apporte des gains de performance mesurables par rapport aux versions antérieures :
* **Typage strict natif** : Évite les conversions de types à l'exécution et optimise le bytecode généré par le moteur Zend.
* **Gestion mémoire optimisée** : Amélioration de l'allocation mémoire pour les tableaux et chaînes de caractères.

### Configuration recommandée d'OPcache (Production)
Dans l'environnement de production Docker ou sur serveur dédié, l'activation et le réglage fin d'OPcache permettent d'éviter la recompilation systématique des scripts PHP :

```ini
; /usr/local/etc/php/conf.d/opcache.ini
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0       ; En production : aucun rechargement disque
opcache.revalidate_freq=0
opcache.save_comments=1
```

---

## 2. Optimisation de la Base de Données MySQL

Le schéma relationnel défini dans [`docker/init.sql`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/docker/init.sql) applique les principes d'optimisation suivants :

### 1. Indexation stratégique des clés étrangères et champs de recherche
* `user.email` : Index unique `UNIQUE(email)` accélérant la vérification à la connexion en `O(1)`.
* `adresses.user_id` : Index pour récupérer instantanément les adresses d'un utilisateur.
* `produits.categorie_id` : Index facilitant le filtrage par catégorie dans le catalogue.
* `commandes.user_id` : Index accélérant l'historique de commandes utilisateur.
* `lignes_commande.commande_id` et `lignes_commande.produit_id` : Index facilitant le détail d'une commande.
* `panier(user_id, produit_id)` : Clé primaire composite ou index combiné évitant les scans complets de table (*Full Table Scan*).

### 2. Requêtes ciblées et jointures optimisées
* Les requêtes de récupération de listes sélectionnent uniquement les colonnes requises ou regroupent avec jointures directes (`INNER JOIN` / `LEFT JOIN`) au lieu d'exécuter des requêtes N+1 en boucle PHP.
* Exemple pour le panier :
  ```sql
  SELECT p.*, pr.nom AS produit_nom, pr.prix AS produit_prix, pr.image AS produit_image
  FROM panier p
  JOIN produits pr ON p.produit_id = pr.id
  WHERE p.user_id = :user_id
  ```

---

## 3. Autoloading Composer Optimisé

En environnement de développement, l'autoloader PSR-4 scanne les répertoires. En production, l'autoloader doit être compilé en une table de hachage statique autoritaire (*classmap*) :

```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

**Bénéfices :**
* Aucun appel système `file_exists()` lors du chargement des classes (`App\...`).
* Réduction sensible du temps de réponse initial (*Time to First Byte - TTFB*).

---

## 4. Architecture Légère et Sans Framework Lourd

* **Micro-Router par expressions régulières** ([`app/Routes/Router.php`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/app/Routes/Router.php)) : Résout les routes en quelques microsecondes sans overhead de conteneurs de services monolithiques.
* **Injection de Dépendances par Réflexion ciblée** : L'instanciation des contrôleurs et modèles se fait à la volée uniquement pour la route demandée.
* **Rendu de templates natif** : Le moteur de rendu (`extract($data); include ...`) utilise le moteur PHP natif sans temps de compilation de templates (type Twig/Blade).

---

## 5. Gestion des Sessions et Charge Mémoire

* **Session Payload Minimal** : Seules les données strictes d'identité sont stockées en session (`id`, `nom`, `email`, `role`).
* **Régénération propre** : Le panier et les adresses sont interrogés directement depuis la base de données et non stockés dans la session serveur, évitant l'enflure de la mémoire PHP.

---

## 6. Compression et Mise en Cache HTTP Apache

Le fichier `.htaccess` dans [`public/`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/public) et la configuration Apache peuvent activer :
* **Compression Gzip/Brotli (`mod_deflate`)** pour les flux textuels (HTML, CSS, JS, JSON).
* **En-têtes d'expiration (`mod_expires`)** pour les images stockées dans `public/uploads/` :
  ```apache
  <IfModule mod_expires.c>
      ExpiresActive On
      ExpiresByType image/jpeg "access plus 1 month"
      ExpiresByType image/png "access plus 1 month"
      ExpiresByType image/webp "access plus 1 month"
  </IfModule>
  ```

---

## 7. Outils de Mesure et Benchmarking Recommandés

Pour tester et valider les performances en conditions réelles :
* **Apache Benchmark (ab)** :
  ```bash
  ab -n 1000 -c 50 http://localhost:8080/produits
  ```
* **wrk** :
  ```bash
  wrk -t4 -c100 -d30s http://localhost:8080/
  ```
