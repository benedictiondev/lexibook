# Contrôles avant publication GitHub

## Changements effectués depuis le backup

- Exclusion du dossier `.git` d'origine et des métadonnées macOS.
- Suppression de la copie SQL secondaire et des enregistrements (contacts, comptes et historiques) de l'export SQL conservé. Le SQL public contient uniquement les structures, clés et contraintes.
- Remplacement des identifiants MySQL intégrés au code par `config/database.local.php`, ignoré par Git, et un exemple sans mot de passe.
- Suppression des mots de passe d'initialisation fixes ; scripts `creer_admin.php` et `creer_demo.php` accessibles uniquement en ligne de commande.
- Désactivation par défaut de la connexion démo sans mot de passe.
- Correction des erreurs de syntaxe dans `livres/ajouter.php`, `adherents/modifier.php` et `emprunts/ajouter.php`.
- Suppression d'inclusions de navigation placées trop tôt, pour éviter l'envoi prématuré de contenu HTML.

## Important

- **Ne téléverse pas le fichier original `bibliotheque_backup.zip` sur GitHub.** Il contient des données personnelles et des informations de connexion.
- **N'ajoute pas ton ancien dossier `.git` sur un dépôt public.** De précédents commits peuvent stocker ces informations même si tu supprimes ensuite les fichiers.
- Utilise le nouveau dossier préparé, puis un tout nouveau dépôt Git.
- Modifie les anciens mots de passe s'ils ont été réutilisés ailleurs.
- Le code n'a pas fait l'objet d'un audit de sécurité complet ni de tests fonctionnels sur base MySQL.
- Ne mets pas l'application en ligne sur un hébergement public avant de tester et sécuriser les formulaires et droits d'accès.
