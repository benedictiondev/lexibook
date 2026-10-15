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

$id_adherent = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id_adherent) {
    header('Location: /bibliotheque/adherents/');
    exit;
}


/* =========================================================
   RECUPERATION DE L'ADHERENT
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM adherents
    WHERE id_adherent = :id_adherent
");

$stmt->execute([
    'id_adherent' => $id_adherent
]);

$adherent = $stmt->fetch();


if (!$adherent) {
    header('Location: /bibliotheque/adherents/');
    exit;
}


/* =========================================================
   VALEURS PAR DEFAUT
========================================================= */

$erreurs = [];

$nom = $adherent['nom'];
$prenom = $adherent['prenom'];
$email = $adherent['email'];
$telephone = $adherent['telephone'] ?? '';
$adresse = $adherent['adresse'] ?? '';
$actif = (int) $adherent['actif'];


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

    if (
        $email !== ''
        &&
        filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM adherents
            WHERE email = :email
            AND id_adherent != :id_adherent
        ");

        $stmt->execute([
            'email' => $email,
            'id_adherent' => $id_adherent
        ]);

        if ($stmt->fetchColumn() > 0) {
            $erreurs[] =
                "Un autre adhérent utilise déjà cette adresse email.";
        }
    }


    /* =========================
       MODIFICATION
    ========================== */

    if (empty($erreurs)) {

        $stmt = $pdo->prepare("
            UPDATE adherents

            SET
                nom = :nom,
                prenom = :prenom,
                email = :email,
                telephone = :telephone,
                adresse = :adresse,
                actif = :actif

            WHERE id_adherent = :id_adherent
        ");

        $stmt->execute([
            'nom' => strtoupper($nom),
            'prenom' => $prenom,
            'email' => $email,

            'telephone' =>
                $telephone !== ''
                    ? $telephone
                    : null,

            'adresse' =>
                $adresse !== ''
                    ? $adresse
                    : null,

            'actif' => $actif,

            'id_adherent' => $id_adherent
        ]);


        header(
            'Location: /bibliotheque/adherents/?modification=success'
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

            <h1>Modifier un adhérent</h1>

            <p class="page-description">
                Modifiez les informations de l'adhérent.
            </p>

        </div>


        <a
            href="/bibliotheque/adherents/"
            class="btn btn-secondary"
        >
            ← Retour aux adhérents
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
                    Enregistrer les modifications
                </button>

            </div>

        </form>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>