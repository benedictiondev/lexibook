<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/demo.php';

bloquer_modification_demo(
    '/bibliotheque/livres/index.php'
);


/* =========================================================
   VERIFICATION DE LA METHODE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /bibliotheque/emprunts/');
    exit;
}


/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_emprunt = filter_input(
    INPUT_POST,
    'id_emprunt',
    FILTER_VALIDATE_INT
);


if (!$id_emprunt) {

    header(
        'Location: /bibliotheque/emprunts/?retour=erreur'
    );

    exit;
}


/* =========================================================
   RECUPERATION DE L'EMPRUNT
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM emprunts
    WHERE id_emprunt = :id_emprunt
");

$stmt->execute([
    'id_emprunt' => $id_emprunt
]);

$emprunt = $stmt->fetch();


if (!$emprunt) {

    header(
        'Location: /bibliotheque/emprunts/?retour=introuvable'
    );

    exit;
}


/* =========================================================
   VERIFICATION : RETOUR DEJA EFFECTUE
========================================================= */

if ($emprunt['date_retour_reelle'] !== null) {

    header(
        'Location: /bibliotheque/emprunts/?retour=deja'
    );

    exit;
}


/* =========================================================
   ENREGISTREMENT DU RETOUR
========================================================= */

try {

    $pdo->beginTransaction();


    $stmt = $pdo->prepare("
        UPDATE emprunts

        SET
            date_retour_reelle = CURDATE(),
            statut = 'retourne'

        WHERE id_emprunt = :id_emprunt
        AND date_retour_reelle IS NULL
    ");

    $stmt->execute([
        'id_emprunt' => $id_emprunt
    ]);


    if ($stmt->rowCount() !== 1) {

        throw new Exception(
            "Le retour n'a pas pu être enregistré."
        );
    }


    $pdo->commit();


    header(
        'Location: /bibliotheque/emprunts/?retour=success'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    header(
        'Location: /bibliotheque/emprunts/?retour=erreur'
    );

    exit;
}