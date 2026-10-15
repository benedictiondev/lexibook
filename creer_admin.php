<?php
/** Créer un administrateur en local : php creer_admin.php */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script doit être exécuté uniquement depuis le terminal.');
}
require_once __DIR__ . '/config/database.php';

function demander(string $question): string {
    echo $question;
    return trim((string) fgets(STDIN));
}

$nom = demander('Nom de l’administrateur : ');
$email = demander('Email de l’administrateur : ');
$motDePasse = demander('Mot de passe (12 caractères minimum ; affiché à la saisie) : ');
if ($nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($motDePasse) < 12) {
    fwrite(STDERR, "Nom, email ou mot de passe invalide.\n");
    exit(1);
}
$stmt = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = ?');
$stmt->execute([$email]);
if ((int) $stmt->fetchColumn() !== 0) {
    fwrite(STDERR, "Un compte utilise déjà cette adresse.\n");
    exit(1);
}
$stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'admin')");
$stmt->execute([$nom, $email, password_hash($motDePasse, PASSWORD_DEFAULT)]);
echo "Compte administrateur créé. Ne partagez pas son mot de passe.\n";
