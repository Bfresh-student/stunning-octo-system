# 🛡️ Guide de Sécurité

Ce document décrit en détail l'ensemble des mesures, politiques et mécanismes de sécurité appliqués au sein de l'application **Boutique E-commerce PHP 8.3**.

---

## 1. Principes et Standards Généraux

* **Typage strict (`declare(strict_types=1);`)** : Actif en en-tête de **100% des fichiers PHP** du projet. Cela interdit la coercition implicite des types et protège l'application contre les failles d'incohérence de type (*type juggling*).
* **Architecture en couches étanches** : Le dossier exposé au web est strictement [`public/`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/public). Le code source métier (`app/`), les configurations sensibles (`config/`, `.env`), les logs (`logs/`) et les tests (`test/`) se trouvent en dehors du `DocumentRoot` Apache.
* **Principe du moindre privilège** :
  * Les conteneurs Docker exécutent Apache sous l'utilisateur système dédié non-root `www-data`.
  * Seuls les dossiers nécessaires (`public/uploads` et `logs`) disposent des droits d'écriture.

---

## 2. Protection contre les Injections SQL

L'ensemble des interactions avec la base de données MySQL s'effectue exclusivement par l'intermédiaire de **requêtes préparées PDO** avec liaison de paramètres (*parameter binding*).

### Exemple d'implémentation :
```php
$stmt = $this->pdo->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(\PDO::FETCH_ASSOC);
```

* **Aucune concaténation de variable utilisateur** n'est tolérée dans les requêtes SQL.
* Le mode d'erreur PDO est configuré sur `PDO::ERRMODE_EXCEPTION`, empêchant l'exposition silencieuse de données corrompues.
* L'encodage `utf8mb4` est imposé dans le DSN de connexion afin d'éviter les attaques par injection via des caractères multi-octets tronqués.

---

## 3. Protection contre les Failles CSRF (Cross-Site Request Forgery)

La classe utilitaire [`App\Utils\Csrf`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/app/Utils/Csrf.php) fournit un mécanisme robuste de génération et de vérification des jetons anti-CSRF :

```php
// 1. Génération cryptographique aléatoire (32 octets = 64 caractères hexadécimaux)
$token = bin2hex(random_bytes(32));

// 2. Vérification sécurisée contre les attaques temporelles (timing attacks)
hash_equals($_SESSION['csrf_token'], $submittedToken);
```

### Application :
* Tout formulaire HTML effectuant une mutation d'état (POST : connexion, inscription, ajout produit, ajout panier, commande) inclut un champ caché :
  ```html
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Utils\Csrf::generateToken()) ?>">
  ```
* Chaque contrôleur valide systématiquement le jeton dès le début de la méthode POST. En cas d'anomalie, une réponse HTTP `403 Forbidden` est retournée et une alerte est enregistrée dans le journal d'audit :
  ```php
  if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
      AppLogger::warning('Tentative non autorisée : jeton CSRF invalide');
      http_response_code(403);
      exit;
  }
  ```

---

## 4. Protection contre les Failles XSS (Cross-Site Scripting)

* **Échappement systématique à l'affichage** : Toutes les données dynamiques provenant des utilisateurs ou de la base de données sont nettoyées avec `htmlspecialchars()` avec les drapeaux `ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'` avant toute injection dans le DOM HTML.
* **Exemple dans les vues** :
  ```php
  <h2><?= htmlspecialchars($produit['nom']) ?></h2>
  <p><?= htmlspecialchars($produit['description']) ?></p>
  ```

---

## 5. Authentification et Gestion des Mots de Passe

* **Hachage cryptographique sécurisé** : Assuré par le service [`App\Services\Hashage`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/app/Services/Hashage.php) qui utilise l'algorithme `PASSWORD_BCRYPT` natif de PHP (avec sel automatique et facteur de travail adaptatif).
  ```php
  public function hash(string $password): string {
      return password_hash($password, PASSWORD_BCRYPT);
  }

  public function verify(string $password, string $hash): bool {
      return password_verify($password, $hash);
  }
  ```
* **Politique de complexité** : Longueur minimale requise de 8 caractères à l'inscription.
* **Régénération d'identifiant de session** : Lors d'une connexion réussie, `session_regenerate_id(true)` est exécuté pour détruire l'ancien identifiant et neutraliser les attaques par fixation de session (*Session Fixation*).
* **Destruction de session** : La déconnexion vide (`session_unset`) et détruit (`session_destroy`) la session serveur de manière irréversible.

---

## 6. Sécurité des Téléchargements de Fichiers (Uploads)

Le service [`App\Services\UploadFiles`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/app/Services/UploadFiles.php) applique des règles strictes sur tout fichier envoyé :

1. **Validation du type MIME réel** via `finfo` (et non sur la simple extension fournie par le client) :
   * Formats autorisés : `image/jpeg`, `image/png`, `image/webp`.
2. **Renommage aléatoire obligatoire** : Le nom d'origine du fichier est rejeté et remplacé par un identifiant cryptographique unique (`uniqid('img_', true) . '.' . $extension`), empêchant les attaques par traversée de répertoire (*Path Traversal*) ou écrasement de fichiers système.
3. **Limitation de taille** : Rejet de tout fichier dépassant la limite autorisée (2 Mo).
4. **Stockage isolé** : Les fichiers sont enregistrés dans [`public/uploads/`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/public/uploads), dossier pour lequel l'exécution de scripts PHP est désactivée par configuration Apache.

---

## 7. Gestion des Secrets et Données Sensibles

* **Variables d'environnement (`.env`)** : Tous les identifiants de base de données, clés secrètes et hôtes sont exclus du gestionnaire de version Git via [`.gitignore`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/.gitignore).
* **Isolation CI/CD** : Les variables de test sont injectées de manière éphémère dans le runner GitHub Actions sans persistance des secrets en clair dans le dépôt.

---

## 8. Journalisation et Détection d'Intrusions

Toutes les anomalies de sécurité sont tracées avec leur contexte par [`App\Utils\AppLogger`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/app/Utils/AppLogger.php) dans [`logs/app.log`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/logs/app.log) :
* Échecs répétés de connexion (`AppLogger::error('Échec de connexion', ['email' => ...])`)
* Invalidité de jeton CSRF (`AppLogger::warning('Tentative... jeton CSRF invalide')`)
* Erreurs critiques de connexion BDD (`AppLogger::critical(...)`)
* Tentatives d'accès à des routes inexistantes (`AppLogger::warning('Route non trouvée (404)', ...)`)
