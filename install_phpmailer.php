<?php
/**
 * SCRIPT INSTALLATION AUTOMATIQUE PHPMAILER
 * ==========================================
 * This file will automatically download and install PHPMailer
 */

$phpmailer_dir = __DIR__ . '/PHPMailer';
$zip_url = 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip';
$zip_file = __DIR__ . '/phpmailer.zip';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation PHPMailer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 500;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        
        .step h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #d63384;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .file-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .file-list ul {
            list-style: none;
            padding-left: 0;
        }
        
        .file-list li {
            padding: 5px 0;
            color: #495057;
        }
        
        .file-list li:before {
            content: "📄 ";
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Installation PHPMailer</h1>
        
        <?php
        // Vérification de l'installation
        $phpmailer_installed = false;
        $files_ok = false;
        
        if (is_dir($phpmailer_dir)) {
            echo '<div class="info">📁 Dossier PHPMailer existe: ' . $phpmailer_dir . '</div>';
            
            $required_files = [
                'Exception.php',
                'PHPMailer.php',
                'SMTP.php'
            ];
            
            $missing_files = [];
            foreach ($required_files as $file) {
                if (!file_exists($phpmailer_dir . '/' . $file)) {
                    $missing_files[] = $file;
                }
            }
            
            if (empty($missing_files)) {
                echo '<div class="success">✅ Tous les fichiers PHPMailer sont présents!</div>';
                $phpmailer_installed = true;
                $files_ok = true;
                
                // Afficher les fichiers
                echo '<div class="file-list">';
                echo '<strong>Fichiers trouvés:</strong>';
                echo '<ul>';
                $files = scandir($phpmailer_dir);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        echo '<li>' . htmlspecialchars($file) . '</li>';
                    }
                }
                echo '</ul>';
                echo '</div>';
                
            } else {
                echo '<div class="error">❌ Fichiers manquants: ' . implode(', ', $missing_files) . '</div>';
            }
        } else {
            echo '<div class="warning">⚠️ Dossier PHPMailer n\'existe pas encore</div>';
        }
        
        // Si pas installé, montrer les instructions
        if (!$files_ok) {
        ?>
        
        <div class="step">
            <h3>🎯 Installation Manuelle (Méthode Recommandée)</h3>
            
            <p><strong>Étape 1:</strong> Télécharger PHPMailer</p>
            <p>Cliquez sur ce lien: <a href="https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip" target="_blank" style="color: #667eea; font-weight: bold;">📥 Télécharger PHPMailer</a></p>
            
            <p style="margin-top: 15px;"><strong>Étape 2:</strong> Extraire le ZIP</p>
            <p>Vous aurez un dossier <code>PHPMailer-master</code></p>
            
            <p style="margin-top: 15px;"><strong>Étape 3:</strong> Copier les fichiers</p>
            <p>Dans <code>PHPMailer-master/src/</code>, copiez TOUS les fichiers vers:</p>
            <code><?php echo str_replace('\\', '/', $phpmailer_dir); ?>/</code>
            
            <p style="margin-top: 15px;"><strong>Étape 4:</strong> Vérifier</p>
            <p>Rechargez cette page pour vérifier l'installation</p>
        </div>
        
        <div class="step">
            <h3>📋 Structure attendue:</h3>
            <div class="file-list">
                <ul>
                    <li>Exception.php (obligatoire)</li>
                    <li>PHPMailer.php (obligatoire)</li>
                    <li>SMTP.php (obligatoire)</li>
                    <li>POP3.php</li>
                    <li>OAuth.php</li>
                    <li>... (autres fichiers)</li>
                </ul>
            </div>
        </div>
        
        <div class="warning">
            <p><strong>⚠️ Important:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Copiez le CONTENU du dossier "src", pas le dossier lui-même</li>
                <li>Le chemin doit être: <code>C:\xampp\htdocs\CICD\PHPMailer\PHPMailer.php</code></li>
                <li>PAS: <code>C:\xampp\htdocs\CICD\PHPMailer\src\PHPMailer.php</code></li>
            </ul>
        </div>
        
        <?php
        } else {
            // PHPMailer installé - Tester la configuration
        ?>
        
        <div class="success">
            <h3 style="color: #155724;">✅ PHPMailer est correctement installé!</h3>
        </div>
        
        <div class="step">
            <h3>🧪 Étape suivante: Tester l'envoi d'email</h3>
            <p>Maintenant que PHPMailer est installé, testez l'envoi:</p>
            <a href="test_email.php" class="btn">📧 Tester l'envoi d'email</a>
        </div>
        
        <div class="info">
            <p><strong>Configuration actuelle:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Email: souhailessekhayry@gmail.com</li>
                <li>SMTP: smtp.gmail.com:587</li>
                <li>App Password: gqaf qnsv njwf temu</li>
            </ul>
        </div>
        
        <?php } ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn" style="background: #6c757d;">← Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>