/**
 * Sistema de Integración del Juego DA-I en Laravel
 * 
 * Combinaciones de teclas:
 * - Ctrl + Alt + G: Iniciar/Pausar juego
 * - Ctrl + Alt + X: Pausar juego y volver a la app (modo jefe)
 */

(function() {
    'use strict';

    // Estado del juego
    const gameState = {
        isActive: false,
        isPaused: false,
        lastView: null,
        gameIframe: null,
        gameContainer: null
    };

    let pauseGameTimer = null;

    // Combinaciones de teclas
    const GAME_TOGGLE_KEY = 'g'; // Ctrl + Alt + G
    const BOSS_MODE_KEY = 'x';   // Ctrl + Alt + X (eXit - modo jefe)

    // Guardar la última vista actual
    function saveLastView() {
        const currentUrl = window.location.href;
        const currentPath = window.location.pathname;
        
        // Guardar en sessionStorage para que persista durante la sesión
        sessionStorage.setItem('dai_last_view_url', currentUrl);
        sessionStorage.setItem('dai_last_view_path', currentPath);
        
        // También guardar el scroll position
        sessionStorage.setItem('dai_last_scroll', window.pageYOffset || document.documentElement.scrollTop);
        
        gameState.lastView = {
            url: currentUrl,
            path: currentPath,
            scroll: window.pageYOffset || document.documentElement.scrollTop
        };
    }

    // Restaurar la última vista
    function restoreLastView() {
        const savedUrl = sessionStorage.getItem('dai_last_view_url');
        const savedPath = sessionStorage.getItem('dai_last_view_path');
        const savedScroll = parseInt(sessionStorage.getItem('dai_last_scroll') || '0');

        if (savedPath && savedPath !== window.location.pathname) {
            // Si la URL guardada es diferente, navegar a ella
            window.location.href = savedUrl || savedPath;
        } else if (savedScroll > 0) {
            // Si estamos en la misma página, restaurar el scroll
            window.scrollTo(0, savedScroll);
        }
    }

    // Crear el contenedor del juego
    function createGameContainer() {
        if (gameState.gameContainer) {
            return gameState.gameContainer;
        }

        const container = document.createElement('div');
        container.id = 'dai-game-container';
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #0a0a0a;
            z-index: 99999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        `;
        // Hacer que el contenedor pueda recibir eventos de teclado
        container.setAttribute('tabindex', '-1');

        // Crear iframe para el juego
        const iframe = document.createElement('iframe');
        iframe.id = 'dai-game-iframe';
        iframe.src = '/game/index.html';
        iframe.style.cssText = `
            width: 100%;
            height: 100%;
            border: none;
            background: #0a0a0a;
        `;
        iframe.allow = 'autoplay';

        // NO agregar overlay que bloquee eventos - usar solo postMessage del iframe
        // El iframe ya tiene el listener para Ctrl+Alt+X y envía postMessage

        // Escuchar mensajes del iframe (postMessage)
        window.addEventListener('message', function(event) {
            // Verificar que el mensaje viene del iframe del juego
            if (event.data && event.data.type) {
                if (event.data.type === 'DAI_BOSS_MODE' && event.data.action === 'pause') {
                    pauseGame();
                } else if (event.data.type === 'DAI_TOGGLE' && event.data.action === 'toggle') {
                    // Toggle: si está activo y no pausado -> pausar, si está pausado -> reanudar, si no está activo -> iniciar
                    if (gameState.isActive && !gameState.isPaused) {
                        pauseGame();
                    } else if (gameState.isActive && gameState.isPaused) {
                        startGame();
                    } else {
                        startGame();
                    }
                }
            }
        });

        // Botón para cerrar el juego
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '✕ Cerrar Juego';
        closeBtn.style.cssText = `
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            z-index: 100000;
            transition: background 0.3s;
        `;
        closeBtn.onmouseover = () => closeBtn.style.background = 'rgba(255, 0, 0, 1)';
        closeBtn.onmouseout = () => closeBtn.style.background = 'rgba(255, 0, 0, 0.8)';
        closeBtn.onclick = () => pauseGame();

        container.appendChild(iframe);
        container.appendChild(closeBtn);
        document.body.appendChild(container);

        gameState.gameContainer = container;
        gameState.gameIframe = iframe;

        return container;
    }

    // Iniciar el juego
    function startGame() {
        // Si ya está activo y no pausado, no hacer nada
        if (gameState.isActive && !gameState.isPaused) {
            return;
        }

        // Guardar la vista actual antes de iniciar/reanudar
        saveLastView();

        const container = createGameContainer();
        
        if (gameState.isPaused) {
            // Reanudar el juego
            container.style.display = 'flex';
            gameState.isPaused = false;
            
            // Dar foco al iframe para que pueda recibir eventos de teclado
            setTimeout(() => {
                if (gameState.gameIframe) {
                    try {
                        // Intentar dar foco al iframe directamente
                        gameState.gameIframe.focus();
                        // También intentar acceder al contenido del iframe y darle foco
                        const iframeWindow = gameState.gameIframe.contentWindow;
                        if (iframeWindow) {
                            iframeWindow.focus();
                            // Dar foco al body del iframe
                            const iframeDoc = iframeWindow.document;
                            if (iframeDoc && iframeDoc.body) {
                                iframeDoc.body.focus();
                            }
                        }
                    } catch (e) {
                        // Si hay error de CORS, al menos dar foco al contenedor
                        container.focus();
                    }
                } else {
                    container.focus();
                }
            }, 200);
        } else {
            // Iniciar nuevo juego
            container.style.display = 'flex';
            gameState.isActive = true;
            gameState.isPaused = false;
            
            // Recargar el iframe para iniciar un nuevo juego
            if (gameState.gameIframe) {
                gameState.gameIframe.src = '/game/index.html';
                
                // Cuando el iframe carga, darle foco
                gameState.gameIframe.addEventListener('load', function onIframeLoad() {
                    setTimeout(() => {
                        try {
                            gameState.gameIframe.focus();
                            const iframeWindow = gameState.gameIframe.contentWindow;
                            if (iframeWindow) {
                                iframeWindow.focus();
                                const iframeDoc = iframeWindow.document;
                                if (iframeDoc && iframeDoc.body) {
                                    iframeDoc.body.focus();
                                }
                            }
                        } catch (e) {
                            // Ignorar errores de CORS
                        }
                        // Remover el listener después de usarlo
                        gameState.gameIframe.removeEventListener('load', onIframeLoad);
                    }, 300);
                }, { once: true });
            }
        }

        // Pantalla completa: ocultar toda la shell de la app (sidebar + header + main)
        const appShell = document.getElementById('melichinkul-app-shell');
        if (appShell) {
            appShell.style.display = 'none';
        }
    }

    function hideGameOverlay() {
        pauseGameTimer = null;
        if (!gameState.isActive) {
            return;
        }

        const container = gameState.gameContainer;
        if (container) {
            container.style.display = 'none';
        }

        gameState.isPaused = true;

        const appShell = document.getElementById('melichinkul-app-shell');
        if (appShell) {
            appShell.style.display = '';
        }

        restoreLastView();

        setTimeout(() => {
            document.body.focus();
            window.focus();
        }, 100);
    }

    /**
     * Cierra el overlay: pide al iframe guardar puntaje (POST ranking) y espera antes de ocultar.
     */
    function pauseGame() {
        if (!gameState.isActive) {
            return;
        }

        try {
            if (gameState.gameIframe && gameState.gameIframe.contentWindow) {
                gameState.gameIframe.contentWindow.postMessage(
                    { type: 'DAI_PARENT_CLOSING' },
                    window.location.origin
                );
            }
        } catch (err) {
            /* mismo origen */
        }

        if (pauseGameTimer) {
            clearTimeout(pauseGameTimer);
        }
        pauseGameTimer = setTimeout(hideGameOverlay, 900);
    }

    // Manejar combinación de teclas
    function handleKeyCombo(event) {
        // Verificar Ctrl + Alt (evitar conflictos con atajos del navegador)
        if (!event.ctrlKey || !event.altKey) {
            return;
        }

        // Ignorar si Shift está presionado (para evitar combinaciones mixtas)
        if (event.shiftKey) {
            return;
        }

        const key = event.key.toLowerCase();

        // Ctrl + Alt + G: Toggle juego
        if (key === GAME_TOGGLE_KEY) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            
            // Si está activo y NO pausado, pausar
            if (gameState.isActive && !gameState.isPaused) {
                pauseGame();
            } 
            // Si está activo pero pausado, reanudar
            else if (gameState.isActive && gameState.isPaused) {
                startGame();
            }
            // Si no está activo, iniciar
            else {
                startGame();
            }
            return;
        }

        // Ctrl + Alt + X: Pausar y volver a app (modo jefe)
        if (key === BOSS_MODE_KEY) {
            event.preventDefault();
            event.stopPropagation();
            
            // El modo jefe siempre debe funcionar si el juego está activo
            if (gameState.isActive) {
                pauseGame();
            }
            return;
        }
    }

    // Listener de diagnóstico - captura TODAS las teclas con Ctrl+Alt (sin logs)
    function diagnosticListener(event) {
        // Listener silencioso para diagnóstico interno
    }

    // Inicializar el sistema
    function init() {
        try {
            // Primero agregar listener de diagnóstico (captura todo)
            // Usar window para capturar eventos incluso cuando el iframe tiene foco
            window.addEventListener('keydown', diagnosticListener, true);
            document.addEventListener('keydown', diagnosticListener, true);
            
            // Luego escuchar combinaciones de teclas específicas
            // IMPORTANTE: window captura eventos incluso cuando el iframe tiene foco
            window.addEventListener('keydown', handleKeyCombo, true);
            document.addEventListener('keydown', handleKeyCombo, true);
            
            // También escuchar en el body para máxima cobertura
            if (document.body) {
                document.body.addEventListener('keydown', handleKeyCombo, true);
            } else {
                // Intentar de nuevo cuando el body esté disponible
                setTimeout(() => {
                    if (document.body) {
                        document.body.addEventListener('keydown', handleKeyCombo, true);
                    }
                }, 100);
            }

            // Guardar la vista actual al cargar
            saveLastView();
        } catch (error) {
            // Error silencioso - el juego seguirá funcionando aunque falle la inicialización
        }
    }

    // Inicializar cuando el DOM esté listo
    try {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                init();
            });
        } else {
            // Si el DOM ya está listo, ejecutar inmediatamente
            setTimeout(init, 0);
        }
    } catch (error) {
        // Intentar inicializar de todas formas después de un pequeño delay
        setTimeout(init, 100);
    }

    // Exponer funciones globales para debugging
    window.__daiGame = {
        start: startGame,
        pause: pauseGame,
        getState: () => ({ ...gameState }),
        saveView: saveLastView,
        restoreView: restoreLastView
    };
})();
