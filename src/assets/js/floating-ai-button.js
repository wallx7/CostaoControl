/**
 * Botão Flutuante de IA - Global
 * Aparece em todas as páginas para acesso rápido ao Dashboard IA
 */

class FloatingAIButton {
    constructor() {
        this.button = null;
        this.tooltip = null;
        this.init();
    }

    init() {
        this.createButton();
        this.createTooltip();
        this.addEventListeners();
        this.startIdleAnimation();
    }

    createButton() {
        this.button = document.createElement('div');
        this.button.className = 'floating-ai-button';
        this.button.innerHTML = `
            <div class="ai-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                    <path d="M8.5 14c.28 0 .5-.22.5-.5s-.22-.5-.5-.5-.5.22-.5.5.22.5.5.5z"/>
                    <path d="M15.5 14c.28 0 .5-.22.5-.5s-.22-.5-.5-.5-.5.22-.5.5.22.5.5.5z"/>
                    <path d="M12 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm0-4c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1z"/>
                </svg>
            </div>
            <div class="ai-pulse"></div>
        `;

        const styles = `
            .floating-ai-button {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                cursor: pointer;
                z-index: 1000;
                box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .floating-ai-button:hover {
                transform: scale(1.1) translateY(-5px);
                box-shadow: 0 12px 40px rgba(102, 126, 234, 0.6);
            }

            .floating-ai-button:active {
                transform: scale(0.95);
            }

            .ai-icon {
                width: 30px;
                height: 30px;
                color: white;
                z-index: 2;
                position: relative;
            }

            .ai-pulse {
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.1);
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    transform: scale(1);
                    opacity: 1;
                }
                50% {
                    transform: scale(1.3);
                    opacity: 0.7;
                }
                100% {
                    transform: scale(1.6);
                    opacity: 0;
                }
            }

            .ai-tooltip {
                position: fixed;
                bottom: 100px;
                right: 30px;
                background: white;
                padding: 16px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                max-width: 280px;
                z-index: 999;
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.3s ease;
                pointer-events: none;
            }

            .ai-tooltip.show {
                opacity: 1;
                transform: translateY(0);
            }

            .ai-tooltip::after {
                content: '';
                position: absolute;
                bottom: -8px;
                right: 20px;
                width: 0;
                height: 0;
                border-left: 8px solid transparent;
                border-right: 8px solid transparent;
                border-top: 8px solid white;
            }

            .ai-tooltip h4 {
                margin: 0 0 8px 0;
                color: #333;
                font-size: 16px;
                font-weight: 600;
            }

            .ai-tooltip p {
                margin: 0;
                color: #666;
                font-size: 14px;
                line-height: 1.5;
            }

            .ai-tooltip .close {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 24px;
                height: 24px;
                border: none;
                background: none;
                cursor: pointer;
                color: #999;
                font-size: 20px;
                line-height: 1;
            }

            .ai-tooltip .close:hover {
                color: #666;
            }

            /* Animação de idle - botão "respirando" */
            .floating-ai-button.idle {
                animation: breathe 4s ease-in-out infinite;
            }

            @keyframes breathe {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            /* Animação de notificação */
            .floating-ai-button.notify {
                animation: shake 0.5s ease-in-out;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px) rotate(-5deg); }
                75% { transform: translateX(5px) rotate(5deg); }
            }

            /* Responsividade */
            @media (max-width: 768px) {
                .floating-ai-button {
                    bottom: 20px;
                    right: 20px;
                    width: 50px;
                    height: 50px;
                }
                
                .ai-icon {
                    width: 24px;
                    height: 24px;
                }
                
                .ai-tooltip {
                    bottom: 80px;
                    right: 20px;
                    max-width: 240px;
                }
            }
        `;

        // Adicionar estilos ao head
        const styleSheet = document.createElement('style');
        styleSheet.textContent = styles;
        document.head.appendChild(styleSheet);

        // Adicionar ao body
        document.body.appendChild(this.button);
    }

    createTooltip() {
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'ai-tooltip';
        this.tooltip.innerHTML = `
            <button class="close" onclick="floatingAIButton.hideTooltip()">×</button>
            <h4>🤖 Assistente IA</h4>
            <p>Clique aqui para acessar o Dashboard de IA e ver análises inteligentes do seu inventário!</p>
        `;

        document.body.appendChild(this.tooltip);

        // Mostrar tooltip após 5 segundos na primeira visita
        setTimeout(() => {
            if (!localStorage.getItem('aiButtonTooltipShown')) {
                this.showTooltip();
                localStorage.setItem('aiButtonTooltipShown', 'true');
            }
        }, 5000);
    }

    addEventListeners() {
        this.button.addEventListener('click', () => {
            this.hideTooltip();
            this.goToDashboard();
        });

        this.button.addEventListener('mouseenter', () => {
            this.showTooltip();
        });

        this.button.addEventListener('mouseleave', () => {
            setTimeout(() => {
                if (!this.tooltip.matches(':hover')) {
                    this.hideTooltip();
                }
            }, 300);
        });
    }

    showTooltip() {
        if (this.tooltip) {
            this.tooltip.classList.add('show');
        }
    }

    hideTooltip() {
        if (this.tooltip) {
            this.tooltip.classList.remove('show');
        }
    }

    goToDashboard() {
        // Adicionar efeito de transição
        this.button.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            window.location.href = 'dashboard-ia.php';
        }, 200);
    }

    startIdleAnimation() {
        // Adicionar animação de "respiração" quando idle
        setInterval(() => {
            if (!this.button.matches(':hover')) {
                this.button.classList.add('idle');
                setTimeout(() => {
                    this.button.classList.remove('idle');
                }, 4000);
            }
        }, 15000); // A cada 15 segundos

        // Notificação ocasional (30% de chance a cada 2 minutos)
        setInterval(() => {
            if (Math.random() < 0.3 && !this.button.matches(':hover')) {
                this.button.classList.add('notify');
                setTimeout(() => {
                    this.button.classList.remove('notify');
                }, 500);
            }
        }, 120000);
    }

    // Método público para mostrar mensagens customizadas
    showMessage(title, message) {
        if (this.tooltip) {
            this.tooltip.querySelector('h4').textContent = title;
            this.tooltip.querySelector('p').textContent = message;
            this.showTooltip();
            
            setTimeout(() => {
                this.hideTooltip();
            }, 5000);
        }
    }
}

// Inicializar quando o DOM estiver pronto
let floatingAIButton;

document.addEventListener('DOMContentLoaded', function() {
    // Não criar botão se já estiver no dashboard IA
    if (window.location.pathname.includes('dashboard-ia.php')) {
        return;
    }
    
    floatingAIButton = new FloatingAIButton();
    
    // Adicionar ao escopo global para acessos externos
    window.floatingAIButton = floatingAIButton;
});

// Adicionar atalho de teclado (Ctrl + I) para dashboard IA
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'i') {
        e.preventDefault();
        window.location.href = 'dashboard-ia.php';
    }
});