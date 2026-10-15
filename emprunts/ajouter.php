<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/demo.php';

bloquer_modification_demo(
    '/bibliotheque/livres/index.php'
);


/* =========================================================
   VALEURS PAR DÉFAUT
========================================================= */

$erreurs = [];

$id_livre = '';
$id_adherent = '';

$date_emprunt = date('Y-m-d');

$date_retour_prevue = date(
    'Y-m-d',
    strtotime('+14 days')
);


/* =========================================================
   RÉCUPÉRATION DES ADHÉRENTS ACTIFS
========================================================= */

$stmt = $pdo->query("
    SELECT
        id_adherent,
        nom,
        prenom,
        email

    FROM adherents

    WHERE actif = 1

    ORDER BY nom ASC, prenom ASC
");

$adherents = $stmt->fetchAll();


/* =========================================================
   RÉCUPÉRATION DES LIVRES DISPONIBLES
========================================================= */

$stmt = $pdo->query("
    SELECT
        l.id_livre,
        l.titre,
        l.auteur,
        l.nombre_exemplaires,

        (
            l.nombre_exemplaires
            -
            (
                SELECT COUNT(*)

                FROM emprunts e

                WHERE e.id_livre = l.id_livre
                AND e.date_retour_reelle IS NULL
            )
        ) AS exemplaires_disponibles

    FROM livres l

    HAVING exemplaires_disponibles > 0

    ORDER BY l.titre ASC
");

$livres = $stmt->fetchAll();


/* =========================================================
   TRAITEMENT DU FORMULAIRE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livre = filter_input(
        INPUT_POST,
        'id_livre',
        FILTER_VALIDATE_INT
    );

    $id_adherent = filter_input(
        INPUT_POST,
        'id_adherent',
        FILTER_VALIDATE_INT
    );

    $date_emprunt = trim(
        $_POST['date_emprunt'] ?? ''
    );

    $date_retour_prevue = trim(
        $_POST['date_retour_prevue'] ?? ''
    );


    /* =====================================================
       VALIDATION DES CHAMPS
    ===================================================== */

    if (!$id_livre) {
        $erreurs[] = "Veuillez sélectionner un livre.";
    }

    if (!$id_adherent) {
        $erreurs[] = "Veuillez sélectionner un adhérent.";
    }

    if ($date_emprunt === '') {
        $erreurs[] = "La date d'emprunt est obligatoire.";
    }

    if ($date_retour_prevue === '') {
        $erreurs[] = "La date de retour prévue est obligatoire.";
    }


    if (
        $date_emprunt !== ''
        &&
        $date_retour_prevue !== ''
        &&
        $date_retour_prevue < $date_emprunt
    ) {
        $erreurs[] =
            "La date de retour prévue ne peut pas être antérieure à la date d'emprunt.";
    }


    /* =====================================================
       VÉRIFICATION DE L'ADHÉRENT
    ===================================================== */

    if ($id_adherent) {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)

            FROM adherents

            WHERE id_adherent = :id_adherent
            AND actif = 1
        ");

        $stmt->execute([
            'id_adherent' => $id_adherent
        ]);


        if ((int) $stmt->fetchColumn() === 0) {

            $erreurs[] =
                "Cet adhérent n'existe pas ou n'est pas actif.";
        }
    }


    /* =====================================================
       VÉRIFICATION DU LIVRE ET DE SA DISPONIBILITÉ
    ===================================================== */

    if ($id_livre) {

        $stmt = $pdo->prepare("
            SELECT
                l.nombre_exemplaires,

                (
                    SELECT COUNT(*)

                    FROM emprunts e

                    WHERE e.id_livre = l.id_livre
                    AND e.date_retour_reelle IS NULL
                ) AS exemplaires_empruntes

            FROM livres l

            WHERE l.id_livre = :id_livre
        ");

        $stmt->execute([
            'id_livre' => $id_livre
        ]);

        $livreSelectionne = $stmt->fetch();


        if (!$livreSelectionne) {

            $erreurs[] =
                "Le livre sélectionné est introuvable.";

        } else {

            $disponibles =
                (int) $livreSelectionne['nombre_exemplaires']
                -
                (int) $livreSelectionne['exemplaires_empruntes'];


            if ($disponibles <= 0) {

                $erreurs[] =
                    "Aucun exemplaire de ce livre n'est actuellement disponible.";
            }
        }
    }


    /* =====================================================
       INSERTION DE L'EMPRUNT
    ===================================================== */

    if (empty($erreurs)) {

        try {

            $pdo->beginTransaction();


            /*
             * On revérifie la disponibilité juste avant
             * l'insertion.
             */

            $stmt = $pdo->prepare("
                SELECT
                    l.nombre_exemplaires,

                    (
                        SELECT COUNT(*)

                        FROM emprunts e

                        WHERE e.id_livre = l.id_livre
                        AND e.date_retour_reelle IS NULL
                    ) AS exemplaires_empruntes

                FROM livres l

                WHERE l.id_livre = :id_livre

                FOR UPDATE
            ");

            $stmt->execute([
                'id_livre' => $id_livre
            ]);

            $verificationLivre = $stmt->fetch();


            if (!$verificationLivre) {
                throw new Exception(
                    "Le livre sélectionné est introuvable."
                );
            }


            $disponibles =
                (int) $verificationLivre['nombre_exemplaires']
                -
                (int) $verificationLivre['exemplaires_empruntes'];


            if ($disponibles <= 0) {
                throw new Exception(
                    "Ce livre n'est plus disponible."
                );
            }


            $stmt = $pdo->prepare("
                INSERT INTO emprunts (
                    id_livre,
                    id_adherent,
                    date_emprunt,
                    date_retour_prevue,
                    statut
                )

                VALUES (
                    :id_livre,
                    :id_adherent,
                    :date_emprunt,
                    :date_retour_prevue,
                    'en_cours'
                )
            ");


            $stmt->execute([
                'id_livre' => $id_livre,
                'id_adherent' => $id_adherent,
                'date_emprunt' => $date_emprunt,
                'date_retour_prevue' => $date_retour_prevue
            ]);


            $pdo->commit();


            header(
                'Location: /bibliotheque/emprunts/?ajout=success'
            );

            exit;


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erreurs[] = $e->getMessage();
        }
    }
}


require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

?>


<main class="main-content">

    <!-- =========================
         EN-TÊTE
    ========================== -->

    <div class="page-header">

        <div>

            <p class="page-label">
                Gestion des emprunts
            </p>

            <h1>Nouvel emprunt</h1>

            <p class="page-description">
                Enregistrez le prêt d'un livre à un adhérent.
            </p>

        </div>


        <a
            href="/bibliotheque/emprunts/"
            class="btn btn-secondary"
        >
            ← Retour aux emprunts
        </a>

    </div>


    <!-- =========================
         ERREURS
    ========================== -->

    <?php if (!empty($erreurs)): ?>

        <div class="alert alert-error">

            <strong>
                L'emprunt n'a pas pu être enregistré :
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


                <!-- ADHÉRENT -->

                <div class="form-group form-group-full">

                    <label for="id_adherent">

                        Adhérent
                        <span class="required">*</span>

                    </label>


                    <select
                        id="id_adherent"
                        name="id_adherent"
                        required
                    >

                        <option value="">
                            Sélectionner un adhérent
                        </option>


                        <?php foreach ($adherents as $adherent): ?>

                            <option
                                value="<?= $adherent['id_adherent'] ?>"
                                <?= (string) $id_adherent ===
                                    (string) $adherent['id_adherent']
                                        ? 'selected'
                                        : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $adherent['prenom']
                                    . ' '
                                    . $adherent['nom']
                                    . ' — '
                                    . $adherent['email']
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- LIVRE -->

                <div class="form-group form-group-full">

                    <label for="id_livre">

                        Livre
                        <span class="required">*</span>

                    </label>


                    <select
                        id="id_livre"
                        name="id_livre"
                        required
                    >

                        <option value="">
                            Sélectionner un livre
                        </option>


                        <?php foreach ($livres as $livre): ?>

                            <option
                                value="<?= $livre['id_livre'] ?>"
                                <?= (string) $id_livre ===
                                    (string) $livre['id_livre']
                                        ? 'selected'
                                        : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $livre['titre']
                                    . ' — '
                                    . $livre['auteur']
                                ) ?>

                                (
                                <?= (int) $livre['exemplaires_disponibles'] ?>
                                disponible<?= (int) $livre['exemplaires_disponibles'] > 1 ? 's' : '' ?>
                                )

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- DATE EMPRUNT -->

                <div class="form-group">

                    <label for="date_emprunt">

                        Date d'emprunt
                        <span class="required">*</span>

                    </label>

                    <input
                        type="date"
                        id="date_emprunt"
                        name="date_emprunt"
                        required
                        value="<?= htmlspecialchars($date_emprunt) ?>"
                    >

                </div>


                <!-- DATE RETOUR -->

                <div class="form-group">

                    <label for="date_retour_prevue">

                        Retour prévu
                        <span class="required">*</span>

                    </label>

                    <input
                        type="date"
                        id="date_retour_prevue"
                        name="date_retour_prevue"
                        required
                        value="<?= htmlspecialchars($date_retour_prevue) ?>"
                    >

                </div>

            </div>


            <!-- =========================
                 INFORMATION
            ========================== -->

            <div class="loan-info">

                <strong>Durée conseillée</strong>

                <p>
                    La date de retour proposée automatiquement
                    correspond à un emprunt de 14 jours.
                </p>

            </div>


            <div class="form-actions">

                <a
                    href="/bibliotheque/emprunts/"
                    class="btn btn-secondary"
                >
                    Annuler
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Enregistrer l'emprunt
                </button>

            </div>

        </form>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>