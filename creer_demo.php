<?php
/** Compte d'essai facultatif. Démo désactivée par défaut en connexion.php. */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script doit être exécuté uniquement depuis le terminal.');
}
require_once __DIR__ . '/config/database.php';
$email = 'demo@lexibook.fr';
$stmt = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = ?');
$stmt->execute([$email]);
if ((int)$stmt->fetchColumn() > 0) {
    echo "Le compte démo existe déjà.\n";
    exit;
}
$motDePasse = bin2hex(random_bytes(24)); // non publié ; connexion normale non nécessaire pour le mode démo
$stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'employe')");
$stmt->execute(['Compte Démo', $email, password_hash($motDePasse, PASSWORD_DEFAULT)]);
echo "Compte démo créé. La connexion démo requiert LEXIBOOK_ENABLE_DEMO=1 sur une instance dédiée.\n";
