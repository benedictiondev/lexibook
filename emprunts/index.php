<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/* =========================================================
   RECHERCHE ET FILTRE
========================================================= */

$recherche = trim($_GET['q'] ?? '');
$filtre = $_GET['filtre'] ?? 'tous';


$conditions = [];
$params = [];


/* Recherche */

if ($recherche !== '') {

    $conditions[] = "
        (
            l.titre LIKE :recherche
            OR l.auteur LIKE :recherche
            OR a.nom LIKE :recherche
            OR a.prenom LIKE :recherche
        )
    ";

    $params['recherche'] = '%' . $recherche . '%';
}


/* Filtre */

switch ($filtre) {

    case 'en_cours':

        $conditions[] = "
            e.date_retour_reelle IS NULL
            AND e.date_retour_prevue >= CURDATE()
        ";

        break;


    case 'en_retard':

        $conditions[] = "
            e.date_retour_reelle IS NULL
            AND e.date_retour_prevue < CURDATE()
        ";

        break;


    case 'retourne':

        $conditions[] = "
            e.date_retour_reelle IS NOT NULL
        ";

        break;


    default:

        $filtre = 'tous';

        break;
}


/* =========================================================
   REQUETE
========================================================= */

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
";


if (!empty($conditions)) {

    $sql .= "
        WHERE " . implode(' AND ', $conditions);
}


$sql .= "
    ORDER BY e.date_emprunt DESC,
             e.id_emprunt DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$emprunts = $stmt->fetchAll();


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
                Gestion
            </p>

            <h1>Emprunts</h1>

            <p class="page-description">
                Gérez les emprunts, les retours et les retards
                de la bibliothèque.
            </p>

        </div>


        <a
            href="/bibliotheque/emprunts/ajouter.php"
            class="btn btn-primary"
        >
            + Nouvel emprunt
        </a>

    </div>
    <div class="page-actions">

    <a
        href="/bibliotheque/emprunts/historique.php"
        class="btn btn-secondary"
    >
        Historique
    </a>

    <a
        href="/bibliotheque/emprunts/ajouter.php"
        class="btn btn-primary"
    >
        + Nouvel emprunt
    </a>

</div>


    <!-- =========================
         MESSAGES
    ========================== -->

    <?php if (($_GET['ajout'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            L'emprunt a été enregistré avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['retour'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            Le retour du livre a été enregistré avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['retour'] ?? '') === 'erreur'): ?>

        <div class="alert alert-error">
            Le retour du livre n'a pas pu être enregistré.
        </div>

    <?php endif; ?>


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


            <select name="filtre">

                <option
                    value="tous"
                    <?= $filtre === 'tous' ? 'selected' : '' ?>
                >
                    Tous les emprunts
                </option>

                <option
                    value="en_cours"
                    <?= $filtre === 'en_cours' ? 'selected' : '' ?>
                >
                    En cours
                </option>

                <option
                    value="en_retard"
                    <?= $filtre === 'en_retard' ? 'selected' : '' ?>
                >
                    En retard
                </option>

                <option
                    value="retourne"
                    <?= $filtre === 'retourne' ? 'selected' : '' ?>
                >
                    Retournés
                </option>

            </select>


            <button
                type="submit"
                class="btn btn-primary"
            >
                Rechercher
            </button>


            <?php if (
                $recherche !== ''
                ||
                $filtre !== 'tous'
            ): ?>

                <a
                    href="/bibliotheque/emprunts/"
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
                <?= count($emprunts) ?>
            </strong>

            emprunt<?= count($emprunts) > 1 ? 's' : '' ?>

            trouvé<?= count($emprunts) > 1 ? 's' : '' ?>

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
                    <th>Statut</th>
                    <th>Action</th>
                </tr>

            </thead>


            <tbody>

            <?php if (empty($emprunts)): ?>

                <tr>

                    <td
                        colspan="7"
                        class="empty-table"
                    >
                        Aucun emprunt trouvé.
                    </td>

                </tr>

            <?php else: ?>


                <?php foreach ($emprunts as $emprunt): ?>

                    <?php

                    /*
                     * On calcule le statut réel à partir
                     * des dates plutôt que de dépendre
                     * uniquement de la colonne statut.
                     */

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


                        <!-- STATUT -->

                        <td>

                            <span
                                class="status <?= $classeStatut ?>"
                            >
                                <?= $statut ?>
                            </span>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <?php if (
                                $emprunt['date_retour_reelle'] === null
                            ): ?>

                                <form
    method="POST"
    action="/bibliotheque/emprunts/retour.php"
    onsubmit="return confirm('Confirmer le retour de ce livre ?');"
>



    <input
        type="hidden"
        name="id_emprunt"
        value="<?= $emprunt['id_emprunt'] ?>"
    >

    <button
        type="submit"
        class="btn-return"
    >
        Enregistrer le retour
    </button>

</form>

                            <?php else: ?>

                                <span class="text-muted">
                                    Terminé
                                </span>

                            <?php endif; ?>

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