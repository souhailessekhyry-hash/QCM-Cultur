<?php
require_once 'config.php';

if (!isset($_SESSION['utilisateur_id']) || !isset($_SESSION['test_actif']) || !$_SESSION['test_actif']) {
    header('Location: index.php');
    exit;
}

// Calculer les résultats
$reponses = $_SESSION['reponses'];
$questions = $_SESSION['questions'];
$total_questions = count($reponses);
$bonnes_reponses = 0;

foreach ($reponses as $reponse) {
    if ($reponse['est_correcte']) {
        $bonnes_reponses++;
    }
}

$pourcentage = ($bonnes_reponses / $total_questions) * 100;
$temps_ecoule = time() - $_SESSION['temps_debut'];

// Enregistrer les résultats dans la base de données
try {
    $pdo->beginTransaction();
    
    // Insérer la tentative
    $stmt = $pdo->prepare("
        INSERT INTO tentatives (utilisateur_id, niveau_id, score, total_questions, pourcentage, temps_ecoule)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['utilisateur_id'],
        $_SESSION['niveau_id'],
        $bonnes_reponses,
        $total_questions,
        $pourcentage,
        $temps_ecoule
    ]);
    
    $tentative_id = $pdo->lastInsertId();
    
    // Insérer les détails des réponses
    $stmt = $pdo->prepare("
        INSERT INTO reponses_utilisateur (tentative_id, question_id, reponse_donnee, est_correcte)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($reponses as $reponse) {
        $stmt->execute([
            $tentative_id,
            $reponse['question_id'],
            $reponse['reponse_donnee'],
            $reponse['est_correcte'] ? 1 : 0
        ]);
    }
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de l'enregistrement des résultats : " . $e->getMessage());
}

// Déterminer le message selon le score
function getMessageScore($pourcentage) {
    if ($pourcentage >= 90) return ["Excellent !", "🌟", "Vous êtes un véritable expert !"];
    if ($pourcentage >= 75) return ["Très bien !", "🎉", "Excellente performance !"];
    if ($pourcentage >= 60) return ["Bien !", "👏", "Bon travail, continuez ainsi !"];
    if ($pourcentage >= 50) return ["Passable", "📚", "Vous pouvez faire mieux !"];
    return ["À améliorer", "💪", "Continuez à apprendre !"];
}

[$message, $emoji, $description] = getMessageScore($pourcentage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - QCM</title>
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
            max-width: 900px;
            margin: 0 auto;
        }
        
        .resultat-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .emoji-grand {
            font-size: 5em;
            margin-bottom: 20px;
            animation: bounce 1s;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-30px); }
            60% { transform: translateY(-15px); }
        }
        
        .message-principal {
            font-size: 2.5em;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .description {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 30px;
        }
        
        .score-principal {
            font-size: 5em;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
        }
        
        .score-pourcentage {
            color: #667eea;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-box {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .stat-value {
            color: #667eea;
            font-size: 2em;
            font-weight: bold;
        }
        
        .detail-reponses {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .detail-titre {
            font-size: 1.5em;
            color: #667eea;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .question-detail {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .question-detail.correcte {
            border-color: #4CAF50;
            background: #f1f8f4;
        }
        
        .question-detail.incorrecte {
            border-color: #f44336;
            background: #fef5f5;
        }
        
        .question-numero {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .question-texte {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .reponse-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        
        .reponse-donnee {
            color: #f44336;
            font-weight: 600;
        }
        
        .reponse-correcte {
            color: #4CAF50;
            font-weight: 600;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="resultat-card">
            <div class="emoji-grand"><?= $emoji ?></div>
            <div class="message-principal"><?= $message ?></div>
            <div class="description"><?= $description ?></div>
            
            <div class="score-principal">
                <span class="score-pourcentage"><?= number_format($pourcentage, 1) ?>%</span>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-label">Bonnes réponses</div>
                    <div class="stat-value"><?= $bonnes_reponses ?>/<?= $total_questions ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Temps écoulé</div>
                    <div class="stat-value"><?= floor($temps_ecoule / 60) ?>:<?= str_pad($temps_ecoule % 60, 2, '0', STR_PAD_LEFT) ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Niveau</div>
                    <div class="stat-value"><?= $_SESSION['niveau_nom'] ?></div>
                </div>
            </div>
        </div>
        
        <div class="detail-reponses">
            <div class="detail-titre">📋 Détail des Réponses</div>
            
            <?php foreach ($reponses as $index => $reponse): ?>
                <?php $question = $questions[$index]; ?>
                <div class="question-detail <?= $reponse['est_correcte'] ? 'correcte' : 'incorrecte' ?>">
                    <div class="question-numero">
                        Question <?= $index + 1 ?> 
                        <?= $reponse['est_correcte'] ? '✓ Correcte' : '✗ Incorrecte' ?>
                    </div>
                    <div class="question-texte"><?= htmlspecialchars($question['question_texte'], ENT_QUOTES, 'UTF-8') ?></div>
                    
                    <?php if (!$reponse['est_correcte']): ?>
                        <div class="reponse-info">
                            <span>Votre réponse:</span>
                            <span class="reponse-donnee"><?= $reponse['reponse_donnee'] ?>. <?= htmlspecialchars($question['option_' . strtolower($reponse['reponse_donnee'])], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="reponse-info">
                        <span>Réponse correcte:</span>
                        <span class="reponse-correcte"><?= $reponse['reponse_correcte'] ?>. <?= htmlspecialchars($question['option_' . strtolower($reponse['reponse_correcte'])], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="resultat-card">
            <div class="buttons">
                <a href="selection_niveau.php" class="btn btn-primary">Choisir un autre niveau</a>
                <a href="deconnexion.php" class="btn btn-secondary">Se déconnecter</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Nettoyer la session du test
unset($_SESSION['test_actif']);
unset($_SESSION['niveau_id']);
unset($_SESSION['niveau_nom']);
unset($_SESSION['questions']);
unset($_SESSION['reponses']);
unset($_SESSION['temps_debut']);
?>