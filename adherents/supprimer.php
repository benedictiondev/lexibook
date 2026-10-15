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

    header('Location: /bibliotheque/adherents/');
    exit;
}


/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_adherent = filter_input(
    INPUT_POST,
    'id_adherent',
    FILTER_VALIDATE_INT
);


if (!$id_adherent) {

    header(
        'Location: /bibliotheque/adherents/?suppression=erreur'
    );

    exit;
}


/* =========================================================
   VERIFICATION DE L'ADHERENT
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

    header(
        'Location: /bibliotheque/adherents/?suppression=introuvable'
    );

    exit;
}


/* =========================================================
   VERIFICATION DE L'HISTORIQUE D'EMPRUNTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM emprunts
    WHERE id_adherent = :id_adherent
");

$stmt->execute([
    'id_adherent' => $id_adherent
]);

$nombreEmprunts = (int) $stmt->fetchColumn();


if ($nombreEmprunts > 0) {

    header(
        'Location: /bibliotheque/adherents/?suppression=historique'
    );

    exit;
}


/* =========================================================
   SUPPRESSION
========================================================= */

$stmt = $pdo->prepare("
    DELETE FROM adherents
    WHERE id_adherent = :id_adherent
");

$stmt->execute([
    'id_adherent' => $id_adherent
]);


header(
    'Location: /bibliotheque/adherents/?suppression=success'
);

exit;