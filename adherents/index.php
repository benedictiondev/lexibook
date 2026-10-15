<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/* =========================================================
   RECHERCHE
========================================================= */

$recherche = trim($_GET['q'] ?? '');


$sql = "
    SELECT
        a.*,

        (
            SELECT COUNT(*)
            FROM emprunts e
            WHERE e.id_adherent = a.id_adherent
            AND e.date_retour_reelle IS NULL
        ) AS emprunts_en_cours

    FROM adherents a
";


$params = [];


if ($recherche !== '') {

    $sql .= "
        WHERE
            a.nom LIKE :recherche
            OR a.prenom LIKE :recherche
            OR a.email LIKE :recherche
            OR a.telephone LIKE :recherche
    ";

    $params['recherche'] = '%' . $recherche . '%';
}


$sql .= "
    ORDER BY a.nom ASC, a.prenom ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$adherents = $stmt->fetchAll();


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

            <h1>Adhérents</h1>

            <p class="page-description">
                Consultez et gérez les adhérents de la bibliothèque.
            </p>

        </div>


        <a
            href="/bibliotheque/adherents/ajouter.php"
            class="btn btn-primary"
        >
            + Ajouter un adhérent
        </a>

    </div>


    <!-- =========================
         MESSAGES
    ========================== -->

    <?php if (($_GET['ajout'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            L'adhérent a été ajouté avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['modification'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            L'adhérent a été modifié avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['suppression'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            L'adhérent a été supprimé avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['suppression'] ?? '') === 'historique'): ?>

        <div class="alert alert-error">
            Cet adhérent ne peut pas être supprimé car il possède
            un historique d'emprunts.
        </div>

    <?php endif; ?>


    <?php if (($_GET['suppression'] ?? '') === 'introuvable'): ?>

        <div class="alert alert-error">
            L'adhérent demandé est introuvable.
        </div>

    <?php endif; ?>


    <?php if (($_GET['suppression'] ?? '') === 'erreur'): ?>

        <div class="alert alert-error">
            La suppression n'a pas pu être effectuée.
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
                placeholder="Rechercher par nom, prénom, email ou téléphone..."
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
                    href="/bibliotheque/adherents/"
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
                <?= count($adherents) ?>
            </strong>

            adhérent<?= count($adherents) > 1 ? 's' : '' ?>

            trouvé<?= count($adherents) > 1 ? 's' : '' ?>

        </p>

    </div>


    <!-- =========================
         TABLEAU
    ========================== -->

    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Inscription</th>
                    <th>Emprunts en cours</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>

            </thead>


            <tbody>

            <?php if (empty($adherents)): ?>

                <tr>

                    <td
                        colspan="7"
                        class="empty-table"
                    >
                        Aucun adhérent trouvé.
                    </td>

                </tr>

            <?php else: ?>


                <?php foreach ($adherents as $adherent): ?>

                    <tr>

                        <!-- NOM -->

                        <td>

                            <strong>
                                <?= htmlspecialchars(
                                    $adherent['prenom']
                                    . ' '
                                    . $adherent['nom']
                                ) ?>
                            </strong>

                        </td>


                        <!-- EMAIL -->

                        <td>

                            <?= htmlspecialchars(
                                $adherent['email']
                            ) ?>

                        </td>


                        <!-- TELEPHONE -->

                        <td>

                            <?= !empty($adherent['telephone'])
                                ? htmlspecialchars(
                                    $adherent['telephone']
                                )
                                : '—'
                            ?>

                        </td>


                        <!-- INSCRIPTION -->

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $adherent['date_inscription']
                                )
                            ) ?>

                        </td>


                        <!-- EMPRUNTS EN COURS -->

                        <td>

                            <?= (int) $adherent['emprunts_en_cours'] ?>

                        </td>


                        <!-- STATUT -->

                        <td>

                            <?php if ((int) $adherent['actif'] === 1): ?>

                                <span class="status status-returned">
                                    Actif
                                </span>

                            <?php else: ?>

                                <span class="status status-late">
                                    Inactif
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- ACTIONS -->

                        <td>

                            <div class="table-actions">

                                <a
                                    href="/bibliotheque/adherents/details.php?id=<?= (int) $adherent['id_adherent'] ?>"
                                    class="action-link"
                                >
                                    Voir
                                </a>


                                <a
                                    href="/bibliotheque/adherents/modifier.php?id=<?= (int) $adherent['id_adherent'] ?>"
                                    class="action-link"
                                >
                                    Modifier
                                </a>


                                <?php if (
                                    isset($_SESSION['utilisateur']['role'])
                                    &&
                                    $_SESSION['utilisateur']['role'] === 'admin'
                                ): ?>

                                    <form
                                        method="POST"
                                        action="/bibliotheque/adherents/supprimer.php"
                                        class="delete-form"
                                        onsubmit="return confirm('Voulez-vous vraiment supprimer cet adhérent ?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_adherent"
                                            value="<?= (int) $adherent['id_adherent'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="action-link action-delete"
                                        >
                                            Supprimer
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

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