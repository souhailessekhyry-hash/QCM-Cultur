<?php
require_once 'config.php';
require_once 'email_config.php';

$message = '';
$message_type = '';
$etape = 'inscription'; // inscription ou verification

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $message = "Tous les champs sont obligatoires";
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas";
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide";
        $message_type = 'error';
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id, password FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user_existe = $stmt->fetch();
            
            if ($user_existe && !empty($user_existe['password'])) {
                $message = "Cet email est déjà utilisé. Veuillez vous connecter.";
                $message_type = 'error';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                if ($user_existe) {
                    // Mettre à jour l'utilisateur existant (ancien système)
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ?, nom = ?, prenom = ? WHERE email = ?");
                    $stmt->execute([$password_hash, $nom, $prenom, $email]);
                    $user_id = $user_existe['id'];
                } else {
                    // Créer nouveau compte
                    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nom, $prenom, $email, $password_hash]);
                    $user_id = $pdo->lastInsertId();
                }
                
                // Générer et envoyer le code
                $code = genererCode();
                enregistrerCode($pdo, $user_id, $code, $email);
                
                // Envoyer l'email
                $nom_complet = htmlspecialchars("$prenom $nom");
                if (envoyerCodeVerification($email, $code, $nom_complet)) {
                    $_SESSION['email_inscription'] = $email;
                    $_SESSION['nom_inscription'] = $nom_complet;
                    $message = "Compte créé ! Un code de vérification a été envoyé à votre email.";
                    $message_type = 'success';
                    $etape = 'verification';
                } else {
                    $message = "Erreur lors de l'envoi de l'email. Code: $code (utilisez ce code pour tester)";
                    $message_type = 'error';
                }
            }
        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - QCM</title>
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
        
        .password-hint {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Inscription</h1>
        <p class="subtitle">Créez votre compte QCM</p>
        
        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        
        <?php if ($etape === 'inscription'): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" required placeholder="Votre nom">
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" required placeholder="Votre prénom">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required placeholder="votre.email@exemple.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" required placeholder="••••••">
                    <div class="password-hint">Minimum 6 caractères</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••">
                </div>
                
                <button type="submit" name="inscription" class="btn">
                    S'inscrire
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 20px;">
                <p style="font-size: 1.2em; margin-bottom: 20px;">
                    ✅ Inscription réussie !
                </p>
                <p style="color: #666; margin-bottom: 20px;">
                    Un code de vérification a été envoyé à votre email.
                </p>
                <a href="connexion.php" class="btn" style="display: inline-block; text-decoration: none;">
                    Continuer vers la connexion
                </a>
            </div>
        <?php endif; ?>
        
        <div class="links">
            <p>Vous avez déjà un compte ? <a href="connexion.php">Se connecter</a></p>
            <p><a href="index.php">← Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>