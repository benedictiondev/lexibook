<?php

require_once __DIR__ . '/../includes/auth.php';

require_once __DIR__ . '/../config/database.php';


/* =========================================================
   RECHERCHE
========================================================= */

$recherche = trim($_GET['q'] ?? '');


$sql = "
    SELECT
        l.*,

        (
            l.nombre_exemplaires
            -
            (
                SELECT COUNT(*)
                FROM emprunts e
                WHERE e.id_livre = l.id_livre
                AND e.date_retour_reelle IS NULL
            )
        ) AS exemplaires_disponibles

    FROM livres l
";


$params = [];


if ($recherche !== '') {

    $sql .= "
        WHERE
            l.titre LIKE :recherche
            OR l.auteur LIKE :recherche
            OR l.isbn LIKE :recherche
            OR l.categorie LIKE :recherche
    ";

    $params['recherche'] = '%' . $recherche . '%';
}


$sql .= " ORDER BY l.titre ASC";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$livres = $stmt->fetchAll();


require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

?>


<main class="main-content">
<?php if (($_GET['ajout'] ?? '') === 'success'): ?>

    <div class="alert alert-success">
        Le livre a été ajouté avec succès.
    </div>

<?php endif; ?>
<?php if (($_GET['modification'] ?? '') === 'success'): ?>

    <div class="alert alert-success">
        Le livre a été modifié avec succès.
    </div>

<?php endif; ?>
<?php if (($_GET['suppression'] ?? '') === 'success'): ?>

    <div class="alert alert-success">
        Le livre a été supprimé avec succès.
    </div>

<?php endif; ?>


<?php if (($_GET['suppression'] ?? '') === 'historique'): ?>

    <div class="alert alert-error">
        Ce livre ne peut pas être supprimé car il possède
        un historique d'emprunts.
    </div>

<?php endif; ?>


<?php if (($_GET['suppression'] ?? '') === 'introuvable'): ?>

    <div class="alert alert-error">
        Le livre demandé est introuvable.
    </div>

<?php endif; ?>


<?php if (($_GET['suppression'] ?? '') === 'erreur'): ?>

    <div class="alert alert-error">
        La suppression n'a pas pu être effectuée.
    </div>

<?php endif; ?>

    <!-- =========================
         TITRE
    ========================== -->

    <div class="page-header">

        <div>

            <p class="page-label">
                Gestion
            </p>

            <h1>Livres</h1>

            <p class="page-description">
                Consultez et gérez les ouvrages disponibles
                dans la bibliothèque.
            </p>

        </div>


        <a
            href="/bibliotheque/livres/ajouter.php"
            class="btn btn-primary"
        >
            + Ajouter un livre
        </a>

    </div>


    <!-- =========================
         RECHERCHE
    ========================== -->

    <div class="search-panel">

        <form method="GET" class="search-form">

            <input
                type="search"
                name="q"
                placeholder="Rechercher par titre, auteur, ISBN ou catégorie..."
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
                    href="/bibliotheque/livres/"
                    class="btn btn-secondary"
                >
                    Réinitialiser
                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- =========================
         NOMBRE DE RÉSULTATS
    ========================== -->

    <div class="list-header">

        <p>
            <strong>
                <?= count($livres) ?>
            </strong>

            livre<?= count($livres) > 1 ? 's' : '' ?>
            trouvé<?= count($livres) > 1 ? 's' : '' ?>
        </p>

    </div>


    <!-- =========================
         TABLEAU DES LIVRES
    ========================== -->

    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Année</th>
                    <th>ISBN</th>
                    <th>Exemplaires</th>
                    <th>Disponibles</th>
                    <th>Actions</th>
                </tr>

            </thead>


            <tbody>

                <?php if (empty($livres)): ?>

                    <tr>

                        <td
                            colspan="8"
                            class="empty-table"
                        >
                            Aucun livre trouvé.
                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach ($livres as $livre): ?>

                        <?php

                        $disponible =
                            (int) $livre['exemplaires_disponibles'];

                        ?>


                        <tr>

                            <!-- TITRE -->

                            <td>

                                <strong>
                                    <?= htmlspecialchars(
                                        $livre['titre']
                                    ) ?>
                                </strong>

                            </td>


                            <!-- AUTEUR -->

                            <td>
                                <?= htmlspecialchars(
                                    $livre['auteur']
                                ) ?>
                            </td>


                            <!-- CATEGORIE -->

                            <td>

                                <?php if (!empty($livre['categorie'])): ?>

                                    <?= htmlspecialchars(
                                        $livre['categorie']
                                    ) ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ANNEE -->

                            <td>

                                <?= $livre['annee_publication']
                                    ? htmlspecialchars(
                                        $livre['annee_publication']
                                    )
                                    : '—'
                                ?>

                            </td>


                            <!-- ISBN -->

                            <td>

                                <?= $livre['isbn']
                                    ? htmlspecialchars(
                                        $livre['isbn']
                                    )
                                    : '—'
                                ?>

                            </td>


                            <!-- TOTAL -->

                            <td>
                                <?= (int) $livre['nombre_exemplaires'] ?>
                            </td>


                            <!-- DISPONIBILITE -->

                            <td>

                                <?php if ($disponible > 0): ?>

                                    <span class="availability available">

                                        <?= $disponible ?>
                                        disponible<?= $disponible > 1 ? 's' : '' ?>

                                    </span>

                                <?php else: ?>

                                    <span class="availability unavailable">

                                        Indisponible

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div class="table-actions">

                                    <a
                                        href="/bibliotheque/livres/details.php?id=<?= $livre['id_livre'] ?>"
                                        class="action-link"
                                    >
                                        Voir
                                    </a>


                                    <a
                                        href="/bibliotheque/livres/modifier.php?id=<?= $livre['id_livre'] ?>"
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
                                            action="/bibliotheque/livres/supprimer.php"
                                            class="delete-form"
                                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce livre ?');"
                                        >

                                           

                                            <input
                                                type="hidden"
                                                name="id_livre"
                                                value="<?= (int) $livre['id_livre'] ?>"
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