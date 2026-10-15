<?php

require_once __DIR__ . '/../config/database.php';


/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_utilisateur = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id_utilisateur) {

    header(
        'Location: /bibliotheque/utilisateurs/'
    );

    exit;
}


/* =========================================================
   RECUPERATION DE L'UTILISATEUR
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        id_utilisateur,
        nom,
        email,
        role

    FROM utilisateurs

    WHERE id_utilisateur = :id_utilisateur
");

$stmt->execute([
    'id_utilisateur' => $id_utilisateur
]);

$utilisateur = $stmt->fetch();


if (!$utilisateur) {

    header(
        'Location: /bibliotheque/utilisateurs/'
    );

    exit;
}


/* =========================================================
   VARIABLES
========================================================= */

$erreurs = [];

$nom = $utilisateur['nom'];
$email = $utilisateur['email'];
$role = $utilisateur['role'];

$estMonCompte =
    (int) $id_utilisateur ===
    (int) $_SESSION['utilisateur']['id'];


/* =========================================================
   TRAITEMENT DU FORMULAIRE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim(
        $_POST['nom'] ?? ''
    );

    $email = trim(
        $_POST['email'] ?? ''
    );

    $role = $_POST['role'] ?? 'employe';

    $mot_de_passe =
        $_POST['mot_de_passe'] ?? '';

    $confirmation =
        $_POST['confirmation_mot_de_passe'] ?? '';


    /* =====================================================
       VALIDATION DU NOM
    ===================================================== */

    if ($nom === '') {

        $erreurs[] =
            "Le nom est obligatoire.";
    }


    /* =====================================================
       VALIDATION EMAIL
    ===================================================== */

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


    /* =====================================================
       VALIDATION DU ROLE
    ===================================================== */

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


    /*
     * L'administrateur connecté ne peut pas
     * retirer son propre rôle administrateur.
     */

    if (
        $estMonCompte
        &&
        $role !== 'admin'
    ) {

        $erreurs[] =
            "Vous ne pouvez pas retirer votre propre rôle administrateur.";

        $role = 'admin';
    }


    /* =====================================================
       VALIDATION DU NOUVEAU MOT DE PASSE
    ===================================================== */

    if ($mot_de_passe !== '') {

        if (strlen($mot_de_passe) < 8) {

            $erreurs[] =
                "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        }


        if (
            $mot_de_passe !==
            $confirmation
        ) {

            $erreurs[] =
                "Les deux mots de passe ne correspondent pas.";
        }
    }


    /* =====================================================
       VERIFICATION DE L'EMAIL
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
            AND id_utilisateur != :id_utilisateur
        ");

        $stmt->execute([
            'email' => $email,
            'id_utilisateur' => $id_utilisateur
        ]);


        if (
            (int) $stmt->fetchColumn() > 0
        ) {

            $erreurs[] =
                "Un autre utilisateur utilise déjà cette adresse email.";
        }
    }


    /* =====================================================
       MODIFICATION
    ===================================================== */

    if (empty($erreurs)) {


        /*
         * Si un nouveau mot de passe a été saisi.
         */

        if ($mot_de_passe !== '') {

            $hash = password_hash(
                $mot_de_passe,
                PASSWORD_DEFAULT
            );


            $stmt = $pdo->prepare("
                UPDATE utilisateurs

                SET
                    nom = :nom,
                    email = :email,
                    role = :role,
                    mot_de_passe = :mot_de_passe

                WHERE id_utilisateur = :id_utilisateur
            ");


            $stmt->execute([
                'nom' => $nom,
                'email' => $email,
                'role' => $role,
                'mot_de_passe' => $hash,
                'id_utilisateur' => $id_utilisateur
            ]);

        } else {


            /*
             * Aucun nouveau mot de passe :
             * on conserve l'ancien.
             */

            $stmt = $pdo->prepare("
                UPDATE utilisateurs

                SET
                    nom = :nom,
                    email = :email,
                    role = :role

                WHERE id_utilisateur = :id_utilisateur
            ");


            $stmt->execute([
                'nom' => $nom,
                'email' => $email,
                'role' => $role,
                'id_utilisateur' => $id_utilisateur
            ]);
        }


        /* =================================================
           MISE A JOUR DE LA SESSION SI C'EST MON COMPTE
        ================================================= */

        if ($estMonCompte) {

            $_SESSION['utilisateur']['nom'] =
                $nom;

            $_SESSION['utilisateur']['email'] =
                $email;

            $_SESSION['utilisateur']['role'] =
                $role;
        }


        header(
            'Location: /bibliotheque/utilisateurs/?modification=success'
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

            <h1>Modifier un utilisateur</h1>

            <p class="page-description">
                Modifiez les informations et les droits du compte.
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
                Le formulaire contient une ou plusieurs erreurs :
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

        <form method="POST">

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
                    >

                </div>


                <!-- ROLE -->

                <div class="form-group form-group-full">

                    <label for="role">

                        Rôle
                        <span class="required">*</span>

                    </label>


                    <?php if ($estMonCompte): ?>

                        <select
                            id="role"
                            name="role"
                            disabled
                        >

                            <option value="admin" selected>
                                Administrateur
                            </option>

                        </select>


                        <input
                            type="hidden"
                            name="role"
                            value="admin"
                        >


                        <small class="form-help">
                            Vous ne pouvez pas retirer le rôle administrateur
                            de votre propre compte.
                        </small>

                    <?php else: ?>

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

                    <?php endif; ?>

                </div>


                <!-- NOUVEAU MOT DE PASSE -->

                <div class="form-group">

                    <label for="mot_de_passe">
                        Nouveau mot de passe
                    </label>

                    <input
                        type="password"
                        id="mot_de_passe"
                        name="mot_de_passe"
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Laisser vide pour conserver l'ancien"
                    >

                    <small class="form-help">
                        Minimum 8 caractères.
                    </small>

                </div>


                <!-- CONFIRMATION -->

                <div class="form-group">

                    <label for="confirmation_mot_de_passe">
                        Confirmer le nouveau mot de passe
                    </label>

                    <input
                        type="password"
                        id="confirmation_mot_de_passe"
                        name="confirmation_mot_de_passe"
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Confirmer le nouveau mot de passe"
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
                    Enregistrer les modifications
                </button>

            </div>

        </form>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>