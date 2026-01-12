<?php
require_once 'config.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: index.php');
    exit;
}

// Démarrer un nouveau test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['niveau_id'])) {
    $niveau_id = (int)$_POST['niveau_id'];
    
    // Récupérer les informations du niveau
    $stmt = $pdo->prepare("SELECT * FROM niveaux WHERE id = ?");
    $stmt->execute([$niveau_id]);
    $niveau = $stmt->fetch();
    
    if (!$niveau) {
        header('Location: selection_niveau.php');
        exit;
    }
    
    // Récupérer 20 questions aléatoires pour ce niveau
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE niveau_id = ? ORDER BY RAND() LIMIT ?");
    $stmt->execute([$niveau_id, QUESTIONS_PAR_TEST]);
    $questions = $stmt->fetchAll();
    
    if (count($questions) < QUESTIONS_PAR_TEST) {
        die("Erreur : Pas assez de questions disponibles pour ce niveau.");
    }
    
    // Sauvegarder les données du test dans la session
    $_SESSION['test_actif'] = true;
    $_SESSION['niveau_id'] = $niveau_id;
    $_SESSION['niveau_nom'] = $niveau['nom'];
    $_SESSION['questions'] = $questions;
    $_SESSION['reponses'] = [];
    $_SESSION['temps_debut'] = time();
}

// Vérifier qu'un test est en cours
if (!isset($_SESSION['test_actif']) || !$_SESSION['test_actif']) {
    header('Location: selection_niveau.php');
    exit;
}

$questions = $_SESSION['questions'];
$reponses = $_SESSION['reponses'];
$question_actuelle = count($reponses);

// Si toutes les questions sont répondues, rediriger vers les résultats
if ($question_actuelle >= count($questions)) {
    header('Location: resultat.php');
    exit;
}

$question = $questions[$question_actuelle];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test en cours - QCM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header-test {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .niveau-info {
            color: #667eea;
            font-weight: 600;
            font-size: 1.2em;
        }
        
        .progression {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .progression-text {
            font-weight: 600;
            color: #333;
        }
        
        .progress-bar {
            width: 200px;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s;
        }
        
        .question-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .question-numero {
            color: #667eea;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .question-texte {
            font-size: 1.4em;
            color: #333;
            line-height: 1.6;
            margin-bottom: 30px;
            font-weight: 500;
        }
        
        .options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .option {
            display: flex;
            align-items: center;
            padding: 18px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .option:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateX(5px);
        }
        
        .option input[type="radio"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .option-text {
            font-size: 1.1em;
            color: #333;
            flex: 1;
        }
        
        .btn-suivant {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-suivant:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-suivant:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .timer {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .timer-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .timer-value {
            color: #667eea;
            font-size: 1.5em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-test">
            <div class="niveau-info">
                📚 <?= htmlspecialchars($_SESSION['niveau_nom'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="progression">
                <span class="progression-text">
                    Question <?= $question_actuelle + 1 ?> / <?= count($questions) ?>
                </span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= (($question_actuelle + 1) / count($questions)) * 100 ?>%"></div>
                </div>
            </div>
        </div>
        
        <div class="timer">
            <div class="timer-label">⏱️ Temps écoulé</div>
            <div class="timer-value" id="timer">00:00</div>
        </div>
        
        <form method="POST" action="traiter_reponse.php" id="questionForm">
            <div class="question-card">
                <div class="question-numero">Question <?= $question_actuelle + 1 ?></div>
                <div class="question-texte">
                    <?= htmlspecialchars($question['question_texte'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                
                <div class="options">
                    <label class="option">
                        <input type="radio" name="reponse" value="A" required>
                        <span class="option-text">A. <?= htmlspecialchars($question['option_a'], ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                    
                    <label class="option">
                        <input type="radio" name="reponse" value="B" required>
                        <span class="option-text">B. <?= htmlspecialchars($question['option_b'], ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                    
                    <label class="option">
                        <input type="radio" name="reponse" value="C" required>
                        <span class="option-text">C. <?= htmlspecialchars($question['option_c'], ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                    
                    <label class="option">
                        <input type="radio" name="reponse" value="D" required>
                        <span class="option-text">D. <?= htmlspecialchars($question['option_d'], ENT_QUOTES, 'UTF-8') ?></span>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn-suivant">
                <?= $question_actuelle < count($questions) - 1 ? 'Question Suivante →' : 'Terminer le Test ✓' ?>
            </button>
        </form>
    </div>
    
    <script>
        // Timer
        const tempsDebut = <?= $_SESSION['temps_debut'] ?>;
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            const maintenant = Math.floor(Date.now() / 1000);
            const elapsed = maintenant - tempsDebut;
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            timerElement.textContent = 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0');
        }
        
        setInterval(updateTimer, 1000);
        updateTimer();
    </script>
</body>
</html>