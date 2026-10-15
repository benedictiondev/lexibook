<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/demo.php';

bloquer_modification_demo(
    '/bibliotheque/livres/index.php'
);


/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_livre = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id_livre) {
    header('Location: /bibliotheque/livres/');
    exit;
}


/* =========================================================
   RECUPERATION DU LIVRE
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM livres
    WHERE id_livre = :id_livre
");

$stmt->execute([
    'id_livre' => $id_livre
]);

$livre = $stmt->fetch();


if (!$livre) {
    header('Location: /bibliotheque/livres/');
    exit;
}


/* =========================================================
   NOMBRE D'EXEMPLAIRES ACTUELLEMENT EMPRUNTES
========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM emprunts
    WHERE id_livre = :id_livre
    AND date_retour_reelle IS NULL
");

$stmt->execute([
    'id_livre' => $id_livre
]);

$exemplaires_empruntes = (int) $stmt->fetchColumn();


/* =========================================================
   VALEURS PAR DEFAUT
========================================================= */

$erreurs = [];

$titre = $livre['titre'];
$auteur = $livre['auteur'];
$isbn = $livre['isbn'] ?? '';
$categorie = $livre['categorie'] ?? '';
$annee_publication = $livre['annee_publication'] ?? '';
$nombre_exemplaires = (int) $livre['nombre_exemplaires'];


/* =========================================================
   TRAITEMENT DU FORMULAIRE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $annee_publication = trim($_POST['annee_publication'] ?? '');

    $nombre_exemplaires =
        (int) ($_POST['nombre_exemplaires'] ?? 1);


    /* =========================
       VALIDATION
    ========================== */

    if ($titre === '') {
        $erreurs[] = "Le titre est obligatoire.";
    }

    if ($auteur === '') {
        $erreurs[] = "L'auteur est obligatoire.";
    }


    if ($nombre_exemplaires < 1) {

        $erreurs[] =
            "Le nombre d'exemplaires doit être supérieur ou égal à 1.";
    }


    /*
     * On ne peut pas déclarer moins d'exemplaires
     * que le nombre actuellement emprunté.
     */

    if ($nombre_exemplaires < $exemplaires_empruntes) {

        $erreurs[] =
            "Impossible de réduire le nombre d'exemplaires à "
            . $nombre_exemplaires
            . " car "
            . $exemplaires_empruntes
            . " exemplaire(s) sont actuellement emprunté(s).";
    }


    if (
        $annee_publication !== ''
        &&
        (
            !ctype_digit($annee_publication)
            ||
            (int) $annee_publication < 0
            ||
            (int) $annee_publication > (int) date('Y')
        )
    ) {

        $erreurs[] =
            "L'année de publication n'est pas valide.";
    }


    /* =========================
       VERIFICATION ISBN
    ========================== */

    if ($isbn !== '') {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM livres
            WHERE isbn = :isbn
            AND id_livre != :id_livre
        ");

        $stmt->execute([
            'isbn' => $isbn,
            'id_livre' => $id_livre
        ]);


        if ($stmt->fetchColumn() > 0) {

            $erreurs[] =
                "Un autre livre avec cet ISBN existe déjà.";
        }

    }


    /* =========================
       MODIFICATION
    ========================== */

    if (empty($erreurs)) {

        $stmt = $pdo->prepare("
            UPDATE livres

            SET
                titre = :titre,
                auteur = :auteur,
                isbn = :isbn,
                categorie = :categorie,
                annee_publication = :annee_publication,
                nombre_exemplaires = :nombre_exemplaires

            WHERE id_livre = :id_livre
        ");


        $stmt->execute([
            'titre' => $titre,
            'auteur' => $auteur,

            'isbn' =>
                $isbn !== ''
                    ? $isbn
                    : null,

            'categorie' =>
                $categorie !== ''
                    ? $categorie
                    : null,

            'annee_publication' =>
                $annee_publication !== ''
                    ? (int) $annee_publication
                    : null,

            'nombre_exemplaires' =>
                $nombre_exemplaires,

            'id_livre' =>
                $id_livre
        ]);


        header(
            'Location: /bibliotheque/livres/?modification=success'
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
                Gestion des livres
            </p>

            <h1>Modifier un livre</h1>

            <p class="page-description">
                Modifiez les informations de l'ouvrage.
            </p>

        </div>


        <a
            href="/bibliotheque/livres/"
            class="btn btn-secondary"
        >
            ← Retour aux livres
        </a>

    </div>


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


    <div class="form-card">

        <form method="POST" class="book-form">

            <div class="form-grid">


                <!-- TITRE -->

                <div class="form-group form-group-full">

                    <label for="titre">
                        Titre
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="titre"
                        name="titre"
                        maxlength="200"
                        required
                        value="<?= htmlspecialchars($titre) ?>"
                    >

                </div>


                <!-- AUTEUR -->

                <div class="form-group">

                    <label for="auteur">
                        Auteur
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="auteur"
                        name="auteur"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($auteur) ?>"
                    >

                </div>


                <!-- CATEGORIE -->

                <div class="form-group">

                    <label for="categorie">
                        Catégorie
                    </label>

                    <input
                        type="text"
                        id="categorie"
                        name="categorie"
                        maxlength="100"
                        value="<?= htmlspecialchars($categorie) ?>"
                    >

                </div>


                <!-- ISBN -->

                <div class="form-group">

                    <label for="isbn">
                        ISBN
                    </label>

                    <input
                        type="text"
                        id="isbn"
                        name="isbn"
                        maxlength="20"
                        value="<?= htmlspecialchars($isbn) ?>"
                    >

                </div>


                <!-- ANNEE -->

                <div class="form-group">

                    <label for="annee_publication">
                        Année de publication
                    </label>

                    <input
                        type="number"
                        id="annee_publication"
                        name="annee_publication"
                        min="0"
                        max="<?= date('Y') ?>"
                        value="<?= htmlspecialchars(
                            (string) $annee_publication
                        ) ?>"
                    >

                </div>


                <!-- EXEMPLAIRES -->

                <div class="form-group">

                    <label for="nombre_exemplaires">
                        Nombre d'exemplaires
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        id="nombre_exemplaires"
                        name="nombre_exemplaires"
                        min="<?= max(1, $exemplaires_empruntes) ?>"
                        required
                        value="<?= $nombre_exemplaires ?>"
                    >

                    <?php if ($exemplaires_empruntes > 0): ?>

                        <small class="form-help">

                            <?= $exemplaires_empruntes ?>

                            exemplaire<?= $exemplaires_empruntes > 1 ? 's' : '' ?>

                            actuellement emprunté<?= $exemplaires_empruntes > 1 ? 's' : '' ?>.

                        </small>

                    <?php endif; ?>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="/bibliotheque/livres/"
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