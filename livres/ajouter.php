<?php

require_once __DIR__ . '/../includes/auth.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/demo.php';

bloquer_modification_demo(
    '/bibliotheque/livres/index.php'
);


$erreurs = [];

$titre = '';
$auteur = '';
$isbn = '';
$categorie = '';
$annee_publication = '';
$nombre_exemplaires = 1;


/* =========================================================
   TRAITEMENT DU FORMULAIRE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  
        /* =================================================
           RECUPERATION DES DONNEES
        ================================================= */

        $titre = trim($_POST['titre'] ?? '');
        $auteur = trim($_POST['auteur'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '');
        $categorie = trim($_POST['categorie'] ?? '');

        $annee_publication =
            trim($_POST['annee_publication'] ?? '');

        $nombre_exemplaires =
            (int) ($_POST['nombre_exemplaires'] ?? 1);


        /* =================================================
           VALIDATION
        ================================================= */

        if ($titre === '') {

            $erreurs[] =
                "Le titre est obligatoire.";
        }


        if ($auteur === '') {

            $erreurs[] =
                "L'auteur est obligatoire.";
        }


        if ($nombre_exemplaires < 1) {

            $erreurs[] =
                "Le nombre d'exemplaires doit être supérieur ou égal à 1.";
        }


        /* =================================================
           VALIDATION DE L'ANNEE
        ================================================= */

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


        /* =================================================
           VERIFICATION ISBN
        ================================================= */

        if ($isbn !== '') {

            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM livres
                WHERE isbn = :isbn
            ");

            $stmt->execute([
                'isbn' => $isbn
            ]);


            if (
                (int) $stmt->fetchColumn() > 0
            ) {

                $erreurs[] =
                    "Un livre avec cet ISBN existe déjà.";
            }
        }


        /* =================================================
           INSERTION
        ================================================= */

        if (empty($erreurs)) {

            $stmt = $pdo->prepare("
                INSERT INTO livres (
                    titre,
                    auteur,
                    isbn,
                    categorie,
                    annee_publication,
                    nombre_exemplaires
                )

                VALUES (
                    :titre,
                    :auteur,
                    :isbn,
                    :categorie,
                    :annee_publication,
                    :nombre_exemplaires
                )
            ");


            $stmt->execute([

                'titre' =>
                    $titre,

                'auteur' =>
                    $auteur,

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
                    $nombre_exemplaires

            ]);


            header(
                'Location: /bibliotheque/livres/?ajout=success'
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

            <h1>Ajouter un livre</h1>

            <p class="page-description">
                Enregistrez un nouvel ouvrage dans la bibliothèque.
            </p>

        </div>


        <a
            href="/bibliotheque/livres/"
            class="btn btn-secondary"
        >
            ← Retour aux livres
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

        <form
            method="POST"
            class="book-form"
        >

            


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
                        placeholder="Ex. Le Petit Prince"
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
                        placeholder="Ex. Antoine de Saint-Exupéry"
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
                        placeholder="Ex. Roman"
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
                        placeholder="Ex. 9782070612758"
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
                        value="<?= htmlspecialchars($annee_publication) ?>"
                        placeholder="Ex. 1943"
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
                        min="1"
                        required
                        value="<?= (int) $nombre_exemplaires ?>"
                    >

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
                    Ajouter le livre
                </button>

            </div>

        </form>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>