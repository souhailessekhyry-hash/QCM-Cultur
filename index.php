<?php
require_once 'config.php';

// Si utilisateur déjà connecté, rediriger
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: selection_niveau.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCM Culture Générale</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
            text-align: left;
        }
        
        .info-box p {
            color: #0c5460;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        
        .info-box strong {
            color: #004085;
        }
        
        .buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .btn {
            padding: 18px;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #4CAF50;
            color: white;
        }
        
        .features {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }
        
        .features h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .feature-list {
            text-align: left;
            margin-top: 15px;
        }
        
        .feature-item {
            padding: 10px 0;
            color: #555;
        }
        
        .feature-item::before {
            content: "✓ ";
            color: #4CAF50;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .admin-link {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .admin-link a {
            color: #666;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .admin-link a:hover {
            color: #667eea;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 QCM</h1>
        <p class="subtitle">Test de Culture Générale en Français</p>
        
        <div class="info-box">
            <p><strong>Bienvenue sur notre plateforme QCM !</strong></p>
            <p>📚 10 niveaux de difficulté</p>
            <p>📝 20 questions par test</p>
            <p>🏆 Suivez votre progression</p>
            <p>🔐 Connexion sécurisée par email</p>
        </div>
        
        <div class="buttons">
            <a href="inscription.php" class="btn btn-secondary">
                📝 Créer un compte
            </a>
            <a href="connexion.php" class="btn btn-primary">
                🔐 Se connecter
            </a>
        </div>
        
        <div class="features">
            <h3>✨ Fonctionnalités</h3>
            <div class="feature-list">
                <div class="feature-item">Inscription rapide et facile</div>
                <div class="feature-item">Code de vérification par email</div>
                <div class="feature-item">10 niveaux progressifs</div>
                <div class="feature-item">Résultats détaillés après chaque test</div>
                <div class="feature-item">Historique de vos scores</div>
                <div class="feature-item">Possibilité de refaire les tests</div>
            </div>
        </div>
        
        <div class="admin-link">
            <a href="login_admin.php">👨‍💼 Espace Administrateur</a>
        </div>
    </div>
</body>
</html>