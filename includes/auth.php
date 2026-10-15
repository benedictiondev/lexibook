<?php

/* =========================================================
   DEMARRAGE DE LA SESSION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   VERIFICATION DE LA CONNEXION
========================================================= */

if (!isset($_SESSION['utilisateur'])) {

    header('Location: /bibliotheque/connexion.php');
    exit;
}