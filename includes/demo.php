<?php

function est_mode_demo(): bool
{
    return isset($_SESSION['utilisateur']['demo'])
        && $_SESSION['utilisateur']['demo'] === true;
}


function bloquer_modification_demo(
    string $redirection = '/bibliotheque/dashboard.php'
): void {

    if (
        est_mode_demo()
        &&
        $_SERVER['REQUEST_METHOD'] === 'POST'
    ) {

        $_SESSION['message_demo'] =
            "Mode démonstration : les modifications sont désactivées.";

        header(
            'Location: ' . $redirection
        );

        exit;
    }
}