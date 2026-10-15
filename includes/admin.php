<?php

require_once __DIR__ . '/auth.php';


/* =========================================================
   VERIFICATION DU ROLE ADMIN
========================================================= */

if (
    !isset($_SESSION['utilisateur']['role'])
    ||
    $_SESSION['utilisateur']['role'] !== 'admin'
) {

    http_response_code(403);

    die("
        <h1>Accès interdit</h1>
        <p>Cette action est réservée aux administrateurs.</p>
        <p>
            <a href='/bibliotheque/dashboard.php'>
                Retour au tableau de bord
            </a>
        </p>
    ");
}