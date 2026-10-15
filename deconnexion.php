<?php

session_start();


/* =========================================================
   SUPPRESSION DES DONNEES DE SESSION
========================================================= */

$_SESSION = [];


/* =========================================================
   SUPPRESSION DU COOKIE DE SESSION
========================================================= */

if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}


/* =========================================================
   DESTRUCTION DE LA SESSION
========================================================= */

session_destroy();


/* =========================================================
   REDIRECTION
========================================================= */

header('Location: /bibliotheque/connexion.php');
exit;