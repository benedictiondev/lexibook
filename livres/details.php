<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/* =========================================================
   RECUPERATION DE L'ID
========================================================= */

$id_livre = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id_livre) {
    header('Location: /bibliotheque/livres/');
    exit;
}


/* =========================================================
   RECUPERATION DU LIVRE
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        l.*,

        (
            SELECT COUNT(*)
            FROM emprunts e
            WHERE e.id_livre = l.id_livre
            AND e.date_retour_reelle IS NULL
        ) AS exemplaires_empruntes

    FROM livres l

    WHERE l.id_livre = :id_livre
");

$stmt->execute([
    'id_livre' => $id_livre
]);

$livre = $stmt->fetch();


if (!$livre) {
    header('Location: /bibliotheque/livres/');
    exit;
}


/* =========================================================
   DISPONIBILITE
========================================================= */

$nombreExemplaires =
    (int) $livre['nombre_exemplaires'];

$exemplairesEmpruntes =
    (int) $livre['exemplaires_empruntes'];

$exemplairesDisponibles =
    $nombreExemplaires - $exemplairesEmpruntes;


/* =========================================================
   HISTORIQUE DES EMPRUNTS
========================================================= */

$stmt = $pdo->prepare("
    SELECT
        e.id_emprunt,
        e.date_emprunt,
        e.date_retour_prevue,
        e.date_retour_reelle,

        a.nom,
        a.prenom

    FROM emprunts e

    INNER JOIN adherents a
        ON e.id_adherent = a.id_adherent

    WHERE e.id_livre = :id_livre

    ORDER BY e.date_emprunt DESC
");

$stmt->execute([
    'id_livre' => $id_livre
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
                Fiche livre
            </p>

            <h1>
                <?= htmlspecialchars($livre['titre']) ?>
            </h1>

            <p class="page-description">
                Informations détaillées sur l'ouvrage.
            </p>

        </div>


        <div class="page-actions">

            <a
                href="/bibliotheque/livres/"
                class="btn btn-secondary"
            >
                ← Retour
            </a>


            <a
                href="/bibliotheque/livres/modifier.php?id=<?= $livre['id_livre'] ?>"
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
                Informations générales
            </h2>


            <div class="details-list">

                <div class="details-row">

                    <span>Titre</span>

                    <strong>
                        <?= htmlspecialchars($livre['titre']) ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Auteur</span>

                    <strong>
                        <?= htmlspecialchars($livre['auteur']) ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Catégorie</span>

                    <strong>
                        <?= !empty($livre['categorie'])
                            ? htmlspecialchars($livre['categorie'])
                            : '—'
                        ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>ISBN</span>

                    <strong>
                        <?= !empty($livre['isbn'])
                            ? htmlspecialchars($livre['isbn'])
                            : '—'
                        ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Année de publication</span>

                    <strong>
                        <?= !empty($livre['annee_publication'])
                            ? htmlspecialchars(
                                (string) $livre['annee_publication']
                            )
                            : '—'
                        ?>
                    </strong>

                </div>


                <div class="details-row">

                    <span>Date d'ajout</span>

                    <strong>
                        <?= date(
                            'd/m/Y',
                            strtotime($livre['date_ajout'])
                        ) ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- =========================
             DISPONIBILITE
        ========================== -->

        <section class="details-card">

            <p class="section-label">
                Disponibilité
            </p>

            <h2>
                Exemplaires
            </h2>


            <div class="book-stats">

                <div class="book-stat">

                    <span>
                        <?= $nombreExemplaires ?>
                    </span>

                    <p>
                        Exemplaires au total
                    </p>

                </div>


                <div class="book-stat">

                    <span>
                        <?= $exemplairesEmpruntes ?>
                    </span>

                    <p>
                        Actuellement empruntés
                    </p>

                </div>


                <div class="book-stat">

                    <span>
                        <?= $exemplairesDisponibles ?>
                    </span>

                    <p>
                        Disponibles
                    </p>

                </div>

            </div>


            <?php if ($exemplairesDisponibles > 0): ?>

                <div class="availability-message available-message">

                    Ce livre peut actuellement être emprunté.

                </div>

            <?php else: ?>

                <div class="availability-message unavailable-message">

                    Tous les exemplaires sont actuellement empruntés.

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
                    Emprunts du livre
                </h2>

            </div>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>
                        <th>Adhérent</th>
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
                            colspan="5"
                            class="empty-table"
                        >
                            Aucun emprunt enregistré pour ce livre.
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

                                <?= htmlspecialchars(
                                    $emprunt['prenom']
                                    . ' '
                                    . $emprunt['nom']
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