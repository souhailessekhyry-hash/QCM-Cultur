<?php
require_once 'config.php';

if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['test_actif']) || !$_SESSION['test_actif']) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
    $reponse = $_POST['reponse'];
    $question_actuelle = count($_SESSION['reponses']);
    $question = $_SESSION['questions'][$question_actuelle];
    
    // Enregistrer la réponse
    $_SESSION['reponses'][] = [
        'question_id' => $question['id'],
        'reponse_donnee' => $reponse,
        'reponse_correcte' => $question['reponse_correcte'],
        'est_correcte' => ($reponse === $question['reponse_correcte'])
    ];
    
    // Rediriger vers la prochaine question ou les résultats
    header('Location: test.php');
    exit;
}

header('Location: test.php');
exit;
?>