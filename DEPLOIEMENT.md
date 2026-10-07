# 🚀 Guide de Déploiement et CI/CD

Ce document décrit le pipeline d'intégration continue (CI), les stratégies de déploiement continu (CD), la gestion des environnements et les procédures de mise en production de l'application **Boutique E-commerce PHP 8.3**.

---

## 1. Pipeline CI/CD GitHub Actions

Le projet intègre un pipeline complet automatisé défini dans [`.github/workflows/ci.yml`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/.github/workflows/ci.yml).

```mermaid
flowchart TD
    Commit([git push / PR origin master]) --> CI[GitHub Actions Runner]
    subgraph Job 1: tests
        ServiceDB[(Service MySQL 8.0)]
        SetupPHP[Configuration PHP 8.3 + Extensions]
        InstallComp[Composer Install avec Cache]
        Lint[Linting syntaxique php -l]
        InitDB[Import schéma docker/init.sql]
        RunTests[Exécution PHPUnit 54 tests]
        SetupPHP --> InstallComp --> Lint --> InitDB --> RunTests
        ServiceDB -.-> InitDB
    end
    subgraph Job 2: docker-build
        CheckCompose[Validation docker compose config]
        BuildImage[Construction Docker Image boutique-app]
        CheckCompose --> BuildImage
    end
    CI --> Job 1: tests
    Job 1: tests -->|Succès| Job 2: docker-build
    Job 2: docker-build -->|Optionnel| CD[Déploiement Serveur Production]
```

### Déclencheurs :
* Tout `push` sur les branches `master` et `main`.
* Toute `pull_request` ciblant `master` ou `main`.

---

## 2. Gestion des Environnements

| Élément | Développement Local | CI GitHub Actions | Production (VPS / Cloud) |
| :--- | :--- | :--- | :--- |
| **Hôte BDD** | `localhost` ou `db` | `127.0.0.1` | `db` ou service managé Cloud |
| **Port BDD** | `3306` (WAMP) / `3307` (Docker) | `3306` (Service Container) | `3306` (Réseau privé interne) |
| **Base de données**| `boutique` | `boutique` | `boutique_prod` |
| **Display Errors** | Activé (`E_ALL`) | Désactivé dans Apache | Désactivé (`display_errors = Off`) |
| **Niveau de Log** | `DEBUG` | `DEBUG` | `INFO` / `WARNING` |

---

## 3. Déploiement Continu sur Serveur VPS avec Docker Compose

### Étape 1 : Prérequis sur le serveur
* Docker Engine 24+ et Docker Compose v2 installés.
* Un utilisateur dédié avec accès SSH par clé.
* Cloner le dépôt et initialiser le fichier `.env` de production :
  ```bash
  git clone https://github.com/votre-compte/votre-projet.git /var/www/boutique
  cd /var/www/boutique
  cp .env.example .env
  # Ajuster les mots de passe et secrets
  nano .env
  ```

### Étape 2 : Lancement des conteneurs
```bash
docker compose up -d --build
```

### Étape 3 : Automatisation via GitHub Actions (CD)
Vous pouvez étendre [`.github/workflows/ci.yml`](file:///c:/Users/Beauchard/Documents/exercicesCS/PHP/project/.github/workflows/ci.yml) en ajoutant un job de déploiement conditionné au succès des tests sur la branche `master` :

```yaml
  deploy:
    name: Déploiement Production
    runs-on: ubuntu-latest
    needs: [tests, docker-build]
    if: github.ref == 'refs/heads/master'

    steps:
      - name: Déploiement via SSH
        uses: appleboy/ssh-action@v1.0.0
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.PROD_SSH_KEY }}
          script: |
            cd /var/www/boutique
            git pull origin master
            docker compose build --no-cache app
            docker compose up -d app
            docker image prune -f
```

---

## 4. Stratégie de Mise à Jour de la Base de Données

1. Les données persistantes résident dans le volume Docker nommé `db_data`. Elles ne sont jamais perdues lors de la recréation des conteneurs applicatifs.
2. Pour les évolutions de schéma :
   * Créer des fichiers de migration incrémentaux (ex: `docker/migrations/001_add_column.sql`).
   * Exécuter la migration via `docker exec -i boutique_db mysql -u root -p boutique < migration.sql`.

---

## 5. Procédure de Rollback Rapide

En cas d'anomalie détectée après une mise en production :
```bash
# 1. Revenir au commit stable précédent
git checkout <commit_hash_precedent>

# 2. Reconstruire et relancer le conteneur applicatif
docker compose build app
docker compose up -d app
```
