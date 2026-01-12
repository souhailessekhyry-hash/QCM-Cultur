<?php
session_start();
require_once 'config.php';
require_once 'email_config.php';

$erreur = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code  = trim($_POST['code'] ?? '');

    if (!empty($email) && !empty($code)) {
        $result = verifierCode($pdo, $email, $code);
        
        if ($result) {
            // Get user information to set proper session variables
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Code correct - set session variables to match the rest of the application
                $_SESSION['utilisateur_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $email;

                // Mark code as used
                marquerCodeUtilise($pdo, $result['id']);

                // Redirect to level selection
                header('Location: selection_niveau.php');
                exit;
            } else {
                $erreur = "❌ Utilisateur non trouvé";
            }
        } else {
            $erreur = "❌ Code incorrect ou expiré";
        }
    } else {
        $erreur = "❌ Veuillez remplir tous les champs";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du Code - QCM</title>
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
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: left;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔢 Vérification</h1>
        <p class="subtitle">Entrez le code de vérification reçu par email</p>
        
        <?php if ($erreur): ?>
            <div class="message error">
                <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <p><strong>ℹ️ Information :</strong></p>
            <p>Cette page est pour la vérification manuelle d'un code.</p>
            <p>Pour une expérience complète, utilisez plutôt : <a href="connexion.php" style="color: #667eea; font-weight: bold;">connexion.php</a></p>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="email">📧 Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="votre.email@exemple.com"
                       required
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            
            <div class="form-group">
                <label for="code">🔢 Code de vérification</label>
                <input type="text" 
                       id="code" 
                       name="code" 
                       placeholder="Entrez le code reçu"
                       required
                       maxlength="6"
                       pattern="[0-9]{6}">
            </div>
            
            <button type="submit" class="btn">
                Vérifier et se connecter
            </button>
        </form>
        
        <div class="links">
            <p><a href="connexion.php">← Retour à la connexion</a></p>
            <p><a href="index.php">🏠 Accueil</a></p>
        </div>
    </div>
</body>
</html>
</body>
</html>
