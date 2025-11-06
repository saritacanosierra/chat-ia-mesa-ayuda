/**
 * Vista: Robot 3D - Spline Design
 * Maneja la visualización e interacción del robot 3D usando spline-viewer
 */
class Robot3DView {
    constructor(viewerId, controller = null) {
        this.viewerId = viewerId;
        this.viewer = null;
        this.isLoaded = false;
        this.controller = controller; // Referencia al controlador para mostrar mensajes
        
        // Suprimir advertencias de Spline sobre morphTargetInfluences (no afectan funcionalidad)
        this.suppressSplineWarnings();
    }

    /**
     * Suprime advertencias específicas de Spline/Three.js que no afectan la funcionalidad
     */
    suppressSplineWarnings() {
        const originalError = console.error;
        const originalWarn = console.warn;
        
        // Filtro para errores/advertencias de Spline sobre morphTargetInfluences
        const splineWarningPatterns = [
            /THREE\.PropertyBinding.*morphTargetInfluences.*wasn't found/i,
            /Trying to update property for track.*morphTargetInfluences/i,
            /L_Eye\.morphTargetInfluences/i,
            /R_Eye\.morphTargetInfluences/i
        ];
        
        console.error = (...args) => {
            const message = args.join(' ');
            const shouldSuppress = splineWarningPatterns.some(pattern => pattern.test(message));
            if (!shouldSuppress) {
                originalError.apply(console, args);
            }
        };
        
        console.warn = (...args) => {
            const message = args.join(' ');
            const shouldSuppress = splineWarningPatterns.some(pattern => pattern.test(message));
            if (!shouldSuppress) {
                originalWarn.apply(console, args);
            }
        };
    }

    /**
     * Inicializa el robot 3D desde Spline Design
     * @param {string} sceneUrl - URL de la escena de Spline (opcional, por defecto usa la del HTML)
     */
    async init(sceneUrl = null) {
        // Esperar a que el componente spline-viewer esté disponible
        await this.waitForSplineViewer();

        const container = document.getElementById('robot3DContainer');
        if (!container) {
            console.warn('Contenedor del robot 3D no encontrado');
            return;
        }

        // Obtener o crear el elemento spline-viewer
        this.viewer = document.getElementById(this.viewerId);
        
        if (!this.viewer) {
            // Si no existe, crearlo
            this.viewer = document.createElement('spline-viewer');
            this.viewer.id = this.viewerId;
            container.innerHTML = '';
            container.appendChild(this.viewer);
        }

        // Si se proporciona una URL, actualizarla
        if (sceneUrl) {
            this.viewer.setAttribute('url', sceneUrl);
        } else {
            // Usar la URL por defecto si no hay una en el HTML
            const currentUrl = this.viewer.getAttribute('url');
            if (!currentUrl) {
                this.viewer.setAttribute('url', 'https://prod.spline.design/AUWIhN4z2jz-OeM2/scene.splinecode');
            }
        }

        // Deshabilitar todas las interacciones del cursor sobre el robot
        this.viewer.setAttribute('disable-controls', '');
        this.viewer.style.pointerEvents = 'none';
        
        // Deshabilitar todos los eventos del mouse en el viewer
        if (this.viewer.shadowRoot) {
            const canvas = this.viewer.shadowRoot.querySelector('canvas');
            if (canvas) {
                canvas.style.pointerEvents = 'none';
                
                // Prevenir todos los eventos del mouse
                const preventAllEvents = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                };
                
                ['mousedown', 'mousemove', 'mouseup', 'mouseenter', 'mouseleave', 
                 'click', 'dblclick', 'contextmenu', 'wheel', 'touchstart', 
                 'touchmove', 'touchend'].forEach(eventType => {
                    canvas.addEventListener(eventType, preventAllEvents, { capture: true, passive: false });
                });
            }
        }

        // Configurar eventos del viewer
        this.setupInteractions();

        // Ocultar el botón "Built with Spline" después de que se cargue
        this.hideSplineBranding();

