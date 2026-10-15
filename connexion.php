<?php

session_start();

require_once __DIR__ . '/config/database.php';


/* =========================================================
   UTILISATEUR DEJA CONNECTE
========================================================= */

if (isset($_SESSION['utilisateur'])) {

    header('Location: /bibliotheque/dashboard.php');
    exit;
}


/* =========================================================
   VARIABLES
========================================================= */

$erreur = '';

$email = '';

// Désactivée par défaut : à activer seulement sur une instance de démonstration isolée.
$demoActive = getenv('LEXIBOOK_ENABLE_DEMO') === '1';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    $demoActive
    && isset($_POST['connexion_demo'])
) {

    $emailDemo = 'demo@lexibook.fr';


    $requete = $pdo->prepare(
        "SELECT
            id_utilisateur,
            nom,
            email,
            role

         FROM utilisateurs

         WHERE email = ?"
    );


    $requete->execute([
        $emailDemo
    ]);


    $utilisateur =
        $requete->fetch(PDO::FETCH_ASSOC);


    if ($utilisateur) {

        $_SESSION['utilisateur'] = [
            'id' =>
                $utilisateur['id_utilisateur'],

            'nom' =>
                $utilisateur['nom'],

            'email' =>
                $utilisateur['email'],

            'role' =>
                $utilisateur['role'],

            'demo' =>
                true
        ];


        header(
            'Location: /bibliotheque/dashboard.php'
        );

        exit;
    }
}
/* =========================================================
   TRAITEMENT DU FORMULAIRE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    $mot_de_passe = $_POST['mot_de_passe'] ?? '';


    /* =========================
       VALIDATION
    ========================== */

    if ($email === '' || $mot_de_passe === '') {

        $erreur =
            "Veuillez renseigner votre email et votre mot de passe.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur =
            "L'adresse email saisie n'est pas valide.";

    } else {

        /* =========================
           RECHERCHE UTILISATEUR
        ========================== */

        $stmt = $pdo->prepare("
            SELECT
                id_utilisateur,
                nom,
                email,
                mot_de_passe,
                role

            FROM utilisateurs

            WHERE email = :email

            LIMIT 1
        ");

        $stmt->execute([
            'email' => $email
        ]);

        $utilisateur = $stmt->fetch();


        /* =========================
           VERIFICATION MOT DE PASSE
        ========================== */

        if (
            $utilisateur
            &&
            password_verify(
                $mot_de_passe,
                $utilisateur['mot_de_passe']
            )
        ) {

            /*
             * Protection contre la fixation de session.
             */

            session_regenerate_id(true);


            $_SESSION['utilisateur'] = [

                'id' =>
                    (int) $utilisateur['id_utilisateur'],

                'nom' =>
                    $utilisateur['nom'],

                'email' =>
                    $utilisateur['email'],

                'role' =>
                    $utilisateur['role']

            ];


            header(
                'Location: /bibliotheque/dashboard.php'
            );

            exit;

        } else {

            /*
             * Message volontairement général :
             * on ne précise pas si l'email ou
             * le mot de passe est incorrect.
             */

            $erreur =
                "Email ou mot de passe incorrect.";
        }
    }
}

?>


<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Connexion — Gestion de LEXIBOOK
    </title>

    <link
        rel="stylesheet"
        href="/bibliotheque/css/style.css"
    >

</head>


<body class="login-page">


<main class="login-container">

    <section class="login-card">


        <div class="login-header">

            <div class="login-logo">
    <img
        src="images/logolexibook.png"
        alt="LexiBook"
    >
</div>

            <h1>Connexion</h1>

            <p>
                Connectez-vous pour accéder
                à l'application de gestion.
            </p>

        </div>


        <?php if ($erreur !== ''): ?>

            <div class="alert alert-error">

                <?= htmlspecialchars($erreur) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label for="email">
                    Adresse email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="exemple@email.com"
                >

            </div>


            <div class="form-group">

                <label for="mot_de_passe">
                    Mot de passe
                </label>

                <input
                    type="password"
                    id="mot_de_passe"
                    name="mot_de_passe"
                    required
                    autocomplete="current-password"
                    placeholder="Votre mot de passe"
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary login-button"
            >
                Se connecter
            </button>


        </form>
        <?php if ($demoActive): ?>
        <div class="demo-access">

    <p>
        Vous souhaitez simplement découvrir LexiBook ?
    </p>

    <form method="POST">
        <input
            type="hidden"
            name="connexion_demo"
            value="1"
        >

        <button
            type="submit"
            class="btn-demo"
        >
            Accéder à la démo
        </button>
    </form>

</div>
        <?php endif; ?>

    </section>

</main>


</body>

</html>