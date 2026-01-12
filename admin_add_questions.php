<?php
require_once 'config.php';

// Vérifier si admin (optionnel)
// if (!isset($_SESSION['admin_id'])) {
//     header('Location: login_admin.php');
//     exit;
// }

$message = '';
$message_type = '';

// Traiter l'ajout de question via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add'])) {
    header('Content-Type: application/json');
    
    $niveau_id = (int)$_POST['niveau_id'];
    $question_texte = trim($_POST['question_texte']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $reponse_correcte = $_POST['reponse_correcte'];
    
    if (empty($question_texte) || empty($option_a) || empty($option_b) || 
        empty($option_c) || empty($option_d) || empty($reponse_correcte)) {
        echo json_encode(['success' => false, 'message' => '⚠️ Veuillez remplir tous les champs']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO questions (niveau_id, question_texte, option_a, option_b, option_c, option_d, reponse_correcte)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $niveau_id,
            $question_texte,
            $option_a,
            $option_b,
            $option_c,
            $option_d,
            $reponse_correcte
        ]);
        
        echo json_encode(['success' => true, 'message' => '✅ Question ajoutée avec succès !']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '❌ Erreur : ' . $e->getMessage()]);
    }
    exit;
}

// Récupérer les niveaux
$stmt = $pdo->query("SELECT * FROM niveaux ORDER BY numero_niveau");
$niveaux = $stmt->fetchAll();