        this.isLoaded = true;
        console.log('🤖 Robot 3D (spline-viewer) inicializado');
    }

    /**
     * Espera a que el componente spline-viewer esté disponible
     */
    async waitForSplineViewer() {
        // Esperar hasta 5 segundos para que el componente se registre
        const maxWait = 5000;
        const checkInterval = 100;
        let elapsed = 0;

        while (elapsed < maxWait) {
            // Verificar si el componente está definido
            if (customElements.get('spline-viewer')) {
                return;
            }
            
            await new Promise(resolve => setTimeout(resolve, checkInterval));
            elapsed += checkInterval;
        }

        // Si no se encuentra, mostrar advertencia pero continuar
        console.warn('spline-viewer no se cargó completamente, pero continuando...');
    }

    /**
     * Oculta el botón "Built with Spline"
     */
    hideSplineBranding() {
        if (!this.viewer) return;

        // Función para ocultar elementos de branding
        const hideElement = (el) => {
            if (el) {
                el.style.display = 'none';
                el.style.visibility = 'hidden';
                el.style.opacity = '0';
                el.style.pointerEvents = 'none';
                el.style.position = 'absolute';
                el.style.left = '-9999px';
            }
        };

        // Función para buscar y ocultar branding
        const hideBranding = () => {
            // Método 1: Buscar en el shadow DOM
            if (this.viewer.shadowRoot) {
                // Buscar enlaces y botones
                const branding = this.viewer.shadowRoot.querySelector('a[href*="spline"], button, [class*="branding"], [class*="spline"]');
                if (branding) hideElement(branding);

                // Buscar por texto
                const allElements = this.viewer.shadowRoot.querySelectorAll('*');
                allElements.forEach(el => {
                    const text = el.textContent || '';
                    if (text.toLowerCase().includes('built with spline') || 
                        text.toLowerCase().includes('spline')) {
                        hideElement(el);
                    }
                });
            }

            // Método 2: Buscar elementos hijos directos
            const links = this.viewer.querySelectorAll('a[href*="spline"], button');
            links.forEach(hideElement);
        };

        // Intentar inmediatamente
        hideBranding();

        // Usar MutationObserver para detectar cuando se agregan elementos
        if (this.viewer.shadowRoot) {
            const observer = new MutationObserver(() => {
                hideBranding();
            });

            observer.observe(this.viewer.shadowRoot, {
                childList: true,
                subtree: true
            });

            // También observar el viewer directamente
            const viewerObserver = new MutationObserver(() => {
                hideBranding();
            });

            viewerObserver.observe(this.viewer, {
                childList: true,
                subtree: true
            });
        }

        // Intentar después de delays para asegurar que el componente esté completamente cargado
        setTimeout(hideBranding, 500);
        setTimeout(hideBranding, 1000);
        setTimeout(hideBranding, 2000);
        setTimeout(hideBranding, 3000);
    }

    /**
     * Configura interacciones con el robot
     */
    setupInteractions() {
        if (!this.viewer) return;

        const container = this.viewer.parentElement;
        if (container) {
            // Habilitar clics en el contenedor para mostrar mensaje de ayuda
            container.style.pointerEvents = 'auto';
            container.style.cursor = 'pointer';
            
            // Agregar event listener para el clic
            container.addEventListener('click', (e) => {
                e.stopPropagation();
                this.showHelpMessage();
            });
        }

        // Agregar animación sutil cuando el usuario escribe
        const questionInput = document.getElementById('questionInput');
        if (questionInput) {
            questionInput.addEventListener('focus', () => {
                this.animateOnInput();
            });
        }

        // Deshabilitar todas las interacciones del mouse en el viewer después de que se cargue
        setTimeout(() => {
            this.disableMouseInteractions();
        }, 1000);
    }

    /**
     * Muestra un mensaje de ayuda como nube de texto sobre el robot
     */
    showHelpMessage() {
        const container = this.viewer?.parentElement;
        if (!container) return;

        // Eliminar nube anterior si existe
        const existingBubble = container.querySelector('.robot-help-bubble');
        if (existingBubble) {
            existingBubble.remove();
            return; // Si ya existe, ocultarla al hacer clic de nuevo
        }

        // Crear la nube de texto
        const bubble = document.createElement('div');
        bubble.className = 'robot-help-bubble';
        bubble.innerHTML = `
            <div class="robot-help-bubble-content">
                <div class="robot-help-bubble-header">
                    <span>🤖</span>
                    <strong>¿Necesitas ayuda?</strong>
                    <button class="robot-help-bubble-close">×</button>
                </div>
                <div class="robot-help-bubble-body">
                    <p>Para contactar con soporte técnico:</p>
                    <ul>
                        <li>📧 <strong>Email:</strong> soporte@mesaayuda.com</li>
                        <li>📞 <strong>Teléfono:</strong> +57 1 234 5678</li>
                        <li>🎫 <strong>Ticket:</strong> <a href="http://172.16.1.46:3000/" target="_blank" class="robot-help-link">Abrir sistema de gestión</a></li>
                    </ul>
                    <p><strong>Horario:</strong> Lunes a Viernes 8:00 AM - 6:00 PM</p>
                </div>
            </div>
            <div class="robot-help-bubble-arrow"></div>
        `;

        // Agregar al contenedor
        container.appendChild(bubble);

        // Botón de cerrar
        const closeBtn = bubble.querySelector('.robot-help-bubble-close');
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            bubble.remove();
        });

        // Enlace del ticket - abrir en nueva pestaña y no cerrar la nube
        const ticketLink = bubble.querySelector('.robot-help-link');
        if (ticketLink) {
            ticketLink.addEventListener('click', (e) => {
                e.stopPropagation();
                // El href ya está configurado, solo necesitamos prevenir que se cierre la nube
                window.open('http://172.16.1.46:3000/', '_blank');
            });
        }

        // Cerrar al hacer clic fuera (pero no si es en un enlace dentro de la nube)
        const closeOnOutsideClick = (e) => {
            // No cerrar si el clic es en un enlace dentro de la nube
            if (bubble.contains(e.target)) {
                const clickedLink = e.target.closest('a');
                if (clickedLink) {
                    return; // No cerrar si es un enlace
                }
            }
            
            if (!bubble.contains(e.target) && !container.contains(e.target)) {
                bubble.remove();
                document.removeEventListener('click', closeOnOutsideClick);
            }
        };
        
        // Esperar un momento antes de agregar el listener para evitar que se cierre inmediatamente
        setTimeout(() => {
            document.addEventListener('click', closeOnOutsideClick);
        }, 100);

        // Animación de entrada
        setTimeout(() => {
            bubble.classList.add('show');
        }, 10);
    }

    /**
     * Deshabilita todas las interacciones del mouse en el viewer
     */
    disableMouseInteractions() {
        if (!this.viewer) return;

        // Deshabilitar completamente las interacciones
        this.viewer.style.pointerEvents = 'none';
        
        // Deshabilitar en el shadow DOM
        if (this.viewer.shadowRoot) {
            const canvas = this.viewer.shadowRoot.querySelector('canvas');
            if (canvas) {
                canvas.style.pointerEvents = 'none';
                
                // Prevenir todos los eventos del mouse
                const preventAllEvents = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                };
                
                ['mousedown', 'mousemove', 'mouseup', 'mouseenter', 'mouseleave', 
                 'click', 'dblclick', 'contextmenu', 'wheel', 'touchstart', 
                 'touchmove', 'touchend'].forEach(eventType => {
                    canvas.addEventListener(eventType, preventAllEvents, { capture: true, passive: false });
                });
            }
        }
    }

    /**
     * Anima el robot cuando el usuario interactúa con el input
     */
    animateOnInput() {
        const container = this.viewer?.parentElement;
        if (container) {
            container.style.transform = 'scale(1.1)';
            setTimeout(() => {
                if (container) {
                    container.style.transform = '';
                }
            }, 300);
        }
    }

    /**
     * Muestra un fallback si el robot no se puede cargar
     */
    showFallback() {
        const container = document.getElementById('robot3DContainer');
        if (container) {
            container.innerHTML = `
                <div style="
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: radial-gradient(circle, rgba(108, 140, 255, 0.2) 0%, transparent 70%);
                    border-radius: 50%;
                    font-size: 48px;
                    color: var(--color-primary);
                    animation: floatRobot 3s ease-in-out infinite;
                ">
                    🤖
                </div>
            `;
        }
    }

    /**
     * Limpia recursos cuando se destruye la vista
     */
    destroy() {
        if (this.viewer) {
            // El componente spline-viewer se limpia automáticamente
            this.viewer = null;
        }
        this.isLoaded = false;
    }
}
