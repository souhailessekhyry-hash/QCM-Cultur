<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: index.php');
    exit;
}

// Récupérer les niveaux disponibles
$stmt = $pdo->query("SELECT * FROM niveaux ORDER BY numero_niveau");
$niveaux = $stmt->fetchAll();

// Récupérer l'historique des tentatives de l'utilisateur
$stmt = $pdo->prepare("
    SELECT n.numero_niveau, MAX(t.pourcentage) as meilleur_score
    FROM tentatives t
    JOIN niveaux n ON t.niveau_id = n.id
    WHERE t.utilisateur_id = ?
    GROUP BY n.numero_niveau
");
$stmt->execute([$_SESSION['utilisateur_id']]);
$historique = [];
while ($row = $stmt->fetch()) {
    $historique[$row['numero_niveau']] = $row['meilleur_score'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélection du Niveau - QCM</title>
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
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .user-info {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .niveaux-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .niveau-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .niveau-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .niveau-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .niveau-numero {
            font-size: 3em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .niveau-nom {
            font-size: 1.3em;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .niveau-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .score-badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .btn-commencer {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: opacity 0.3s;
        }
        
        .btn-commencer:hover {
            opacity: 0.9;
        }
        
        .deconnexion {
            text-align: center;
            margin-top: 20px;
        }
        
        .deconnexion a {
            color: white;
            text-decoration: none;
            padding: 10px 30px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .deconnexion a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .info-panel {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .info-panel h2 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .info-panel ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-panel li {
            padding: 8px 0;
            color: #555;
        }
        
        .info-panel li::before {
            content: "✓ ";
            color: #4CAF50;
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Sélectionnez votre Niveau</h1>
            <div class="user-info">
                👤 <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
        
        <div class="info-panel">
            <h2>📋 Informations sur le Test</h2>
            <ul>
                <li>Chaque test contient 20 questions à choix multiples</li>
                <li>Temps recommandé : 30 minutes maximum</li>
                <li>Vous pouvez refaire un niveau pour améliorer votre score</li>
                <li>Les questions sont en français sur la culture générale</li>
            </ul>
        </div>
        
        <div class="niveaux-grid">
            <?php foreach ($niveaux as $niveau): ?>
                <div class="niveau-card">
                    <div class="niveau-numero">Niveau <?= $niveau['numero_niveau'] ?></div>
                    <div class="niveau-nom"><?= htmlspecialchars($niveau['nom']) ?></div>
                    <div class="niveau-description"><?= htmlspecialchars($niveau['description']) ?></div>
                    
                    <?php if (isset($historique[$niveau['numero_niveau']])): ?>
                        <div class="score-badge">
                            🏆 Meilleur score : <?= number_format($historique[$niveau['numero_niveau']], 1) ?>%
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="test.php">
                        <input type="hidden" name="niveau_id" value="<?= $niveau['id'] ?>">
                        <button type="submit" class="btn-commencer">
                            <?= isset($historique[$niveau['numero_niveau']]) ? 'Refaire le test' : 'Commencer le test' ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="deconnexion">
            <a href="deconnexion.php">🚪 Se déconnecter</a>
        </div>
    </div>
</body>
</html>