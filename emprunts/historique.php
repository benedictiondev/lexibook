<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/* =========================================================
   RECHERCHE
========================================================= */

$recherche = trim($_GET['q'] ?? '');


$sql = "
    SELECT
        e.id_emprunt,
        e.date_emprunt,
        e.date_retour_prevue,
        e.date_retour_reelle,

        l.id_livre,
        l.titre,
        l.auteur,

        a.id_adherent,
        a.nom,
        a.prenom

    FROM emprunts e

    INNER JOIN livres l
        ON e.id_livre = l.id_livre

    INNER JOIN adherents a
        ON e.id_adherent = a.id_adherent

    WHERE e.date_retour_reelle IS NOT NULL
";


$params = [];


if ($recherche !== '') {

    $sql .= "
        AND (
            l.titre LIKE :recherche
            OR l.auteur LIKE :recherche
            OR a.nom LIKE :recherche
            OR a.prenom LIKE :recherche
        )
    ";

    $params['recherche'] =
        '%' . $recherche . '%';
}


$sql .= "
    ORDER BY
        e.date_retour_reelle DESC,
        e.id_emprunt DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$historique = $stmt->fetchAll();


require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

?>


<main class="main-content">

    <!-- =========================
         EN-TÊTE
    ========================== -->

    <div class="page-header">

        <div>

            <p class="page-label">
                Historique
            </p>

            <h1>Historique des emprunts</h1>

            <p class="page-description">
                Consultez l'ensemble des emprunts terminés
                de la bibliothèque.
            </p>

        </div>


        <a
            href="/bibliotheque/emprunts/"
            class="btn btn-secondary"
        >
            ← Retour aux emprunts
        </a>

    </div>


    <!-- =========================
         RECHERCHE
    ========================== -->

    <div class="search-panel">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="search"
                name="q"
                placeholder="Rechercher un livre ou un adhérent..."
                value="<?= htmlspecialchars($recherche) ?>"
            >


            <button
                type="submit"
                class="btn btn-primary"
            >
                Rechercher
            </button>


            <?php if ($recherche !== ''): ?>

                <a
                    href="/bibliotheque/emprunts/historique.php"
                    class="btn btn-secondary"
                >
                    Réinitialiser
                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- =========================
         COMPTEUR
    ========================== -->

    <div class="list-header">

        <p>

            <strong>
                <?= count($historique) ?>
            </strong>

            emprunt<?= count($historique) > 1 ? 's' : '' ?>

            terminé<?= count($historique) > 1 ? 's' : '' ?>

        </p>

    </div>


    <!-- =========================
         TABLEAU
    ========================== -->

    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>Livre</th>
                    <th>Adhérent</th>
                    <th>Date d'emprunt</th>
                    <th>Retour prévu</th>
                    <th>Retour réel</th>
                    <th>Retard</th>
                    <th>Statut</th>
                </tr>

            </thead>


            <tbody>

            <?php if (empty($historique)): ?>

                <tr>

                    <td
                        colspan="7"
                        class="empty-table"
                    >
                        Aucun emprunt terminé.
                    </td>

                </tr>

            <?php else: ?>


                <?php foreach ($historique as $emprunt): ?>

                    <?php

                    $datePrevue = new DateTime(
                        $emprunt['date_retour_prevue']
                    );

                    $dateReelle = new DateTime(
                        $emprunt['date_retour_reelle']
                    );


                    $joursRetard = 0;


                    if ($dateReelle > $datePrevue) {

                        $joursRetard =
                            $datePrevue
                                ->diff($dateReelle)
                                ->days;
                    }

                    ?>


                    <tr>

                        <!-- LIVRE -->

                        <td>

                            <a
                                href="/bibliotheque/livres/details.php?id=<?= $emprunt['id_livre'] ?>"
                                class="action-link"
                            >
                                <?= htmlspecialchars(
                                    $emprunt['titre']
                                ) ?>
                            </a>


                            <div class="table-subtext">

                                <?= htmlspecialchars(
                                    $emprunt['auteur']
                                ) ?>

                            </div>

                        </td>


                        <!-- ADHERENT -->

                        <td>

                            <a
                                href="/bibliotheque/adherents/details.php?id=<?= $emprunt['id_adherent'] ?>"
                                class="action-link"
                            >

                                <?= htmlspecialchars(
                                    $emprunt['prenom']
                                    . ' '
                                    . $emprunt['nom']
                                ) ?>

                            </a>

                        </td>


                        <!-- DATE EMPRUNT -->

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $emprunt['date_emprunt']
                                )
                            ) ?>

                        </td>


                        <!-- RETOUR PREVU -->

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $emprunt['date_retour_prevue']
                                )
                            ) ?>

                        </td>


                        <!-- RETOUR REEL -->

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $emprunt['date_retour_reelle']
                                )
                            ) ?>

                        </td>


                        <!-- RETARD -->

                        <td>

                            <?php if ($joursRetard > 0): ?>

                                <span class="late-days">

                                    <?= $joursRetard ?>

                                    jour<?= $joursRetard > 1 ? 's' : '' ?>

                                </span>

                            <?php else: ?>

                                <span class="on-time">
                                    À temps
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- STATUT -->

                        <td>

                            <span class="status status-returned">
                                Retourné
                            </span>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>