<header class="app-header">

    <nav class="navbar">

        <a href="/bibliotheque/dashboard.php" class="navbar-brand">
    <img
        src="/bibliotheque/images/logolexibook.png"
        alt="LexiBook"
        class="navbar-logo"
    >
</a>


        <div class="nav-links">

            <a href="/bibliotheque/dashboard.php">
                Tableau de bord
            </a>

            <a href="/bibliotheque/livres/">
                Livres
            </a>

            <a href="/bibliotheque/adherents/">
                Adhérents
            </a>

            <a href="/bibliotheque/emprunts/">
                Emprunts
            </a>

        </div>
        <?php

require_once __DIR__ . '/demo.php';

?>

<?php if (est_mode_demo()): ?>

    <div class="demo-banner">
        Mode démonstration — Consultation uniquement
    </div>

<?php endif; ?>
<?php if (
    isset($_SESSION['utilisateur']['role'])
    &&
    $_SESSION['utilisateur']['role'] === 'admin'
): ?>

    <a href="/bibliotheque/utilisateurs/">
        Utilisateurs
    </a>

<?php endif; ?>

        <div class="user-menu">

            <div class="user-info">

                <span class="user-name">
                    <?= htmlspecialchars(
                        $_SESSION['utilisateur']['nom']
                    ) ?>
                </span>

                <span class="user-role">
                    <?= htmlspecialchars(
                        ucfirst(
                            $_SESSION['utilisateur']['role']
                        )
                    ) ?>
                </span>

            </div>


            <a
                href="/bibliotheque/deconnexion.php"
                class="logout-link"
            >
                Déconnexion
            </a>

        </div>

    </nav>

</header>