<?php
require_once 'config.php';
require_once 'email_config.php';

$message = '';
$message_type = '';
$etape = 'email'; // email, code

// Étape 1: Email + Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer_code'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND compte_actif = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
                // Générer et envoyer le code
                $code = genererCode();
                enregistrerCode($pdo, $user['id'], $code, $email);
                
                $nom_complet = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
                if (envoyerCodeVerification($email, $code, $nom_complet)) {
                    $_SESSION['temp_email'] = $email;
                    $_SESSION['temp_nom'] = $nom_complet;
                    $etape = 'code';
                    $message = "Un code de vérification a été envoyé à votre email";
                    $message_type = 'success';
                } else {
                    $message = "Erreur lors de l'envoi de l'email. Code pour test: $code";
                    $message_type = 'error';
                    $_SESSION['temp_email'] = $email;
                    $etape = 'code';
                }
            } else {
                $message = "Email ou mot de passe incorrect";
                $message_type = 'error';
            }
        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = "Veuillez remplir tous les champs";
        $message_type = 'error';
    }
}

// Étape 2: Vérification du code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verifier_code'])) {
    $email = $_SESSION['temp_email'] ?? '';
    $code = trim($_POST['code']);
    
    if (!empty($code) && !empty($email)) {
        $verification = verifierCode($pdo, $email, $code);
        
        if ($verification) {
            // Code valide
            marquerCodeUtilise($pdo, $verification['id']);
            
            // Connecter l'utilisateur
            $_SESSION['utilisateur_id'] = $verification['user_id'];
            $_SESSION['nom'] = $verification['nom'];
            $_SESSION['prenom'] = $verification['prenom'];
            $_SESSION['email'] = $email;
            
            // Nettoyer les variables temporaires
            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_nom']);
            
            header('Location: selection_niveau.php');
            exit;
        } else {
            $message = "Code invalide ou expiré";
            $message_type = 'error';
            $etape = 'code';
        }
    }
}

// Renvoyer un nouveau code
if (isset($_GET['renvoyer']) && isset($_SESSION['temp_email'])) {
    $email = $_SESSION['temp_email'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $code = genererCode();
            enregistrerCode($pdo, $user['id'], $code, $email);
            
            $nom_complet = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
            if (envoyerCodeVerification($email, $code, $nom_complet)) {
                $message = "Un nouveau code a été envoyé à votre email";
                $message_type = 'success';
            } else {
                $message = "Erreur lors de l'envoi. Code pour test: $code";
                $message_type = 'error';
            }
            $etape = 'code';
        }
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
        $message_type = 'error';
    }
}

// Si temp_email existe, aller directement à l'étape code
if (isset($_SESSION['temp_email']) && $etape === 'email') {
    $etape = 'code';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - QCM</title>
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
        
        .code-input {
            font-size: 24px;
            letter-spacing: 10px;
            text-align: center;
            font-weight: bold;
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
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box p {
            color: #0c5460;
            line-height: 1.5;
            margin-bottom: 5px;
        }
        
        .renvoyer-link {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9em;
        }
        
        .steps {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
        }
        
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Connexion</h1>
        <p class="subtitle">Connectez-vous à votre compte</p>
        
        <div class="steps">
            <div class="step <?= $etape === 'email' ? 'active' : '' ?>">1</div>
            <div class="step <?= $etape === 'code' ? 'active' : '' ?>">2</div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        
        <?php if ($etape === 'email'): ?>
            <form method="POST" action="">
                <div class="info-box">
                    <p><strong>Étape 1/2 :</strong> Entrez votre email et mot de passe</p>
                    <p>Un code de vérification sera envoyé à votre email</p>
                </div>
                
                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" required placeholder="votre.email@exemple.com">
                </div>
                
                <div class="form-group">
                    <label for="password">🔑 Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="••••••">
                </div>
                
                <button type="submit" name="envoyer_code" class="btn">
                    Envoyer le code
                </button>
            </form>
        <?php else: ?>
            <form method="POST" action="">
                <div class="info-box">
                    <p><strong>Étape 2/2 :</strong> Vérification</p>
                    <p>📧 Un code à 6 chiffres a été envoyé à : <strong><?= htmlspecialchars($_SESSION['temp_email'] ?? '') ?></strong></p>
                    <p>⏱️ Le code est valide pendant <?= CODE_VALIDITY_MINUTES ?> minutes</p>
                </div>
                
                <div class="form-group">
                    <label for="code">🔢 Code de vérification</label>
                    <input type="text" 
                           id="code" 
                           name="code" 
                           class="code-input"
                           required 
                           pattern="[0-9]{6}"
                           maxlength="6"
                           placeholder="000000"
                           autofocus>
                </div>
                
                <button type="submit" name="verifier_code" class="btn">
                    Vérifier et se connecter
                </button>
                
                <div class="renvoyer-link">
                    Vous n'avez pas reçu le code ? 
                    <a href="?renvoyer=1">Renvoyer un nouveau code</a>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="links">
            <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
            <p><a href="index.php">← Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>