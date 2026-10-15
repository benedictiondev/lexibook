<?php

require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../config/database.php';


/* =========================================================
   RECHERCHE
========================================================= */

$recherche = trim($_GET['q'] ?? '');


$sql = "
    SELECT
        id_utilisateur,
        nom,
        email,
        role,
        date_creation

    FROM utilisateurs
";


$params = [];


if ($recherche !== '') {

    $sql .= "
        WHERE
            nom LIKE :recherche
            OR email LIKE :recherche
            OR role LIKE :recherche
    ";

    $params['recherche'] =
        '%' . $recherche . '%';
}


$sql .= "
    ORDER BY nom ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$utilisateurs = $stmt->fetchAll();


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
                Administration
            </p>

            <h1>Utilisateurs</h1>

            <p class="page-description">
                Gérez les comptes ayant accès à l'application.
            </p>

        </div>


        <a
            href="/bibliotheque/utilisateurs/ajouter.php"
            class="btn btn-primary"
        >
            + Ajouter un utilisateur
        </a>

    </div>


    <!-- =========================
         MESSAGES
    ========================== -->

    <?php if (($_GET['ajout'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            L'utilisateur a été créé avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['modification'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            L'utilisateur a été modifié avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['suppression'] ?? '') === 'success'): ?>

        <div class="alert alert-success">
            L'utilisateur a été supprimé avec succès.
        </div>

    <?php endif; ?>


    <?php if (($_GET['suppression'] ?? '') === 'soi_meme'): ?>

        <div class="alert alert-error">
            Vous ne pouvez pas supprimer votre propre compte.
        </div>

    <?php endif; ?>


<?php if (($_GET['suppression'] ?? '') === 'dernier_admin'): ?>

    <div class="alert alert-error">
        Impossible de supprimer le dernier administrateur
        de l'application.
    </div>

<?php endif; ?>


<?php if (($_GET['suppression'] ?? '') === 'introuvable'): ?>

    <div class="alert alert-error">
        L'utilisateur demandé est introuvable.
    </div>

<?php endif; ?>


<?php if (($_GET['suppression'] ?? '') === 'erreur'): ?>

    <div class="alert alert-error">
        La suppression de l'utilisateur n'a pas pu être effectuée.
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
                placeholder="Rechercher par nom, email ou rôle..."
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
                    href="/bibliotheque/utilisateurs/"
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
                <?= count($utilisateurs) ?>
            </strong>

            utilisateur<?= count($utilisateurs) > 1 ? 's' : '' ?>

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
                    <th>Rôle</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>

            </thead>


            <tbody>

            <?php if (empty($utilisateurs)): ?>

                <tr>

                    <td
                        colspan="5"
                        class="empty-table"
                    >
                        Aucun utilisateur trouvé.
                    </td>

                </tr>

            <?php else: ?>


                <?php foreach ($utilisateurs as $utilisateur): ?>

                    <tr>

                        <!-- NOM -->

                        <td>

                            <strong>
                                <?= htmlspecialchars(
                                    $utilisateur['nom']
                                ) ?>
                            </strong>


                            <?php if (
                                (int) $utilisateur['id_utilisateur']
                                ===
                                (int) $_SESSION['utilisateur']['id']
                            ): ?>

                                <div class="table-subtext">
                                    Votre compte
                                </div>

                            <?php endif; ?>

                        </td>


                        <!-- EMAIL -->

                        <td>

                            <?= htmlspecialchars(
                                $utilisateur['email']
                            ) ?>

                        </td>


                        <!-- ROLE -->

                        <td>

                            <?php if (
                                $utilisateur['role'] === 'admin'
                            ): ?>

                                <span class="status status-returned">
                                    Administrateur
                                </span>

                            <?php else: ?>

                                <span class="status status-active">
                                    Employé
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- DATE -->

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime(
                                    $utilisateur['date_creation']
                                )
                            ) ?>

                        </td>


                        <!-- ACTIONS -->

                        <td>

                            <div class="table-actions">

                                <a
                                    href="/bibliotheque/utilisateurs/modifier.php?id=<?= $utilisateur['id_utilisateur'] ?>"
                                    class="action-link"
                                >
                                    Modifier
                                </a>


                                <?php if (
                                    (int) $utilisateur['id_utilisateur']
                                    !==
                                    (int) $_SESSION['utilisateur']['id']
                                ): ?>

                                    <form
                                        method="POST"
                                        action="/bibliotheque/utilisateurs/supprimer.php"
                                        class="delete-form"
                                        onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id_utilisateur"
                                            value="<?= $utilisateur['id_utilisateur'] ?>"
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