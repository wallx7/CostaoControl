/**
 * Tutorial Interativo do Dashboard IA
 * Guia passo a passo para novos usuários
 */

class IADashboardTutorial {
    constructor() {
        this.steps = [
            {
                element: '.bg-gradient-to-br.from-indigo-500',
                title: '🧠 Bem-vindo ao Dashboard IA!',
                content: 'Este é seu assistente inteligente de inventário. Aqui você verá insights e previsões baseadas em inteligência artificial.',
                position: 'bottom'
            },
            {
                element: '.grid.grid-cols-1.md\\:grid-cols-4',
                title: '📊 Cards de Status',
                content: 'Estes cards mostram o status do serviço de IA, total de equipamentos, modelos carregados e previsões ativas.',
                position: 'bottom'
            },
            {
                element: '#statusChart',
                title: '📈 Gráficos Inteligentes',
                content: 'Visualize a distribuição do seu inventário por status e tipo de equipamento com gráficos interativos.',
                position: 'top'
            },
            {
                element: '.bg-white.rounded-lg.shadow.p-6.mb-8',
                title: '🔮 Previsões de Demanda',
                content: 'A IA analisa padrões históricos e prevê demandas futuras para otimizar suas compras.',
                position: 'top'
            },
            {
                element: '.flex.flex-wrap.gap-3',
                title: '⚡ Ações Rápidas',
                content: 'Use estes botões para treinar modelos, gerar relatórios, atualizar previsões e exportar dados.',
                position: 'top'
            }
        ];
        
        this.currentStep = 0;
        this.overlay = null;
        this.tooltip = null;
    }

    init() {
        // Verificar se é a primeira visita
        const hasSeenTutorial = localStorage.getItem('iaDashboardTutorialCompleted');
        if (!hasSeenTutorial) {
            setTimeout(() => {
                this.start();
            }, 3000); // Esperar 3 segundos após o confete
        }
    }

