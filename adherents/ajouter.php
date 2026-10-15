<?php


require_once __DIR__ . '/../includes/auth.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/demo.php';

bloquer_modification_demo(
    '/bibliotheque/livres/index.php'
);

$erreurs = [];

$nom = '';
$prenom = '';
$email = '';
$telephone = '';
$adresse = '';
$actif = 1;


/* =========================================================
   TRAITEMENT DU FORMULAIRE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    $actif = isset($_POST['actif']) ? 1 : 0;


    /* =========================
       VALIDATION
    ========================== */

    if ($nom === '') {
        $erreurs[] = "Le nom est obligatoire.";
    }

    if ($prenom === '') {
        $erreurs[] = "Le prénom est obligatoire.";
    }

    if ($email === '') {
        $erreurs[] = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }


    /* =========================
       VERIFICATION EMAIL
    ========================== */

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM adherents
            WHERE email = :email
        ");

        $stmt->execute([
            'email' => $email
        ]);

        if ($stmt->fetchColumn() > 0) {
            $erreurs[] = "Un adhérent avec cette adresse email existe déjà.";
        }

    }


    /* =========================
       INSERTION
    ========================== */

    if (empty($erreurs)) {

        $stmt = $pdo->prepare("
            INSERT INTO adherents (
                nom,
                prenom,
                email,
                telephone,
                adresse,
                actif
            )
            VALUES (
                :nom,
                :prenom,
                :email,
                :telephone,
                :adresse,
                :actif
            )
        ");

        $stmt->execute([
            'nom' => strtoupper($nom),
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone !== '' ? $telephone : null,
            'adresse' => $adresse !== '' ? $adresse : null,
            'actif' => $actif
        ]);


        header(
            'Location: /bibliotheque/adherents/?ajout=success'
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
                Gestion des adhérents
            </p>

            <h1>Ajouter un adhérent</h1>

            <p class="page-description">
                Enregistrez un nouvel adhérent dans la bibliothèque.
            </p>

        </div>


        <a
            href="/bibliotheque/adherents/"
            class="btn btn-secondary"
        >
            ← Retour aux adhérents
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

        <form method="POST" class="adherent-form">

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
                        placeholder="Ex. DUPONT"
                    >

                </div>


                <!-- PRENOM -->

                <div class="form-group">

                    <label for="prenom">
                        Prénom
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="prenom"
                        name="prenom"
                        maxlength="100"
                        required
                        value="<?= htmlspecialchars($prenom) ?>"
                        placeholder="Ex. Marie"
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email
                        <span class="required">*</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="Ex. marie.dupont@email.com"
                    >

                </div>


                <!-- TELEPHONE -->

                <div class="form-group">

                    <label for="telephone">
                        Téléphone
                    </label>

                    <input
                        type="text"
                        id="telephone"
                        name="telephone"
                        maxlength="20"
                        value="<?= htmlspecialchars($telephone) ?>"
                        placeholder="Ex. 0612345678"
                    >

                </div>


                <!-- ADRESSE -->

                <div class="form-group form-group-full">

                    <label for="adresse">
                        Adresse
                    </label>

                    <input
                        type="text"
                        id="adresse"
                        name="adresse"
                        maxlength="255"
                        value="<?= htmlspecialchars($adresse) ?>"
                        placeholder="Ex. 12 rue de Paris"
                    >

                </div>


                <!-- STATUT -->

                <div class="form-group form-group-full">

                    <label class="checkbox-label">

                        <input
                            type="checkbox"
                            name="actif"
                            value="1"
                            <?= $actif ? 'checked' : '' ?>
                        >

                        <span>
                            Adhérent actif
                        </span>

                    </label>

                    <small class="form-help">
                        Un adhérent inactif ne pourra pas effectuer de nouvel emprunt.
                    </small>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="/bibliotheque/adherents/"
                    class="btn btn-secondary"
                >
                    Annuler
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Ajouter l'adhérent
                </button>

            </div>

        </form>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>