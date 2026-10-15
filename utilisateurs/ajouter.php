<?php

require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../config/database.php';


/* =========================================================
   VARIABLES
========================================================= */

$erreurs = [];

$nom = '';
$email = '';
$role = 'employe';


/* =========================================================
   TRAITEMENT DU FORMULAIRE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $mot_de_passe =
        $_POST['mot_de_passe'] ?? '';

    $confirmation =
        $_POST['confirmation_mot_de_passe'] ?? '';

    $role =
        $_POST['role'] ?? 'employe';


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($nom === '') {

        $erreurs[] =
            "Le nom est obligatoire.";
    }


    if ($email === '') {

        $erreurs[] =
            "L'adresse email est obligatoire.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $erreurs[] =
            "L'adresse email n'est pas valide.";
    }


    if ($mot_de_passe === '') {

        $erreurs[] =
            "Le mot de passe est obligatoire.";

    } elseif (
        strlen($mot_de_passe) < 8
    ) {

        $erreurs[] =
            "Le mot de passe doit contenir au moins 8 caractères.";
    }


    if (
        $mot_de_passe !==
        $confirmation
    ) {

        $erreurs[] =
            "Les deux mots de passe ne correspondent pas.";
    }


    if (
        !in_array(
            $role,
            ['admin', 'employe'],
            true
        )
    ) {

        $erreurs[] =
            "Le rôle sélectionné n'est pas valide.";
    }


    /* =====================================================
       VERIFICATION EMAIL
    ===================================================== */

    if (
        $email !== ''
        &&
        filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)

            FROM utilisateurs

            WHERE email = :email
        ");

        $stmt->execute([
            'email' => $email
        ]);


        if (
            (int) $stmt->fetchColumn() > 0
        ) {

            $erreurs[] =
                "Un utilisateur avec cette adresse email existe déjà.";
        }
    }


    /* =====================================================
       CREATION DE L'UTILISATEUR
    ===================================================== */

    if (empty($erreurs)) {

        $hash = password_hash(
            $mot_de_passe,
            PASSWORD_DEFAULT
        );


        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs (
                nom,
                email,
                mot_de_passe,
                role
            )

            VALUES (
                :nom,
                :email,
                :mot_de_passe,
                :role
            )
        ");


        $stmt->execute([
            'nom' => $nom,
            'email' => $email,
            'mot_de_passe' => $hash,
            'role' => $role
        ]);


        header(
            'Location: /bibliotheque/utilisateurs/?ajout=success'
        );

        exit;
    }
}


require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

?>


<main class="main-content">

    <div class="page-header">

        <div>

            <p class="page-label">
                Administration
            </p>

            <h1>Ajouter un utilisateur</h1>

            <p class="page-description">
                Créez un compte permettant
                d'accéder à l'application.
            </p>

        </div>


        <a
            href="/bibliotheque/utilisateurs/"
            class="btn btn-secondary"
        >
            ← Retour aux utilisateurs
        </a>

    </div>


    <!-- =========================
         ERREURS
    ========================== -->

    <?php if (!empty($erreurs)): ?>

        <div class="alert alert-error">

            <strong>
                Le formulaire contient
                une ou plusieurs erreurs :
            </strong>

            <ul>

                <?php foreach ($erreurs as $erreur): ?>

                    <li>
                        <?= htmlspecialchars($erreur) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- =========================
         FORMULAIRE
    ========================== -->

    <div class="form-card">

        <form method="POST" <form method="POST">

   

            <div class="form-grid">


                <!-- NOM -->

                <div class="form-group">

                    <label for="nom">

                        Nom
                        <span class="required">*</span>

                    </label>

                    <input
                        type="text"
                        id="nom"
                        name="nom"
                        maxlength="100"
                        required
                        value="<?= htmlspecialchars($nom) ?>"
                        placeholder="Ex. Jean DUPONT"
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">

                        Adresse email
                        <span class="required">*</span>

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="Ex. jean.dupont@email.com"
                    >

                </div>


                <!-- ROLE -->

                <div class="form-group form-group-full">

                    <label for="role">

                        Rôle
                        <span class="required">*</span>

                    </label>

                    <select
                        id="role"
                        name="role"
                        required
                    >

                        <option
                            value="employe"
                            <?= $role === 'employe'
                                ? 'selected'
                                : ''
                            ?>
                        >
                            Employé
                        </option>


                        <option
                            value="admin"
                            <?= $role === 'admin'
                                ? 'selected'
                                : ''
                            ?>
                        >
                            Administrateur
                        </option>

                    </select>


                    <small class="form-help">

                        L'administrateur dispose
                        de droits supplémentaires,
                        notamment pour les suppressions
                        et la gestion des utilisateurs.

                    </small>

                </div>


                <!-- MOT DE PASSE -->

                <div class="form-group">

                    <label for="mot_de_passe">

                        Mot de passe
                        <span class="required">*</span>

                    </label>

                    <input
                        type="password"
                        id="mot_de_passe"
                        name="mot_de_passe"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Minimum 8 caractères"
                    >

                </div>


                <!-- CONFIRMATION -->

                <div class="form-group">

                    <label for="confirmation_mot_de_passe">

                        Confirmation
                        <span class="required">*</span>

                    </label>

                    <input
                        type="password"
                        id="confirmation_mot_de_passe"
                        name="confirmation_mot_de_passe"
                        minlength="8"
                        required
                        autocomplete="new-password"
                        placeholder="Confirmer le mot de passe"
                    >

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="/bibliotheque/utilisateurs/"
                    class="btn btn-secondary"
                >
                    Annuler
                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Créer l'utilisateur
                </button>

            </div>

        </form>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>