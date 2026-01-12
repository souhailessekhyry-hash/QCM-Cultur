# QCM Culture Générale

Application web de quiz de culture générale en français avec système d'inscription, connexion et vérification par email.

## Fonctionnalités

- Inscription et connexion des utilisateurs
- Système de vérification par email avec code à usage unique
- Sélection de niveaux de difficulté
- Test de culture générale avec 20 questions
- Affichage des résultats détaillés
- Historique des tentatives

## Installation

1. Clonez le dépôt
2. Configurez votre serveur web (Apache/Nginx) pour pointer vers le dossier
3. Créez la base de données MySQL selon le schéma prévu
4. Configurez les variables d'environnement dans un fichier `.env` (copiez `.env.example`)

## Configuration

Copiez le fichier `.env.example` en `.env` et modifiez les valeurs selon votre configuration :

```
DB_HOST=localhost
DB_NAME=votre_base_de_donnees
DB_USER=votre_utilisateur
DB_PASS=votre_mot_de_passe
SMTP_USERNAME=votre_email@gmail.com
SMTP_PASSWORD=votre_app_password
```

Pour utiliser Gmail, vous devez activer l'authentification à deux facteurs et générer un mot de passe d'application.

## Dépendances

- PHP 7.4+
- MySQL
- PHPMailer (fourni dans le dossier PHPMailer/)

## Structure des fichiers

- `index.php` - Page d'accueil
- `inscription.php` - Formulaire d'inscription
- `connexion.php` - Connexion avec vérification par email
- `selection_niveau.php` - Sélection du niveau de difficulté
- `test.php` - Test de culture générale
- `resultat.php` - Affichage des résultats
- `config.php` - Configuration de la base de données
- `email_config.php` - Configuration de l'envoi d'emails

## Sécurité

- Tous les affichages de données utilisateur sont échappés avec `htmlspecialchars`
- Utilisation de requêtes préparées pour prévenir les injections SQL
- Mots de passe hachés avec `password_hash`
- Codes de vérification à usage unique avec expiration

## Auteur

Projet développé par souhail