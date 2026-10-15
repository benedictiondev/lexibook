<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

/* =========================================================
   STATISTIQUES
========================================================= */

// Nombre total d'exemplaires
$stmt = $pdo->query("
    SELECT COALESCE(SUM(nombre_exemplaires), 0)
    FROM livres
");

$totalLivres = $stmt->fetchColumn();


// Nombre d'adhérents actifs
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM adherents
    WHERE actif = 1
");

$totalAdherents = $stmt->fetchColumn();


// Emprunts actuellement en cours
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM emprunts
    WHERE date_retour_reelle IS NULL
");

$totalEmprunts = $stmt->fetchColumn();


// Emprunts en retard
$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM emprunts
    WHERE date_retour_reelle IS NULL
    AND date_retour_prevue < CURDATE()
");

$totalRetards = $stmt->fetchColumn();


// Derniers emprunts
$stmt = $pdo->query("
    SELECT
        emprunts.id_emprunt,
        livres.titre,
        adherents.nom,
        adherents.prenom,
        emprunts.date_emprunt,
        emprunts.date_retour_prevue,
        emprunts.date_retour_reelle
    FROM emprunts
    INNER JOIN livres
        ON emprunts.id_livre = livres.id_livre
    INNER JOIN adherents
        ON emprunts.id_adherent = adherents.id_adherent
    ORDER BY emprunts.id_emprunt DESC
    LIMIT 5
");

$derniersEmprunts = $stmt->fetchAll();


require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

?>

<main class="main-content">

    <div class="page-header">

        <div>
            <p class="page-label">Bibliothèque</p>
            <h1>Tableau de bord</h1>

            <p class="page-description">
                Vue d'ensemble de la gestion de la bibliothèque.
            </p>
        </div>

        <a href="/bibliotheque/emprunts/ajouter.php"
           class="btn btn-primary">

            + Nouvel emprunt

        </a>

    </div>


    <!-- =========================
         STATISTIQUES
    ========================== -->

    <section class="dashboard-stats">

        <article class="stat-card">

            <span class="stat-number">
                <?= $totalLivres ?>
            </span>

            <div>
                <h2>Livres</h2>
                <p>Exemplaires disponibles dans la bibliothèque</p>
            </div>

        </article>


        <article class="stat-card">

            <span class="stat-number">
                <?= $totalAdherents ?>
            </span>

            <div>
                <h2>Adhérents</h2>
                <p>Adhérents actuellement actifs</p>
            </div>

        </article>


        <article class="stat-card">

            <span class="stat-number">
                <?= $totalEmprunts ?>
            </span>

            <div>
                <h2>Emprunts</h2>
                <p>Emprunts non encore retournés</p>
            </div>

        </article>


        <article class="stat-card stat-card-warning">

            <span class="stat-number">
                <?= $totalRetards ?>
            </span>

            <div>
                <h2>Retards</h2>
                <p>Livres dont la date de retour est dépassée</p>
            </div>

        </article>

    </section>


    <!-- =========================
         ACTIONS RAPIDES
    ========================== -->

    <section class="dashboard-section">

        <div class="dashboard-section-header">
            <div>
                <p class="section-label">Accès rapide</p>
                <h2>Gestion</h2>
            </div>
        </div>


        <div class="quick-actions">

            <a href="/bibliotheque/livres/"
               class="quick-card">

                <span>01</span>

                <h3>Livres</h3>

                <p>
                    Consulter, ajouter et modifier les ouvrages.
                </p>

                <strong>Gérer les livres →</strong>

            </a>


            <a href="/bibliotheque/adherents/"
               class="quick-card">

                <span>02</span>

                <h3>Adhérents</h3>

                <p>
                    Gérer les personnes inscrites à la bibliothèque.
                </p>

                <strong>Gérer les adhérents →</strong>

            </a>


            <a href="/bibliotheque/emprunts/"
               class="quick-card">

                <span>03</span>

                <h3>Emprunts</h3>

                <p>
                    Suivre les emprunts, les retours et les retards.
                </p>

                <strong>Gérer les emprunts →</strong>

            </a>

        </div>

    </section>


    <!-- =========================
         DERNIERS EMPRUNTS
    ========================== -->

    <section class="dashboard-section">

        <div class="dashboard-section-header">

            <div>
                <p class="section-label">Activité récente</p>
                <h2>Derniers emprunts</h2>
            </div>

            <a href="/bibliotheque/emprunts/"
               class="text-link">

                Voir tous les emprunts →

            </a>

        </div>


        <div class="table-container">

            <table>

                <thead>
                    <tr>
                        <th>Livre</th>
                        <th>Adhérent</th>
                        <th>Date d'emprunt</th>
                        <th>Retour prévu</th>
                        <th>Statut</th>
                    </tr>
                </thead>


                <tbody>

                <?php if (empty($derniersEmprunts)): ?>

                    <tr>
                        <td colspan="5" class="empty-table">
                            Aucun emprunt enregistré.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($derniersEmprunts as $emprunt): ?>

                        <?php

                        if ($emprunt['date_retour_reelle'] !== null) {

                            $statut = 'Retourné';
                            $classeStatut = 'status-returned';

                        } elseif (
                            $emprunt['date_retour_prevue'] < date('Y-m-d')
                        ) {

                            $statut = 'En retard';
                            $classeStatut = 'status-late';

                        } else {

                            $statut = 'En cours';
                            $classeStatut = 'status-active';

                        }

                        ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars(
                                    $emprunt['titre']
                                ) ?>
                            </td>

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

                                <span class="status <?= $classeStatut ?>">

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
require_once __DIR__ . '/includes/footer.php';
?>