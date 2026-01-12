<?php
// Configuration Email avec PHPMailer
// معلوماتك صحيحة، فقط نضيف PHPMailer

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'souhailessekhayry@gmail.com');
define('SMTP_PASSWORD', 'gqaf qnsv njwf temu');  // App Password ديالك
define('SMTP_FROM_EMAIL', 'souhailessekhayry@gmail.com');
define('SMTP_FROM_NAME', 'QCM Culture Générale');

// Durée de validité du code (en minutes)
define('CODE_VALIDITY_MINUTES', 10);

// Charger PHPMailer une seule fois au début
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    // Installation via Composer
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
    // Installation manuelle
    require_once __DIR__ . '/PHPMailer/Exception.php';
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';
}

// Import des classes PHPMailer (en dehors de la fonction)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Fonction pour envoyer un email avec PHPMailer
 */
function envoyerCodeVerification($email, $code, $nom_complet) {
    // Vérifier si PHPMailer est disponible
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer non trouvé. Code de test: $code");
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Pour localhost - désactiver vérification SSL
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Expéditeur et destinataire
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $nom_complet);
        
        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Code de verification - QCM Culture Generale';
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 3px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; }
                .code { font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Code de Verification</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$nom_complet</strong>,</p>
                    <p>Vous avez demande a vous connecter a votre compte QCM Culture Generale.</p>
                    <p>Voici votre code de verification :</p>
                    
                    <div class='code-box'>
                        <div class='code'>$code</div>
                    </div>
                    
                    <div class='warning'>
                        ⚠️ <strong>Important :</strong>
                        <ul>
                            <li>Ce code est valable pendant " . CODE_VALIDITY_MINUTES . " minutes</li>
                            <li>Ne partagez jamais ce code avec quelqu'un</li>
                            <li>Si vous n'avez pas demande ce code, ignorez cet email</li>
                        </ul>
                    </div>
                    
                    <p>Si le code ne fonctionne pas, vous pouvez demander un nouveau code.</p>
                </div>
                <div class='footer'>
                    <p>Ceci est un email automatique, merci de ne pas y repondre.</p>
                    <p>&copy; " . date('Y') . " QCM Culture Generale - Tous droits reserves</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $message;
        $mail->AltBody = "Votre code de verification est: $code\n\nCe code est valable pendant " . CODE_VALIDITY_MINUTES . " minutes.";
        
        // Envoyer l'email
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log de l'erreur
        error_log("Erreur PHPMailer: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Générer un code aléatoire à 6 chiffres
 */
function genererCode() {
    return sprintf("%06d", mt_rand(0, 999999));
}

/**
 * Enregistrer le code dans la base de données
 */
function enregistrerCode($pdo, $utilisateur_id, $code, $email) {
    $expiration = date('Y-m-d H:i:s', strtotime('+' . CODE_VALIDITY_MINUTES . ' minutes'));
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $pdo->prepare("
        INSERT INTO codes_verification (utilisateur_id, code, email, date_expiration, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([$utilisateur_id, $code, $email, $expiration, $ip]);
}

/**
 * Vérifier si un code est valide
 */
function verifierCode($pdo, $email, $code) {
    $stmt = $pdo->prepare("
        SELECT cv.*, u.id as user_id, u.nom, u.prenom
        FROM codes_verification cv
        JOIN utilisateurs u ON cv.utilisateur_id = u.id
        WHERE cv.email = ? 
        AND cv.code = ? 
        AND cv.utilise = 0
        AND cv.date_expiration > NOW()
        ORDER BY cv.date_envoi DESC
        LIMIT 1
    ");
    
    $stmt->execute([$email, $code]);
    return $stmt->fetch();
}

/**
 * Marquer un code comme utilisé
 */
function marquerCodeUtilise($pdo, $code_id) {
    $stmt = $pdo->prepare("UPDATE codes_verification SET utilise = 1 WHERE id = ?");
    return $stmt->execute([$code_id]);
}
?>