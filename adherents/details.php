<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_adherent = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id_adherent) {
    header('Location: /bibliotheque/adherents/');
    exit;
}


/* =========================================================
   RECUPERATION DE L'ADHERENT
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
    header('Location: /bibliotheque/adherents/');
    exit;
}


/* =========================================================
   STATISTIQUES
========================================================= */

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM emprunts
    WHERE id_adherent = :id_adherent
");

$stmt->execute([
    'id_adherent' => $id_adherent
]);

$totalEmprunts = (int) $stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM emprunts
    WHERE id_adherent = :id_adherent
    AND date_retour_reelle IS NULL
");

$stmt->execute([
    'id_adherent' => $id_adherent
]);

$empruntsEnCours = (int) $stmt->fetchColumn();


/* =========================================================
   HISTORIQUE DES EMPRUNTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        e.id_emprunt,
        e.date_emprunt,
        e.date_retour_prevue,
        e.date_retour_reelle,
        l.id_livre,
        l.titre,
        l.auteur

    FROM emprunts e

    INNER JOIN livres l
        ON e.id_livre = l.id_livre

    WHERE e.id_adherent = :id_adherent

    ORDER BY e.date_emprunt DESC
");

$stmt->execute([
    'id_adherent' => $id_adherent
]);

$historique = $stmt->fetchAll();


require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

?>


<main class="main-content">

    <!-- =========================
         EN-TETE
    ========================== -->

    <div class="page-header">

        <div>

            <p class="page-label">
                Fiche adhérent
            </p>

            <h1>
                <?= htmlspecialchars(
                    $adherent['prenom']
                    . ' '
                    . $adherent['nom']
                ) ?>
            </h1>

            <p class="page-description">
                Informations détaillées sur l'adhérent.
            </p>

        </div>


        <div class="page-actions">

            <a
                href="/bibliotheque/adherents/"
                class="btn btn-secondary"
            >
                ← Retour
            </a>

            <a
                href="/bibliotheque/adherents/modifier.php?id=<?= $adherent['id_adherent'] ?>"
                class="btn btn-primary"
            >
                Modifier
            </a>

        </div>

    </div>


    <!-- =========================
         INFORMATIONS
    ========================== -->

    <div class="book-details-grid">

        <section class="details-card">

            <p class="section-label">
                Informations
            </p>

            <h2>
                Informations personnelles
            </h2>


            <div class="details-list">

                <div class="details-row">

                    <span>Nom</span>

                    <strong>
                        <?= htmlspecialchars($adherent['nom']) ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Prénom</span>

                    <strong>
                        <?= htmlspecialchars($adherent['prenom']) ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Email</span>

                    <strong>
                        <?= htmlspecialchars($adherent['email']) ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Téléphone</span>

                    <strong>
                        <?= !empty($adherent['telephone'])
                            ? htmlspecialchars($adherent['telephone'])
                            : '—'
                        ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Adresse</span>

                    <strong>
                        <?= !empty($adherent['adresse'])
                            ? htmlspecialchars($adherent['adresse'])
                            : '—'
                        ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Date d'inscription</span>

                    <strong>
                        <?= date(
                            'd/m/Y',
                            strtotime(
                                $adherent['date_inscription']
                            )
                        ) ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Statut</span>

                    <strong>

                        <?php if ((int) $adherent['actif'] === 1): ?>

                            <span class="status status-returned">
                                Actif
                            </span>

                        <?php else: ?>

                            <span class="status status-late">
                                Inactif
                            </span>

                        <?php endif; ?>

                    </strong>

                </div>

            </div>

        </section>


        <!-- =========================
             STATISTIQUES
        ========================== -->

        <section class="details-card">

            <p class="section-label">
                Activité
            </p>

            <h2>
                Emprunts
            </h2>


            <div class="book-stats">

                <div class="book-stat">

                    <span>
                        <?= $totalEmprunts ?>
                    </span>

                    <p>
                        Emprunts au total
                    </p>

                </div>


                <div class="book-stat">

                    <span>
                        <?= $empruntsEnCours ?>
                    </span>

                    <p>
                        Emprunts en cours
                    </p>

                </div>


                <div class="book-stat">

                    <span>
                        <?= $totalEmprunts - $empruntsEnCours ?>
                    </span>

                    <p>
                        Emprunts terminés
                    </p>

                </div>

            </div>


            <?php if ((int) $adherent['actif'] === 1): ?>

                <div class="availability-message available-message">
                    Cet adhérent peut effectuer de nouveaux emprunts.
                </div>

            <?php else: ?>

                <div class="availability-message unavailable-message">
                    Cet adhérent est inactif.
                </div>

            <?php endif; ?>

        </section>

    </div>


    <!-- =========================
         HISTORIQUE
    ========================== -->

    <section class="dashboard-section">

        <div class="dashboard-section-header">

            <div>

                <p class="section-label">
                    Historique
                </p>

                <h2>
                    Emprunts de l'adhérent
                </h2>

            </div>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>Livre</th>
                        <th>Auteur</th>
                        <th>Date d'emprunt</th>
                        <th>Retour prévu</th>
                        <th>Retour réel</th>
                        <th>Statut</th>
                    </tr>

                </thead>


                <tbody>

                <?php if (empty($historique)): ?>

                    <tr>

                        <td
                            colspan="6"
                            class="empty-table"
                        >
                            Aucun emprunt enregistré pour cet adhérent.
                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach ($historique as $emprunt): ?>

                        <?php

                        if ($emprunt['date_retour_reelle'] !== null) {

                            $statut = 'Retourné';
                            $classe = 'status-returned';

                        } elseif (
                            $emprunt['date_retour_prevue']
                            < date('Y-m-d')
                        ) {

                            $statut = 'En retard';
                            $classe = 'status-late';

                        } else {

                            $statut = 'En cours';
                            $classe = 'status-active';

                        }

                        ?>


                        <tr>

                            <td>

                                <a
                                    href="/bibliotheque/livres/details.php?id=<?= $emprunt['id_livre'] ?>"
                                    class="action-link"
                                >
                                    <?= htmlspecialchars(
                                        $emprunt['titre']
                                    ) ?>
                                </a>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $emprunt['auteur']
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    'd/m/Y',
                                    strtotime(
                                        $emprunt['date_emprunt']
                                    )
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    'd/m/Y',
                                    strtotime(
                                        $emprunt['date_retour_prevue']
                                    )
                                ) ?>

                            </td>


                            <td>

                                <?php if ($emprunt['date_retour_reelle']): ?>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $emprunt['date_retour_reelle']
                                        )
                                    ) ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>


                            <td>

                                <span class="status <?= $classe ?>">
                                    <?= $statut ?>
                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>