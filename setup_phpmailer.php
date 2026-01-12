<?php
/**
 * GUIDE D'INSTALLATION PHPMAILER
 * ==============================
 * 
 * PHPMailer is the best library for sending Emails from PHP
 * 
 * Two installation methods:
 */

// ══════════════════════════════════════════════════════════
// MÉTHODE 1: Installation avec Composer (Recommandé)
// ══════════════════════════════════════════════════════════

/*
1. Install Composer (if not installed):
   - Go to: https://getcomposer.org/download/
   - Download and install Composer
   
2. Open CMD/Terminal in project folder:
   cd C:\xampp\htdocs\CICD
   
3. Execute command:
   composer require phpmailer/phpmailer
   
4. ✅ Done! PHPMailer installed
*/

// ══════════════════════════════════════════════════════════
// MÉTHODE 2: Installation Manuelle (بدون Composer)
// ══════════════════════════════════════════════════════════

/*
1. Go to: https://github.com/PHPMailer/PHPMailer
2. Click on "Code" → "Download ZIP"
3. Extract files
4. Copy folder "src" to your project:
   C:\xampp\htdocs\CICD\PHPMailer\
   
5. ✅ Done!
*/

// ══════════════════════════════════════════════════════════
// TÉLÉCHARGEMENT DIRECT
// ══════════════════════════════════════════════════════════

/*
Direct link:
https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip

After download:
1. Extract files
2. Copy folder "src" to: C:\xampp\htdocs\CICD\PHPMailer\
3. Rename "src" to "PHPMailer" (optional)

Required structure:
C:\xampp\htdocs\CICD\
  ├── PHPMailer\
  │   ├── Exception.php
  │   ├── PHPMailer.php
  │   ├── SMTP.php
  │   └── ...
  ├── config.php
  ├── email_config.php
  └── ...
*/

// ══════════════════════════════════════════════════════════
// VÉRIFICATION DE L'INSTALLATION
// ══════════════════════════════════════════════════════════

echo "<h2>🔍 Vérification de PHPMailer</h2>";

// Vérifier avec Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ PHPMailer installé via Composer<br>";
    echo "📁 Chemin: vendor/phpmailer/phpmailer/<br>";
    require __DIR__ . '/vendor/autoload.php';
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✅ PHPMailer chargé avec succès!<br>";
    }
}
// Vérifier installation manuelle
elseif (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
    echo "✅ PHPMailer installé manuellement<br>";
    echo "📁 Chemin: PHPMailer/<br>";
    
    require __DIR__ . '/PHPMailer/Exception.php';
    require __DIR__ . '/PHPMailer/PHPMailer.php';
    require __DIR__ . '/PHPMailer/SMTP.php';
    
    echo "✅ PHPMailer chargé avec succès!<br>";
}
else {
    echo "❌ PHPMailer pas trouvé!<br>";
    echo "<br><strong>Instructions:</strong><br>";
    echo "1. Téléchargez: <a href='https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' target='_blank'>PHPMailer ZIP</a><br>";
    echo "2. Extrayez le dossier 'src'<br>";
    echo "3. Copiez-le vers: C:\\xampp\\htdocs\\CICD\\PHPMailer\\<br>";
    echo "4. Rechargez cette page<br>";
}

// ══════════════════════════════════════════════════════════
// EXEMPLE D'UTILISATION
// ══════════════════════════════════════════════════════════

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Installation PHPMailer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .step {
            background: #e7f3ff;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>📦 Installation PHPMailer</h1>
        <p>PHPMailer est nécessaire pour envoyer des emails avec Gmail/SMTP</p>
    </div>
    
    <div class="box">
        <h2>📋 Étapes d'installation manuelle:</h2>
        
        <div class="step">
            <strong>Étape 1:</strong> Télécharger PHPMailer<br>
            <a href="https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip" target="_blank">
                🔗 Cliquez ici pour télécharger
            </a>
        </div>
        
        <div class="step">
            <strong>Étape 2:</strong> Extraire le ZIP<br>
            - Vous aurez un dossier <code>PHPMailer-master</code>
        </div>
        
        <div class="step">
            <strong>Étape 3:</strong> Copier le dossier 'src'<br>
            - Dans <code>PHPMailer-master/src/</code><br>
            - Copiez tout le contenu vers: <code>C:\xampp\htdocs\CICD\PHPMailer\</code>
        </div>
        
        <div class="step">
            <strong>Étape 4:</strong> Vérifier les fichiers<br>
            Vous devez avoir:
            <ul>
                <li><code>PHPMailer/Exception.php</code></li>
                <li><code>PHPMailer/PHPMailer.php</code></li>
                <li><code>PHPMailer/SMTP.php</code></li>
            </ul>
        </div>
        
        <div class="step">
            <strong>Étape 5:</strong> Recharger cette page pour vérifier
        </div>
    </div>
</body>
</html>