<?php

require_once __DIR__ . '/../includes/admin.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/demo.php';

bloquer_modification_demo(
    '/bibliotheque/livres/index.php'
);


/* =========================================================
   VERIFICATION DE LA METHODE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /bibliotheque/livres/');
    exit;
}

/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_livre = filter_input(
    INPUT_POST,
    'id_livre',
    FILTER_VALIDATE_INT
);


if (!$id_livre) {

    header(
        'Location: /bibliotheque/livres/?suppression=erreur'
    );

    exit;
}


/* =========================================================
   VERIFICATION DU LIVRE
========================================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM livres
    WHERE id_livre = :id_livre
");

$stmt->execute([
    'id_livre' => $id_livre
]);

$livre = $stmt->fetch();


if (!$livre) {

    header(
        'Location: /bibliotheque/livres/?suppression=introuvable'
    );

    exit;
}


/* =========================================================
   VERIFICATION DE L'HISTORIQUE D'EMPRUNTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM emprunts
    WHERE id_livre = :id_livre
");

$stmt->execute([
    'id_livre' => $id_livre
]);

$nombreEmprunts = (int) $stmt->fetchColumn();


if ($nombreEmprunts > 0) {

    header(
        'Location: /bibliotheque/livres/?suppression=historique'
    );

    exit;
}


/* =========================================================
   SUPPRESSION
========================================================= */

$stmt = $pdo->prepare("
    DELETE FROM livres
    WHERE id_livre = :id_livre
");

$stmt->execute([
    'id_livre' => $id_livre
]);


header(
    'Location: /bibliotheque/livres/?suppression=success'
);

exit;