    start() {
        this.createOverlay();
        this.createTooltip();
        this.showStep(this.currentStep);
    }

    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'tutorial-overlay';
        this.overlay.innerHTML = `
            <style>
                .tutorial-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    z-index: 9998;
                    display: none;
                }
                
                .tutorial-highlight {
                    position: absolute;
                    border: 3px solid #667eea;
                    border-radius: 8px;
                    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.7);
                    z-index: 9999;
                    animation: pulse 2s infinite;
                }
                
                @keyframes pulse {
                    0% { box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.7), 0 0 0 0 rgba(102, 126, 234, 0.7); }
                    70% { box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.7), 0 0 0 10px rgba(102, 126, 234, 0); }
                    100% { box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.7), 0 0 0 0 rgba(102, 126, 234, 0); }
                }
                
                .tutorial-tooltip {
                    position: absolute;
                    background: white;
                    border-radius: 12px;
                    padding: 20px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                    z-index: 10000;
                    max-width: 350px;
                    display: none;
                }
                
                .tutorial-tooltip h3 {
                    margin: 0 0 10px 0;
                    color: #333;
                    font-size: 18px;
                    font-weight: bold;
                }
                
                .tutorial-tooltip p {
                    margin: 0 0 20px 0;
                    color: #666;
                    line-height: 1.5;
                }
                
                .tutorial-buttons {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .tutorial-step-indicator {
                    color: #999;
                    font-size: 14px;
                }
                
                .tutorial-btn {
                    padding: 8px 16px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    transition: all 0.3s ease;
                }
                
                .tutorial-btn-skip {
                    background: #f3f4f6;
                    color: #666;
                }
                
                .tutorial-btn-skip:hover {
                    background: #e5e7eb;
                }
                
                .tutorial-btn-next {
                    background: #667eea;
                    color: white;
                }
                
                .tutorial-btn-next:hover {
                    background: #5a67d8;
                }
                
                .tutorial-btn-finish {
                    background: #48bb78;
                    color: white;
                }
                
                .tutorial-btn-finish:hover {
                    background: #38a169;
                }
            </style>
        `;
        document.body.appendChild(this.overlay);
    }

    createTooltip() {
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'tutorial-tooltip';
        this.tooltip.innerHTML = `
            <h3></h3>
            <p></p>
            <div class="tutorial-buttons">
                <button class="tutorial-btn tutorial-btn-skip" onclick="tutorial.skip()">Pular</button>
                <div>
                    <span class="tutorial-step-indicator"></span>
                    <button class="tutorial-btn tutorial-btn-next" onclick="tutorial.next()">Próximo</button>
                </div>
            </div>
        `;
        document.body.appendChild(this.tooltip);
    }

    showStep(stepIndex) {
        const step = this.steps[stepIndex];
        const element = document.querySelector(step.element);
        
        if (!element) {
            console.warn('Elemento não encontrado:', step.element);
            this.next();
            return;
        }

        // Mostrar overlay
        this.overlay.style.display = 'block';
        
        // Criar highlight
        const highlight = document.createElement('div');
        highlight.className = 'tutorial-highlight';
        
        const rect = element.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        highlight.style.left = (rect.left + scrollLeft - 5) + 'px';
        highlight.style.top = (rect.top + scrollTop - 5) + 'px';
        highlight.style.width = (rect.width + 10) + 'px';
        highlight.style.height = (rect.height + 10) + 'px';
        
        // Remover highlight anterior se existir
        const existingHighlight = document.querySelector('.tutorial-highlight');
        if (existingHighlight) {
            existingHighlight.remove();
        }
        
        document.body.appendChild(highlight);

        // Atualizar tooltip
        this.tooltip.querySelector('h3').textContent = step.title;
        this.tooltip.querySelector('p').textContent = step.content;
        this.tooltip.querySelector('.tutorial-step-indicator').textContent = 
            `Passo ${stepIndex + 1} de ${this.steps.length}`;

        // Posicionar tooltip
        this.positionTooltip(rect, step.position);
        
        // Atualizar botão
        const nextBtn = this.tooltip.querySelector('.tutorial-btn-next');
        if (stepIndex === this.steps.length - 1) {
            nextBtn.textContent = 'Finalizar';
            nextBtn.className = 'tutorial-btn tutorial-btn-finish';
            nextBtn.onclick = () => this.finish();
        } else {
            nextBtn.textContent = 'Próximo';
            nextBtn.className = 'tutorial-btn tutorial-btn-next';
            nextBtn.onclick = () => this.next();
        }

        this.tooltip.style.display = 'block';
        
        // Scroll para o elemento
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    positionTooltip(elementRect, position) {
        const tooltipRect = this.tooltip.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        let left, top;
        
        switch (position) {
            case 'top':
                left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
                top = elementRect.top - tooltipRect.height - 20;
                break;
            case 'bottom':
                left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
                top = elementRect.bottom + 20;
                break;
            case 'left':
                left = elementRect.left - tooltipRect.width - 20;
                top = elementRect.top + (elementRect.height - tooltipRect.height) / 2;
                break;
            case 'right':
                left = elementRect.right + 20;
                top = elementRect.top + (elementRect.height - tooltipRect.height) / 2;
                break;
            default:
                left = elementRect.left;
                top = elementRect.bottom + 20;
        }
        
        // Garantir que o tooltip fique dentro da viewport
        left = Math.max(10, Math.min(left, viewportWidth - tooltipRect.width - 10));
        top = Math.max(10, Math.min(top, viewportHeight - tooltipRect.height - 10));
        
        this.tooltip.style.left = left + 'px';
        this.tooltip.style.top = top + window.pageYOffset + 'px';
    }

    next() {
        this.currentStep++;
        if (this.currentStep < this.steps.length) {
            this.showStep(this.currentStep);
        } else {
            this.finish();
        }
    }

    skip() {
        this.finish();
    }

    finish() {
        // Limpar elementos do tutorial
        this.overlay.style.display = 'none';
        this.tooltip.style.display = 'none';
        
        const highlight = document.querySelector('.tutorial-highlight');
        if (highlight) {
            highlight.remove();
        }
        
        // Marcar tutorial como concluído
        localStorage.setItem('iaDashboardTutorialCompleted', 'true');
        
        // Mostrar mensagem de conclusão
        setTimeout(() => {
            alert('🎉 Tutorial concluído!\n\nAgora você está pronto para aproveitar todas as funcionalidades inteligentes do dashboard.');
        }, 500);
    }
}

// Inicializar tutorial quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    window.tutorial = new IADashboardTutorial();
    
    // Adicionar botão para reabrir tutorial
    const tutorialBtn = document.createElement('button');
    tutorialBtn.innerHTML = '❓ Ajuda';
    tutorialBtn.className = 'fixed bottom-4 right-4 bg-purple-600 text-white px-4 py-2 rounded-full shadow-lg hover:bg-purple-700 transition duration-300 z-50';
    tutorialBtn.onclick = function() {
        tutorial.start();
    };
    document.body.appendChild(tutorialBtn);
});