// Récupérer les statistiques
$stmt = $pdo->query("
    SELECT n.id, n.nom, n.numero_niveau, COUNT(q.id) as total_questions
    FROM niveaux n
    LEFT JOIN questions q ON n.id = q.niveau_id
    GROUP BY n.id
    ORDER BY n.numero_niveau
");
$stats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Questions - Admin QCM</title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }
        
        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #667eea;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
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
        
        .required {
            color: #f44336;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-option input[type="radio"] {
            width: auto;
            cursor: pointer;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            display: inline-block;
            text-decoration: none;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .loading {
            display: none;
            text-align: center;
            padding: 10px;
            color: #667eea;
            font-weight: 600;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .counter {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 0.85em;
            color: #999;
        }
        
        .form-group {
            position: relative;
        }
        
        .char-counter {
            font-size: 0.85em;
            color: #999;
            text-align: right;
            margin-top: 5px;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            margin: 0 10px;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .links a:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Ajouter des Questions</h1>
            <p>Interface d'administration - QCM Culture Générale</p>
        </div>
        
        <div class="stats-grid">
            <!-- قبل </body> -->
<script src="admin_questions.js"></script>
</body>
            <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <h3>Niveau <?= $stat['numero_niveau'] ?></h3>
                    <div class="number" id="stat-<?= $stat['id'] ?>"><?= $stat['total_questions'] ?></div>
                    <small>questions</small>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-card">
            <div id="messageContainer"></div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Ajout en cours...</p>
            </div>
            
            <form id="questionForm">
                <div class="form-group">
                    <label for="niveau_id">
                        📊 Niveau <span class="required">*</span>
                    </label>
                    <select id="niveau_id" name="niveau_id" required>
                        <option value="">-- Sélectionner un niveau --</option>
                        <?php foreach ($niveaux as $niveau): ?>
                            <option value="<?= $niveau['id'] ?>">
                                Niveau <?= $niveau['numero_niveau'] ?> - <?= htmlspecialchars($niveau['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="question_texte">
                        ❓ Question <span class="required">*</span>
                    </label>
                    <textarea id="question_texte" 
                              name="question_texte" 
                              required 
                              placeholder="Entrez votre question ici..."
                              maxlength="500"></textarea>
                    <div class="char-counter">
                        <span id="questionCounter">0</span> / 500 caractères
                    </div>
                </div>
                
                <div class="options-grid">
                    <div class="form-group">
                        <label for="option_a">
                            🅰️ Option A <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="option_a" 
                               name="option_a" 
                               required 
                               placeholder="Option A"
                               maxlength="200">
                    </div>
                    
                    <div class="form-group">
                        <label for="option_b">
                            🅱️ Option B <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="option_b" 
                               name="option_b" 
                               required 
                               placeholder="Option B"
                               maxlength="200">
                    </div>
                    
                    <div class="form-group">
                        <label for="option_c">
                            ©️ Option C <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="option_c" 
                               name="option_c" 
                               required 
                               placeholder="Option C"
                               maxlength="200">
                    </div>
                    
                    <div class="form-group">
                        <label for="option_d">
                            🅳 Option D <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="option_d" 
                               name="option_d" 
                               required 
                               placeholder="Option D"
                               maxlength="200">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        ✅ Réponse Correcte <span class="required">*</span>
                    </label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="reponse_correcte" value="A" required>
                            <span>Option A</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="reponse_correcte" value="B" required>
                            <span>Option B</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="reponse_correcte" value="C" required>
                            <span>Option C</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="reponse_correcte" value="D" required>
                            <span>Option D</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    ➕ Ajouter la Question
                </button>
            </form>
        </div>
        
        <div class="links">
            <a href="index.php">🏠 Accueil</a>
            <a href="selection_niveau.php">📚 Tests</a>
        </div>
    </div>

    <script>
        // Compteur de caractères pour la question
        const questionTextarea = document.getElementById('question_texte');
        const questionCounter = document.getElementById('questionCounter');
        
        questionTextarea.addEventListener('input', function() {
            questionCounter.textContent = this.value.length;
        });
        
        // Gestion du formulaire avec AJAX
        const form = document.getElementById('questionForm');
        const messageContainer = document.getElementById('messageContainer');
        const loading = document.getElementById('loading');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Récupérer les données du formulaire
            const formData = new FormData(form);
            formData.append('ajax_add', '1');
            
            // Afficher le loading
            loading.classList.add('active');
            submitBtn.disabled = true;
            messageContainer.innerHTML = '';
            
            try {
                const response = await fetch('admin_add_questions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // Masquer le loading
                loading.classList.remove('active');
                submitBtn.disabled = false;
                
                // Afficher le message
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${result.success ? 'success' : 'error'}`;
                messageDiv.textContent = result.message;
                messageContainer.appendChild(messageDiv);
                
                // Si succès, réinitialiser le formulaire et mettre à jour les stats
                if (result.success) {
                    form.reset();
                    questionCounter.textContent = '0';
                    
                    // Mettre à jour le compteur de questions pour le niveau
                    const niveauId = formData.get('niveau_id');
                    const statElement = document.getElementById('stat-' + niveauId);
                    if (statElement) {
                        const currentCount = parseInt(statElement.textContent);
                        statElement.textContent = currentCount + 1;
                        
                        // Animation du compteur
                        statElement.style.transform = 'scale(1.3)';
                        statElement.style.color = '#4CAF50';
                        setTimeout(() => {
                            statElement.style.transform = 'scale(1)';
                            statElement.style.color = '#333';
                        }, 300);
                    }
                    
                    // Faire défiler vers le haut
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
                
                // Masquer le message après 5 secondes
                setTimeout(() => {
                    messageDiv.style.opacity = '0';
                    setTimeout(() => messageDiv.remove(), 300);
                }, 5000);
                
            } catch (error) {
                loading.classList.remove('active');
                submitBtn.disabled = false;
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message error';
                messageDiv.textContent = '❌ Erreur de connexion : ' + error.message;
                messageContainer.appendChild(messageDiv);
            }
        });
        
        // Validation en temps réel
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = '#f44336';
                } else {
                    this.style.borderColor = '#4CAF50';
                }
            });
            
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#4CAF50';
                }
            });
        });
        
        // Confirmation avant de quitter si le formulaire est rempli
        let formModified = false;
        form.addEventListener('input', function() {
            formModified = true;
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formModified) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter ?';
            }
        });
        
        form.addEventListener('submit', function() {
            formModified = false;
        });
        
        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            // Ctrl + Enter pour soumettre le formulaire
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                form.requestSubmit();
            }
            
            // Ctrl + R pour réinitialiser le formulaire
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                if (confirm('Voulez-vous vraiment réinitialiser le formulaire ?')) {
                    form.reset();
                    questionCounter.textContent = '0';
                    formModified = false;
                }
            }
        });
    </script>
</body>
</html>