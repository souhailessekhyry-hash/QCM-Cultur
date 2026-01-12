// ================================================================
// ADMIN QUESTIONS - JAVASCRIPT FILE
// ================================================================

// Variables globales
let formModified = false;

// ================================================================
// INITIALISATION - عند تحميل الصفحة
// ================================================================
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeCounters();
    initializeValidation();
    initializeShortcuts();
    initializeBeforeUnload();
});

// ================================================================
// 1. INITIALISATION DU FORMULAIRE
// ================================================================
function initializeForm() {
    const form = document.getElementById('questionForm');
    if (!form) return;
    
    form.addEventListener('submit', handleFormSubmit);
    form.addEventListener('input', function() {
        formModified = true;
    });
}

// ================================================================
// 2. GESTION DE L'ENVOI DU FORMULAIRE AVEC AJAX
// ================================================================
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    formData.append('ajax_add', '1');
    
    const messageContainer = document.getElementById('messageContainer');
    const loading = document.getElementById('loading');
    const submitBtn = document.getElementById('submitBtn');
    
    // Afficher le loading
    showLoading(loading, submitBtn);
    messageContainer.innerHTML = '';
    
    try {
        const response = await fetch('admin_add_questions.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        // Masquer le loading
        hideLoading(loading, submitBtn);
        
        // Afficher le message
        showMessage(messageContainer, result.message, result.success ? 'success' : 'error');
        
        // Si succès
        if (result.success) {
            resetFormAfterSuccess(form, formData);
        }
        
    } catch (error) {
        hideLoading(loading, submitBtn);
        showMessage(messageContainer, '❌ Erreur de connexion : ' + error.message, 'error');
    }
}

// ================================================================
// 3. AFFICHER/MASQUER LOADING
// ================================================================
function showLoading(loadingElement, submitBtn) {
    if (loadingElement) loadingElement.classList.add('active');
    if (submitBtn) submitBtn.disabled = true;
}

function hideLoading(loadingElement, submitBtn) {
    if (loadingElement) loadingElement.classList.remove('active');
    if (submitBtn) submitBtn.disabled = false;
}

// ================================================================
// 4. AFFICHER LES MESSAGES
// ================================================================
function showMessage(container, message, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    container.appendChild(messageDiv);
    
    // Animation d'apparition
    setTimeout(() => {
        messageDiv.style.opacity = '1';
    }, 10);
    
    // Masquer après 5 secondes
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => messageDiv.remove(), 300);
    }, 5000);
}

// ================================================================
// 5. RÉINITIALISER LE FORMULAIRE APRÈS SUCCÈS
// ================================================================
function resetFormAfterSuccess(form, formData) {
    // Réinitialiser le formulaire
    form.reset();
    formModified = false;
    
    // Réinitialiser le compteur de caractères
    const questionCounter = document.getElementById('questionCounter');
    if (questionCounter) {
        questionCounter.textContent = '0';
    }
    
    // Mettre à jour les statistiques
    updateStatistics(formData.get('niveau_id'));
    
    // Scroll vers le haut
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Réinitialiser les bordures des champs
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.style.borderColor = '#e0e0e0';
    });
}

// ================================================================
// 6. METTRE À JOUR LES STATISTIQUES
// ================================================================
function updateStatistics(niveauId) {
    if (!niveauId) return;
    
    const statElement = document.getElementById('stat-' + niveauId);
    if (!statElement) return;
    
    const currentCount = parseInt(statElement.textContent);
    statElement.textContent = currentCount + 1;
    
    // Animation
    animateStatCounter(statElement);
}

function animateStatCounter(element) {
    element.style.transform = 'scale(1.3)';
    element.style.color = '#4CAF50';
    
    setTimeout(() => {
        element.style.transform = 'scale(1)';
        element.style.color = '#333';
    }, 300);
}

// ================================================================
// 7. COMPTEUR DE CARACTÈRES
// ================================================================
function initializeCounters() {
    const questionTextarea = document.getElementById('question_texte');
    const questionCounter = document.getElementById('questionCounter');
    
    if (questionTextarea && questionCounter) {
        questionTextarea.addEventListener('input', function() {
            questionCounter.textContent = this.value.length;
            
            // Changer la couleur si proche de la limite
            if (this.value.length > 450) {
                questionCounter.style.color = '#f44336';
            } else {
                questionCounter.style.color = '#999';
            }
        });
    }
}

// ================================================================
// 8. VALIDATION EN TEMPS RÉEL
// ================================================================
function initializeValidation() {
    const form = document.getElementById('questionForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        // Validation lors de la perte de focus
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        // Réinitialiser la validation lors de la saisie
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '#4CAF50';
            }
        });
    });
}

function validateField(field) {
    if (!field.value.trim()) {
        field.style.borderColor = '#f44336';
        shakeElement(field);
    } else {
        field.style.borderColor = '#4CAF50';
    }
}

function shakeElement(element) {
    element.style.animation = 'shake 0.5s';
    setTimeout(() => {
        element.style.animation = '';
    }, 500);
}

// ================================================================
// 9. RACCOURCIS CLAVIER
// ================================================================
function initializeShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl + Enter pour soumettre
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            const form = document.getElementById('questionForm');
            if (form) form.requestSubmit();
        }
        
        // Ctrl + R pour réinitialiser
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            resetFormWithConfirmation();
        }
        
        // ESC pour annuler/nettoyer les messages
        if (e.key === 'Escape') {
            clearMessages();
        }
    });
}

function resetFormWithConfirmation() {
    if (confirm('Voulez-vous vraiment réinitialiser le formulaire ?')) {
        const form = document.getElementById('questionForm');
        if (form) {
            form.reset();
            formModified = false;
            
            const questionCounter = document.getElementById('questionCounter');
            if (questionCounter) questionCounter.textContent = '0';
            
            // Réinitialiser les bordures
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.style.borderColor = '#e0e0e0';
            });
        }
    }
}

function clearMessages() {
    const messageContainer = document.getElementById('messageContainer');
    if (messageContainer) {
        messageContainer.innerHTML = '';
    }
}

// ================================================================
// 10. PROTECTION AVANT DE QUITTER
// ================================================================
function initializeBeforeUnload() {
    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            e.preventDefault();
            e.returnValue = 'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter ?';
            return e.returnValue;
        }
    });
}

// ================================================================
// 11. FONCTIONS UTILITAIRES
// ================================================================

// Vérifier si tous les champs sont remplis
function isFormValid(form) {
    const inputs = form.querySelectorAll('[required]');
    for (let input of inputs) {
        if (!input.value.trim()) {
            return false;
        }
    }
    return true;
}

// Mettre en évidence un champ
function highlightField(field) {
    field.style.borderColor = '#667eea';
    field.focus();
    
    setTimeout(() => {
        field.style.borderColor = '#e0e0e0';
    }, 2000);
}

// Afficher une notification toast
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Copier le texte dans le presse-papiers
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('✅ Copié dans le presse-papiers', 'success');
    }).catch(() => {
        showToast('❌ Erreur de copie', 'error');
    });
}

// Formater un nombre
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

// Obtenir la date et l'heure actuelles
function getCurrentDateTime() {
    const now = new Date();
    return now.toLocaleString('fr-FR');
}

// ================================================================
// 12. ANIMATIONS CSS (à ajouter dans votre CSS)
// ================================================================
const cssAnimations = `
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}
`;

// ================================================================
// EXPORT DES FONCTIONS (si vous utilisez des modules ES6)
// ================================================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showMessage,
        showToast,
        copyToClipboard,
        formatNumber,
        validateField,
        isFormValid
    };
}