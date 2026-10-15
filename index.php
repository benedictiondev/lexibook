<?php

session_start();


if (isset($_SESSION['utilisateur'])) {

    header('Location: /bibliotheque/dashboard.php');

} else {

    header('Location: /bibliotheque/connexion.php');
}

exit;