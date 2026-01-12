<?php
require_once 'config.php';
require_once 'email_config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $email_test = trim($_POST['email_test']);
    
    if (!empty($email_test) && filter_var($email_test, FILTER_VALIDATE_EMAIL)) {
        $code = genererCode();
        $nom = "Utilisateur Test";
        
        if (envoyerCodeVerification($email_test, $code, $nom)) {
            $message = "✅ Email envoyé avec succès à $email_test !<br>Code: <strong>$code</strong>";
            $message_type = 'success';
        } else {
            $message = "❌ Erreur lors de l'envoi de l'email.<br>Vérifiez la configuration dans email_config.php";
            $message_type = 'error';
        }
    } else {
        $message = "⚠️ Veuillez entrer un email valide";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - QCM</title>
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
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
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
        }
        
        .info-box p {
            color: #0c5460;
            line-height: 1.6;
            margin-bottom: 5px;
        }
        
        .config-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 14px;
        }
        
        .config-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .config-line {
            padding: 5px 0;
        }
        
        .config-line strong {
            color: #495057;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Test Email</h1>
        <p class="subtitle">Testez l'envoi d'email avec votre configuration</p>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <p><strong>ℹ️ Ce test va:</strong></p>
            <p>1. Générer un code aléatoire</p>
            <p>2. Envoyer un email de test à l'adresse que vous entrez</p>
            <p>3. Afficher le résultat (succès ou erreur)</p>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email_test">📧 Email de test</label>
                <input type="email" 
                       id="email_test" 
                       name="email_test" 
                       required 
                       placeholder="votre.email@gmail.com"
                       value="<?= htmlspecialchars(SMTP_USERNAME, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            
            <button type="submit" name="test_email" class="btn">
                Envoyer Email de Test
            </button>
        </form>
        
        <div class="config-box">
            <h3>⚙️ Configuration actuelle:</h3>
            <div class="config-line">
                <strong>SMTP Host:</strong> <?= htmlspecialchars(SMTP_HOST, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="config-line">
                <strong>SMTP Port:</strong> <?= htmlspecialchars(SMTP_PORT, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="config-line">
                <strong>Username:</strong> <?= htmlspecialchars(SMTP_USERNAME, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="config-line">
                <strong>From Email:</strong> <?= htmlspecialchars(SMTP_FROM_EMAIL, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="config-line">
                <strong>From Name:</strong> <?= htmlspecialchars(SMTP_FROM_NAME, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="config-line">
                <strong>Code Validity:</strong> <?= CODE_VALIDITY_MINUTES ?> minutes
            </div>
        </div>
        
        <div class="info-box" style="margin-top: 20px; background: #fff3cd; border-left-color: #ffc107;">
            <p><strong>⚠️ Si l'envoi échoue:</strong></p>
            <p>1. Vérifiez que PHPMailer est installé (visitez setup_phpmailer.php)</p>
            <p>2. Vérifiez vos credentials Gmail dans email_config.php</p>
            <p>3. Assurez-vous d'utiliser un App Password (pas le mot de passe normal)</p>
            <p>4. Vérifiez que la validation en 2 étapes est activée sur Gmail</p>
        </div>
        
        <div class="links">
            <p><a href="setup_phpmailer.php">📦 Installer PHPMailer</a></p>
            <p><a href="index.php">← Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>