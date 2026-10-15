<?php

require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../config/database.php';

/* =========================================================
   VERIFICATION DE LA METHODE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /bibliotheque/utilisateurs/');
    exit;
}


/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_utilisateur = filter_input(
    INPUT_POST,
    'id_utilisateur',
    FILTER_VALIDATE_INT
);


if (!$id_utilisateur) {

    header(
        'Location: /bibliotheque/utilisateurs/?suppression=erreur'
    );

    exit;
}


/* =========================================================
   INTERDICTION DE SUPPRIMER SON PROPRE COMPTE
========================================================= */

if (
    (int) $id_utilisateur ===
    (int) $_SESSION['utilisateur']['id']
) {

    header(
        'Location: /bibliotheque/utilisateurs/?suppression=soi_meme'
    );

    exit;
}


/* =========================================================
   RECUPERATION DE L'UTILISATEUR
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        id_utilisateur,
        nom,
        email,
        role

    FROM utilisateurs

    WHERE id_utilisateur = :id_utilisateur
");

$stmt->execute([
    'id_utilisateur' => $id_utilisateur
]);

$utilisateur = $stmt->fetch();


if (!$utilisateur) {

    header(
        'Location: /bibliotheque/utilisateurs/?suppression=introuvable'
    );

    exit;
}


/* =========================================================
   PROTECTION DU DERNIER ADMINISTRATEUR
========================================================= */

if ($utilisateur['role'] === 'admin') {

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM utilisateurs
        WHERE role = 'admin'
    ");

    $nombreAdmins = (int) $stmt->fetchColumn();


    if ($nombreAdmins <= 1) {

        header(
            'Location: /bibliotheque/utilisateurs/?suppression=dernier_admin'
        );

        exit;
    }
}


/* =========================================================
   SUPPRESSION
========================================================= */

try {

    $stmt = $pdo->prepare("
        DELETE FROM utilisateurs
        WHERE id_utilisateur = :id_utilisateur
    ");

    $stmt->execute([
        'id_utilisateur' => $id_utilisateur
    ]);


    if ($stmt->rowCount() !== 1) {

        header(
            'Location: /bibliotheque/utilisateurs/?suppression=erreur'
        );

        exit;
    }


    header(
        'Location: /bibliotheque/utilisateurs/?suppression=success'
    );

    exit;


} catch (Throwable $e) {

    header(
        'Location: /bibliotheque/utilisateurs/?suppression=erreur'
    );

    exit;
}