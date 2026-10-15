# LexiBook — Gestion de bibliothèque

**Projet pédagogique — BTS SIO, option SLAM**  
**Auteur : Bénédiction HOUNKPESSODE** · 2026

## Présentation

LexiBook est une application web de gestion de bibliothèque construite en **PHP, MySQL, HTML et CSS**. Elle permet à une petite bibliothèque de gérer ses livres, ses adhérents et ses emprunts depuis une interface unique.

## Fonctionnalités

- Connexion et gestion des comptes utilisateurs avec rôles administrateur / employé.
- Livres : ajout, recherche, consultation, modification et suppression contrôlée.
- Adhérents : inscription, mise à jour, activation/désactivation.
- Emprunts : enregistrement des prêts, suivi des retours, historique et disponibilité.
- Tableau de bord : indicateurs d'activité.
- Mode de démonstration en consultation seule **optionnel**, désactivé par défaut.

## Technologies

- **PHP** : pages côté serveur, traitements et sessions.
- **MySQL / PDO** : base relationnelle et requêtes préparées.
- **HTML / CSS** : interface utilisateur.
- **MAMP / phpMyAdmin** : environnement local et gestion de la base.

## Structure

```text
lexibook/
├── adherents/         # CRUD des adhérents
├── emprunts/          # Emprunts, retours et historique
├── livres/            # Catalogue de livres
├── utilisateurs/      # Gestion des comptes et rôles
├── config/            # Connexion MySQL (exemple sans mot de passe)
├── includes/          # Authentification, navigation, composants
├── css/               # Feuilles de style
├── images/            # Logo
├── sql/               # Schéma de la base, sans données personnelles
├── creer_admin.php    # Initialisation admin (terminal uniquement)
└── index.php          # Entrée de l'application
```

## Installation locale avec MAMP

1. Installer **MAMP** et démarrer Apache et MySQL.
2. Copier le contenu du dépôt dans le dossier Web `htdocs/bibliotheque/` de MAMP.
3. Dans phpMyAdmin, créer la base **`bibliotheque`** en `utf8mb4`, puis importer `sql/bibliotheque.sql` (schéma vide, sans utilisateurs).
4. Copier `config/database.local.example.php` en `config/database.local.php` et y renseigner **tes identifiants MySQL locaux**. Ce fichier reste ignoré par Git ; vérifier les ports de MAMP (souvent Apache 8888 et MySQL 8889).
5. Dans le terminal, à la racine `bibliotheque`, exécuter `php creer_admin.php` et choisir un mot de passe unique (minimum 12 caractères).
6. Ouvrir `http://localhost:8888/bibliotheque/` (adapter le port Apache selon ta configuration) et se connecter.

> **Attention :** le mot de passe saisi dans le terminal est visible pendant la saisie. Ne l'effectue pas devant d'autres personnes. Ne place jamais de vrais identifiants dans le dépôt GitHub.

### Mode démonstration facultatif

Le mode démo est désactivé par défaut. Sur une **instance d'essai isolée**, exécuter localement `php creer_demo.php` puis définir la variable d'environnement `LEXIBOOK_ENABLE_DEMO=1` dans la configuration du serveur PHP. Ne pas activer ce mode sur une base de données de production ou contenant des données réelles.

## Vérification

```bash
find . -name '*.php' -not -path '*/.git/*' -exec php -l {} \;
```

La syntaxe PHP a été vérifiée pour les fichiers de cette version. Un contrôle de syntaxe ne remplace pas des tests fonctionnels avec une vraie base MySQL ; l'installation et les parcours métier restent à tester localement.

## Points de vigilance et pistes d'amélioration

- Ajouter une protection CSRF aux formulaires de modification et de suppression.
- Renforcer les validations côté serveur et les contrôles d'autorisations.
- Mettre en place des tests d'intégration avec une base dédiée.
- Prévoir une configuration explicite du chemin `/bibliotheque/` si le projet est installé dans un autre dossier.

## Compétences BTS SIO SLAM

Développement web avec PHP, structuration des données avec MySQL, traitement des formulaires, règles de gestion, sessions, authentification et documentation d'un projet.

## Documentation

Voir [`docs/LexiBook_Documentation.pdf`](docs/LexiBook_Documentation.pdf) pour la description fonctionnelle et technique. Certains détails de configuration ont été sécurisés depuis cette version de la documentation ; la présente notice fait foi pour l'installation.
