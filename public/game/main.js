// ============================================================================
// DA-I (Defensa Antiaérea I) - Juego Web 2D
// ============================================================================

// Configuración del juego
const CONFIG = {
    CANVAS_WIDTH: 1200,
    CANVAS_HEIGHT: 800,
    TOWER_Y: 720,
    TOWER_WIDTH: 50,
    TOWER_HEIGHT: 60,
    TOWER_CANNON_HEIGHT: 35,
    TOWER_POSITIONS: [200, 600, 1000], // Posiciones X de las tres torres
    PROJECTILE_SPEED: 8,
    PROJECTILE_SIZE: 4,
    PLANE_SPAWN_INTERVAL: 1200, // ms
    PLANE_MIN_SPEED: 1.5,
    PLANE_MAX_SPEED: 4,
    PLANE_MIN_SIZE: 15,
    PLANE_MAX_SIZE: 35,
    PLANE_MIN_Y: 50,
    PLANE_MAX_Y: 550,
    UFO_SPAWN_INTERVAL_MIN: 4000, // ms (4 segundos mínimo)
    UFO_SPAWN_INTERVAL_MAX: 8000, // ms (8 segundos máximo - aleatorio)
    UFO_MIN_SIZE: 8,
    UFO_MAX_SIZE: 15,
    UFO_MIN_SPEED: 1.5,
    UFO_MAX_SPEED: 3.5,
    UFO_ORBIT_RADIUS: 100,
};

// Variables globales
const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const scoreElement = document.getElementById('score');
const timerElement = document.getElementById('timer');
const playerDisplayElement = document.getElementById('playerDisplay');
const startScreen = document.getElementById('startScreen');
const gameOverScreen = document.getElementById('gameOverScreen');
const playerNameInput = document.getElementById('playerName');
const startBtn = document.getElementById('startBtn');
const restartBtn = document.getElementById('restartBtn');
const rankingBtn = document.getElementById('rankingBtn');
const rankingPreview = document.getElementById('rankingPreview');
const rankingTable = document.getElementById('rankingTable');
const finalScoreElement = document.getElementById('finalScore');
const finalTimeElement = document.getElementById('finalTime');

// Nombre del jugador actual
let currentPlayer = 'AAA';
let gameRunning = false;

// ============================================================================
// Sistema de Sonidos (Web Audio API)
// ============================================================================
let audioContext = null;

// Inicializar AudioContext
function initAudio() {
    try {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    } catch (e) {
        console.warn('Web Audio API no disponible');
    }
}

// Función para crear sonido de disparo (pitido agudo y corto)
function playShootSound() {
    if (!audioContext) return;
    
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800; // Frecuencia alta para sonido de disparo
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.1);
}

// Función para crear sonido de explosión (ruido con frecuencia descendente)
function playExplosionSound() {
    if (!audioContext) return;
    
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.setValueAtTime(300, audioContext.currentTime);
    oscillator.frequency.exponentialRampToValueAtTime(50, audioContext.currentTime + 0.2);
    oscillator.type = 'sawtooth';
    
    gainNode.gain.setValueAtTime(0.4, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.3);
}

// Función para crear sonido único de explosión de UFO (múltiples tonos)
function playUFOExplosionSound() {
    if (!audioContext) return;
    
    // Primer tono (bajo y resonante)
    const oscillator1 = audioContext.createOscillator();
    const gainNode1 = audioContext.createGain();
    oscillator1.connect(gainNode1);
    gainNode1.connect(audioContext.destination);
    
    oscillator1.frequency.setValueAtTime(150, audioContext.currentTime);
    oscillator1.frequency.exponentialRampToValueAtTime(60, audioContext.currentTime + 0.4);
    oscillator1.type = 'square';
    
    gainNode1.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode1.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4);
    
    // Segundo tono (medio-agudo que sube)
    const oscillator2 = audioContext.createOscillator();
    const gainNode2 = audioContext.createGain();
    oscillator2.connect(gainNode2);
    gainNode2.connect(audioContext.destination);
    
    oscillator2.frequency.setValueAtTime(400, audioContext.currentTime);
    oscillator2.frequency.exponentialRampToValueAtTime(800, audioContext.currentTime + 0.2);
    oscillator2.type = 'sine';
    
    gainNode2.gain.setValueAtTime(0.2, audioContext.currentTime);
    gainNode2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
    
    // Tercer tono (agudo que baja, efecto "whoosh")
    const oscillator3 = audioContext.createOscillator();
    const gainNode3 = audioContext.createGain();
    oscillator3.connect(gainNode3);
    gainNode3.connect(audioContext.destination);
    
    oscillator3.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
    oscillator3.frequency.exponentialRampToValueAtTime(200, audioContext.currentTime + 0.5);
    oscillator3.type = 'triangle';
    
    gainNode3.gain.setValueAtTime(0.15, audioContext.currentTime + 0.1);
    gainNode3.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
    
    oscillator1.start(audioContext.currentTime);
    oscillator1.stop(audioContext.currentTime + 0.4);
    
    oscillator2.start(audioContext.currentTime);
    oscillator2.stop(audioContext.currentTime + 0.3);
    
    oscillator3.start(audioContext.currentTime + 0.1);
    oscillator3.stop(audioContext.currentTime + 0.5);
}

let gameState = {
    score: 0,
    startTime: null,
    elapsedTime: 0,
    lastPlaneSpawn: 0,
    lastUFOSpawn: 0,
    nextUFOSpawnInterval: 0,
    keys: {},
    towers: [],
    planes: [],
    ufos: [],
    projectiles: [],
    explosions: [],
};

// ============================================================================
// Sistema de ranking (Laravel: sesión + CSRF; solo usuarios autenticados)
// ============================================================================
const MAX_RANKING_ENTRIES = 10;

const API_URL = window.location.origin + '/api/game';
let useOnlineRanking = true;
let cachedRanking = [];

function getCsrfToken() {
    try {
        if (window.parent !== window) {
            const meta = window.parent.document.querySelector('meta[name="csrf-token"]');
            if (meta && meta.content) {
                return meta.content;
            }
        }
    } catch (e) {
        /* mismo origen: no debería fallar */
    }
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function rankingFetchHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
    };
}

async function getRanking() {
    if (!useOnlineRanking) {
        return [];
    }
    try {
        const response = await fetch(`${API_URL}/ranking`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: rankingFetchHeaders(),
        });

        if (response.status === 401 || response.status === 403) {
            useOnlineRanking = false;
            return [];
        }

        if (!response.ok) {
            console.warn('Ranking GET HTTP', response.status);
            return [];
        }

        const data = await response.json();
        if (!data.success || !Array.isArray(data.ranking)) {
            return [];
        }
        cachedRanking = data.ranking.map(entry => ({
            name: entry.player_name,
            score: entry.score,
            time: entry.play_time,
            date: entry.created_at,
        }));
        return cachedRanking;
    } catch (e) {
        console.warn('Error al obtener ranking:', e.message);
        return [];
    }
}

async function addToRanking(name, score, time) {
    const sanitizedName = name.toUpperCase().substring(0, 10);

    if (!useOnlineRanking) {
        return -1;
    }

    try {
        const response = await fetch(`${API_URL}/ranking`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: rankingFetchHeaders(),
            body: JSON.stringify({
                player_name: sanitizedName,
                score: score,
                play_time: time,
            }),
        });

        if (response.status === 419) {
            console.warn('CSRF expirado o sesión inválida. Vuelve a cargar la aplicación.');
            return -1;
        }

        if (response.status === 401 || response.status === 403) {
            useOnlineRanking = false;
            return -1;
        }

        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            console.warn('No se pudo guardar el puntaje', response.status, err);
            return -1;
        }

        const data = await response.json();
        cachedRanking = data.ranking.map(entry => ({
            name: entry.player_name,
            score: entry.score,
            time: entry.play_time,
            date: entry.created_at,
        }));
        return typeof data.position === 'number' ? data.position - 1 : -1;
    } catch (e) {
        console.warn('Error al guardar puntaje:', e.message);
        return -1;
    }
}

// Renderizar tabla de ranking
async function renderRanking(container, highlightIndex = -1) {
    // Mostrar cargando
    container.innerHTML = '<h3>🏆 Ranking</h3><p style="color: #666; text-align: center;">Cargando...</p>';
    
    const ranking = await getRanking();
    
    if (ranking.length === 0) {
        container.innerHTML = '<h3>🏆 Ranking</h3><p style="color: #666; text-align: center;">Sin registros aún</p>';
        return;
    }
    
    const modeIndicator = useOnlineRanking ? '🌐' : '💾';
    let html = `<h3>${modeIndicator} Ranking</h3>`;
    ranking.forEach((entry, index) => {
        const isHighlighted = index === highlightIndex;
        html += `
            <div class="ranking-entry ${isHighlighted ? 'current' : ''}">
                <span class="rank">${index + 1}.</span>
                <span class="name">${entry.name}</span>
                <span class="score">${entry.score}</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ============================================================================
// Clase Torre
// ============================================================================
class Tower {
    constructor(x, y) {
        this.x = x;
        this.y = y;
        this.width = CONFIG.TOWER_WIDTH;
        this.height = CONFIG.TOWER_HEIGHT;
        this.cannonHeight = CONFIG.TOWER_CANNON_HEIGHT;
        this.color = '#00ffff';
        this.canShoot = true;
        this.shootCooldown = 0;
    }

    update(deltaTime) {
        if (this.shootCooldown > 0) {
            this.shootCooldown -= deltaTime;
        } else {
            this.canShoot = true;
        }
    }

    shoot() {
        if (this.canShoot) {
            const projectile = new Projectile(
                this.x + this.width / 2,
                this.y,
                -CONFIG.PROJECTILE_SPEED
            );
            gameState.projectiles.push(projectile);
            this.canShoot = false;
            this.shootCooldown = 300; // 300ms de cooldown
            
            // Reproducir sonido de disparo
            playShootSound();
        }
    }

    render() {
        // Base de la torre
        ctx.fillStyle = this.color;
        ctx.fillRect(
            this.x,
            this.y + this.height - 20,
            this.width,
            20
        );

        // Cuerpo de la torre
        ctx.fillRect(
            this.x + this.width / 4,
            this.y + this.height - 40,
            this.width / 2,
            20
        );

        // Cañón
        ctx.fillRect(
            this.x + this.width / 2 - 3,
            this.y - this.cannonHeight,
            6,
            this.cannonHeight + 5
        );

        // Detalle de la torre
        ctx.fillStyle = '#0088ff';
        ctx.fillRect(
            this.x + this.width / 4 + 2,
            this.y + this.height - 38,
            this.width / 2 - 4,
            16
        );
    }
}

// ============================================================================
// Clase Avión
// ============================================================================
class Plane {
    constructor() {
        this.size = CONFIG.PLANE_MIN_SIZE + Math.random() * (CONFIG.PLANE_MAX_SIZE - CONFIG.PLANE_MIN_SIZE);
        this.speed = CONFIG.PLANE_MIN_SPEED + Math.random() * (CONFIG.PLANE_MAX_SPEED - CONFIG.PLANE_MIN_SPEED);
        this.y = CONFIG.PLANE_MIN_Y + Math.random() * (CONFIG.PLANE_MAX_Y - CONFIG.PLANE_MIN_Y);
        
        // Determinar dirección y punto de inicio
        if (Math.random() < 0.5) {
            this.x = -this.size;
            this.direction = 1; // De izquierda a derecha
            this.color = '#ff6600';
        } else {
            this.x = CONFIG.CANVAS_WIDTH + this.size;
            this.direction = -1; // De derecha a izquierda
            this.color = '#ff0066';
        }
        
        // Calcular puntaje basado en velocidad y tamaño
        this.points = Math.round((this.speed * 10) + ((CONFIG.PLANE_MAX_SIZE - this.size) * 2));
    }

    update(deltaTime) {
        this.x += this.speed * this.direction * (deltaTime / 16.67);
    }

    render() {
        ctx.save();
        ctx.translate(this.x, this.y);
        
        // Si va de derecha a izquierda, rotar 180 grados
        if (this.direction === -1) {
            ctx.rotate(Math.PI);
        }

        // Fuselaje principal (cuerpo alargado)
        ctx.fillStyle = this.color;
        ctx.fillRect(-this.size * 0.5, -this.size * 0.15, this.size * 1.2, this.size * 0.3);

        // Nariz del avión (puntiaguda)
        ctx.beginPath();
        ctx.moveTo(this.size * 1.1, 0);
        ctx.lineTo(this.size * 0.7, -this.size * 0.2);
        ctx.lineTo(this.size * 0.7, this.size * 0.2);
        ctx.closePath();
        ctx.fill();

        // Ala superior
        ctx.fillStyle = this.color;
        ctx.beginPath();
        ctx.moveTo(-this.size * 0.2, -this.size * 0.15);
        ctx.lineTo(-this.size * 0.1, -this.size * 0.5);
        ctx.lineTo(this.size * 0.3, -this.size * 0.4);
        ctx.lineTo(this.size * 0.2, -this.size * 0.2);
        ctx.closePath();
        ctx.fill();

        // Ala inferior
        ctx.beginPath();
        ctx.moveTo(-this.size * 0.2, this.size * 0.15);
        ctx.lineTo(-this.size * 0.1, this.size * 0.5);
        ctx.lineTo(this.size * 0.3, this.size * 0.4);
        ctx.lineTo(this.size * 0.2, this.size * 0.2);
        ctx.closePath();
        ctx.fill();

        // Ala trasera (estabilizador vertical)
        ctx.fillStyle = '#cc5500';
        ctx.fillRect(-this.size * 0.5, -this.size * 0.2, this.size * 0.15, this.size * 0.4);

        // Ventana de la cabina
        ctx.fillStyle = '#00ccff';
        ctx.beginPath();
        ctx.arc(this.size * 0.3, 0, this.size * 0.1, 0, Math.PI * 2);
        ctx.fill();

        // Motor/detalle delantero
        ctx.fillStyle = '#ffaa00';
        ctx.beginPath();
        ctx.arc(this.size * 0.9, 0, this.size * 0.08, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    getBounds() {
        return {
            x: this.x - this.size,
            y: this.y - this.size * 0.4,
            width: this.size * 1.6,
            height: this.size * 0.8,
        };
    }

    isOffScreen() {
        return (this.direction === 1 && this.x > CONFIG.CANVAS_WIDTH + this.size) ||
               (this.direction === -1 && this.x < -this.size);
    }
}

// ============================================================================
// Clase UFO (Platillo Volador)
// ============================================================================
class UFO {
    constructor() {
        // Tamaño aleatorio
        this.size = CONFIG.UFO_MIN_SIZE + Math.random() * (CONFIG.UFO_MAX_SIZE - CONFIG.UFO_MIN_SIZE);
        // Velocidad aleatoria
        this.speed = CONFIG.UFO_MIN_SPEED + Math.random() * (CONFIG.UFO_MAX_SPEED - CONFIG.UFO_MIN_SPEED);
        
        // Aparecer en una posición aleatoria dentro de la pantalla (más centrada para ser visible)
        // Asegurar que aparezca con suficiente margen para ser visible antes de salir
        const margin = this.size * 4;
        this.x = margin + Math.random() * (CONFIG.CANVAS_WIDTH - margin * 2);
        this.y = CONFIG.PLANE_MIN_Y + Math.random() * (CONFIG.PLANE_MAX_Y - CONFIG.PLANE_MIN_Y);
        
        // Dirección aleatoria: izquierda o derecha
        this.directionX = Math.random() < 0.5 ? -1 : 1;
        
        // Movimiento vertical
        this.velocityY = (Math.random() - 0.5) * 1.5; // Velocidad vertical inicial
        this.verticalMovement = Math.random() < 0.7; // 70% de UFOs se mueven verticalmente
        
        // Comportamiento de órbita
        this.behaviorState = 'forward'; // 'forward', 'orbit'
        this.behaviorTimer = 0;
        this.orbitTarget = null; // Avión a rodear
        this.orbitAngle = 0;
        
        // Puntos más altos que los aviones normales (más puntos para UFOs más pequeños y rápidos)
        this.points = Math.round(100 + (CONFIG.UFO_MAX_SPEED - this.speed) * 10 + (CONFIG.UFO_MAX_SIZE - this.size) * 2);
        
        // Sistema de cambio de colores
        this.colorPhase = Math.random() * Math.PI * 2; // Fase inicial aleatoria
        this.pulsePhase = 0;
        
        // Colores base para rotación
        this.colors = [
            { main: '#00ff00', glow: '#88ff88', accent: '#00ffff' }, // Verde
            { main: '#ff00ff', glow: '#ff88ff', accent: '#ff00ff' }, // Magenta
            { main: '#00ffff', glow: '#88ffff', accent: '#0000ff' }, // Cyan
            { main: '#ffff00', glow: '#ffff88', accent: '#ffaa00' }, // Amarillo
            { main: '#ff0088', glow: '#ff88cc', accent: '#ff00ff' }, // Rosa
            { main: '#00ff88', glow: '#88ffcc', accent: '#00ffff' }, // Verde-cyan
        ];
        this.currentColorIndex = 0;
    }

    update(deltaTime) {
        this.pulsePhase += 0.15 * (deltaTime / 16.67); // Para efecto de pulso
        this.colorPhase += 0.05 * (deltaTime / 16.67); // Rotación de colores
        this.behaviorTimer += deltaTime;
        
        // Cambiar de color gradualmente
        if (Math.sin(this.colorPhase) > 0.98) {
            this.currentColorIndex = (this.currentColorIndex + 1) % this.colors.length;
        }

        // Cambiar comportamiento cada cierto tiempo (2-5 segundos)
        if (this.behaviorTimer > 2000 + Math.random() * 3000) {
            const rand = Math.random();
            if (rand < 0.3 && gameState.planes.length > 0 && this.behaviorState !== 'orbit') {
                // 30% chance de rodear un avión si hay aviones disponibles
                this.behaviorState = 'orbit';
                const randomPlane = gameState.planes[Math.floor(Math.random() * gameState.planes.length)];
                this.orbitTarget = randomPlane;
                this.orbitAngle = Math.atan2(this.y - randomPlane.y, this.x - randomPlane.x);
            } else if (this.behaviorState === 'orbit') {
                // Salir de la órbita
                this.behaviorState = 'forward';
                this.orbitTarget = null;
            }
            this.behaviorTimer = 0;
        }

        // Ejecutar comportamiento
        if (this.behaviorState === 'orbit' && this.orbitTarget && !this.orbitTarget.isOffScreen()) {
            // Rodear el avión objetivo
            const centerX = this.orbitTarget.x;
            const centerY = this.orbitTarget.y;
            this.orbitAngle += 0.03 * (deltaTime / 16.67); // Velocidad de órbita
            
            const radius = CONFIG.UFO_ORBIT_RADIUS;
            this.x = centerX + Math.cos(this.orbitAngle) * radius;
            this.y = centerY + Math.sin(this.orbitAngle) * radius;
        } else {
            // Movimiento normal (adelante)
            this.x += this.speed * this.directionX * (deltaTime / 16.67);
            
            // Movimiento vertical oscilante (si está habilitado)
            if (this.verticalMovement) {
                this.velocityY += (Math.random() - 0.5) * 0.1;
                this.velocityY = Math.max(-2.5, Math.min(2.5, this.velocityY)); // Limitar velocidad vertical
                this.y += this.velocityY * (deltaTime / 16.67);
                
                // Mantener dentro de límites verticales
                if (this.y < CONFIG.PLANE_MIN_Y) {
                    this.y = CONFIG.PLANE_MIN_Y;
                    this.velocityY *= -0.5;
                } else if (this.y > CONFIG.PLANE_MAX_Y) {
                    this.y = CONFIG.PLANE_MAX_Y;
                    this.velocityY *= -0.5;
                }
            }
        }
    }

    render() {
        ctx.save();
        ctx.translate(this.x, this.y);

        // Obtener color actual con interpolación suave
        const colorIndex1 = this.currentColorIndex;
        const colorIndex2 = (this.currentColorIndex + 1) % this.colors.length;
        const colorBlend = (Math.sin(this.colorPhase) + 1) / 2;
        const color1 = this.colors[colorIndex1];
        const color2 = this.colors[colorIndex2];

        // Efecto de pulso para hacerlo más visible
        const pulse = Math.sin(this.pulsePhase) * 0.4 + 0.8; // Entre 0.4 y 1.2
        const glowSize = this.size * 2.5 * pulse;

        // Resplandor externo del platillo (muy intenso)
        const outerGlow = ctx.createRadialGradient(0, 0, 0, 0, 0, glowSize);
        const r1 = parseInt(color1.glow.substring(1, 3), 16);
        const g1 = parseInt(color1.glow.substring(3, 5), 16);
        const b1 = parseInt(color1.glow.substring(5, 7), 16);
        const r2 = parseInt(color2.glow.substring(1, 3), 16);
        const g2 = parseInt(color2.glow.substring(3, 5), 16);
        const b2 = parseInt(color2.glow.substring(5, 7), 16);
        const r = Math.round(r1 + (r2 - r1) * colorBlend);
        const g = Math.round(g1 + (g2 - g1) * colorBlend);
        const b = Math.round(b1 + (b2 - b1) * colorBlend);
        
        outerGlow.addColorStop(0, `rgba(${r}, ${g}, ${b}, ${0.9 * pulse})`);
        outerGlow.addColorStop(0.3, `rgba(${r}, ${g}, ${b}, ${0.6 * pulse})`);
        outerGlow.addColorStop(0.6, `rgba(${r}, ${g}, ${b}, ${0.3 * pulse})`);
        outerGlow.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = outerGlow;
        ctx.beginPath();
        ctx.arc(0, 0, glowSize, 0, Math.PI * 2);
        ctx.fill();

        // PARTE INFERIOR DEL PLATILLO (base circular plana)
        const baseGradient = ctx.createLinearGradient(-this.size, 0, this.size, 0);
        baseGradient.addColorStop(0, color1.main);
        baseGradient.addColorStop(0.5, color2.main);
        baseGradient.addColorStop(1, color1.main);
        ctx.fillStyle = baseGradient;
        ctx.beginPath();
        ctx.ellipse(0, this.size * 0.3, this.size, this.size * 0.25, 0, 0, Math.PI * 2);
        ctx.fill();

        // Borde de la base inferior
        ctx.strokeStyle = color1.accent;
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.ellipse(0, this.size * 0.3, this.size, this.size * 0.25, 0, 0, Math.PI * 2);
        ctx.stroke();

        // CÚPULA SUPERIOR (la parte superior del platillo)
        const domeGradient = ctx.createRadialGradient(0, -this.size * 0.3, 0, 0, -this.size * 0.3, this.size * 0.9);
        domeGradient.addColorStop(0, color2.glow);
        domeGradient.addColorStop(0.4, color1.glow);
        domeGradient.addColorStop(1, color1.main);
        ctx.fillStyle = domeGradient;
        ctx.beginPath();
        ctx.ellipse(0, -this.size * 0.1, this.size * 0.9, this.size * 0.5, 0, 0, Math.PI * 2);
        ctx.fill();

        // Borde brillante de la cúpula
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.ellipse(0, -this.size * 0.1, this.size * 0.9, this.size * 0.5, 0, 0, Math.PI * 2);
        ctx.stroke();

        // VENTANA DE LA CÚPULA (parte superior central brillante)
        const windowGradient = ctx.createRadialGradient(0, -this.size * 0.3, 0, 0, -this.size * 0.3, this.size * 0.5);
        windowGradient.addColorStop(0, '#ffffff');
        windowGradient.addColorStop(0.3, color1.accent);
        windowGradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
        ctx.fillStyle = windowGradient;
        ctx.beginPath();
        ctx.ellipse(0, -this.size * 0.3, this.size * 0.5, this.size * 0.3, 0, 0, Math.PI * 2);
        ctx.fill();

        // LÍNEA CENTRAL HORIZONTAL (característica clásica del platillo)
        ctx.strokeStyle = color1.accent;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(-this.size * 0.85, 0);
        ctx.lineTo(this.size * 0.85, 0);
        ctx.stroke();

        // LUCES ALREDEDOR DEL PLATILLO (efecto parpadeante)
        const lightIntensity = Math.abs(Math.sin(this.pulsePhase * 3));
        ctx.fillStyle = `rgba(255, 255, 255, ${Math.max(0.5, lightIntensity)})`;
        
        // Luz izquierda
        ctx.beginPath();
        ctx.arc(-this.size * 0.7, this.size * 0.2, 5, 0, Math.PI * 2);
        ctx.fill();
        
        // Luz derecha
        ctx.beginPath();
        ctx.arc(this.size * 0.7, this.size * 0.2, 5, 0, Math.PI * 2);
        ctx.fill();
        
        // Luz central
        ctx.beginPath();
        ctx.arc(0, this.size * 0.35, 6, 0, Math.PI * 2);
        ctx.fill();
        
        // Luces adicionales en la cúpula
        ctx.beginPath();
        ctx.arc(-this.size * 0.4, -this.size * 0.2, 4, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(this.size * 0.4, -this.size * 0.2, 4, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    getBounds() {
        return {
            x: this.x - this.size,
            y: this.y - this.size * 0.6,
            width: this.size * 2,
            height: this.size * 1.2,
        };
    }

    isOffScreen() {
        // Eliminar cuando sale completamente de pantalla
        return this.x < -this.size * 3 || this.x > CONFIG.CANVAS_WIDTH + this.size * 3;
    }
}

// ============================================================================
// Clase Proyectil
// ============================================================================
class Projectile {
    constructor(x, y, vy) {
        this.x = x;
        this.y = y;
        this.vy = vy;
        this.size = CONFIG.PROJECTILE_SIZE;
        this.color = '#ffff00';
    }

    update(deltaTime) {
        this.y += this.vy * (deltaTime / 16.67);
    }

    render() {
        ctx.fillStyle = this.color;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
        
        // Efecto de brillo
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(this.x - 1, this.y - 1, this.size * 0.5, 0, Math.PI * 2);
        ctx.fill();
    }

    getBounds() {
        return {
            x: this.x - this.size,
            y: this.y - this.size,
            width: this.size * 2,
            height: this.size * 2,
        };
    }

    isOffScreen() {
        return this.y < -this.size || this.y > CONFIG.CANVAS_HEIGHT + this.size;
    }
}

// ============================================================================
// Clase Explosión
// ============================================================================
class Explosion {
    constructor(x, y) {
        this.x = x;
        this.y = y;
        this.size = 10;
        this.maxSize = 40;
        this.life = 1.0;
        this.decay = 0.03;
    }

    update(deltaTime) {
        this.life -= this.decay * (deltaTime / 16.67);
        this.size += 0.5 * (deltaTime / 16.67);
    }

    render() {
        const alpha = Math.min(1, this.life);
        ctx.globalAlpha = alpha;
        ctx.fillStyle = '#ff4400';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
        
        ctx.fillStyle = '#ffff00';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size * 0.6, 0, Math.PI * 2);
        ctx.fill();
        
        ctx.globalAlpha = 1;
    }

    isDead() {
        return this.life <= 0;
    }
}

// ============================================================================
// Funciones de colisión
// ============================================================================
function checkCollision(bounds1, bounds2) {
    return bounds1.x < bounds2.x + bounds2.width &&
           bounds1.x + bounds1.width > bounds2.x &&
           bounds1.y < bounds2.y + bounds2.height &&
           bounds1.y + bounds1.height > bounds2.y;
}

// ============================================================================
// Inicialización del juego
// ============================================================================
function resetGameState() {
    gameState = {
        score: 0,
        startTime: null,
        elapsedTime: 0,
        lastPlaneSpawn: 0,
        lastUFOSpawn: 0,
        nextUFOSpawnInterval: 0,
        keys: {},
        towers: [],
        planes: [],
        ufos: [],
        projectiles: [],
        explosions: [],
    };
}

function initGame() {
    // Resetear estado del juego
    resetGameState();
    
    // Inicializar sistema de audio
    initAudio();
    
    // Inicializar tiempo de juego
    gameState.startTime = Date.now();
    gameState.lastPlaneSpawn = Date.now();
    gameState.lastUFOSpawn = Date.now();
    // Intervalo aleatorio para el primer UFO
    gameState.nextUFOSpawnInterval = CONFIG.UFO_SPAWN_INTERVAL_MIN + Math.random() * (CONFIG.UFO_SPAWN_INTERVAL_MAX - CONFIG.UFO_SPAWN_INTERVAL_MIN);

    // Crear las tres torres
    CONFIG.TOWER_POSITIONS.forEach(x => {
        const tower = new Tower(x - CONFIG.TOWER_WIDTH / 2, CONFIG.TOWER_Y);
        gameState.towers.push(tower);
    });
    
    // Actualizar UI
    scoreElement.textContent = 'Puntaje: 0';
    timerElement.textContent = 'Tiempo: 00:00';
    playerDisplayElement.textContent = `Jugador: ${currentPlayer}`;
    
    gameRunning = true;
}

function setupEventListeners() {
    // Event listeners para teclado
    // Listener para modo jefe (Ctrl+Alt+X) y toggle (Ctrl+Alt+G) - debe estar antes del listener del juego
    document.addEventListener('keydown', (e) => {
        // Solo procesar si es Ctrl+Alt (sin Shift)
        if (!e.ctrlKey || !e.altKey || e.shiftKey) {
            return;
        }
        
        const key = e.key.toLowerCase();
        
        // Detectar Ctrl+Alt+X para modo jefe
        if (key === 'x') {
            e.preventDefault();
            e.stopPropagation();
            // Enviar mensaje al padre (página que contiene el iframe)
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'DAI_BOSS_MODE', action: 'pause' }, '*');
            }
            return;
        }
        
        // Detectar Ctrl+Alt+G para toggle (pausar/reanudar)
        if (key === 'g') {
            e.preventDefault();
            e.stopPropagation();
            // Enviar mensaje al padre para toggle
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'DAI_TOGGLE', action: 'toggle' }, '*');
            }
            return;
        }
    }, true); // Usar captura para interceptar antes que otros listeners

    document.addEventListener('keydown', (e) => {
        if (!gameRunning) return;
        
        gameState.keys[e.key] = true;
        
        // Disparar torres con teclas 1, 2, 3
        if (e.key === '1' && gameState.towers[0]) {
            gameState.towers[0].shoot();
        } else if (e.key === '2' && gameState.towers[1]) {
            gameState.towers[1].shoot();
        } else if (e.key === '3' && gameState.towers[2]) {
            gameState.towers[2].shoot();
        }
        
        // Tecla Escape para terminar juego (debug/prueba)
        if (e.key === 'Escape') {
            endGame();
        }
    });

    document.addEventListener('keyup', (e) => {
        gameState.keys[e.key] = false;
    });
    
    // Botón de inicio
    startBtn.addEventListener('click', () => {
        const name = playerNameInput.value.trim() || 'AAA';
        currentPlayer = name.toUpperCase().substring(0, 10);
        startScreen.classList.add('hidden');
        initGame();
    });
    
    // Permitir iniciar con Enter en el input
    playerNameInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            startBtn.click();
        }
    });
    
    // Botón de reinicio
    restartBtn.addEventListener('click', () => {
        gameOverScreen.classList.add('hidden');
        startScreen.classList.remove('hidden');
        renderRanking(rankingPreview);
    });
    
    // Botón de ranking
    rankingBtn.addEventListener('click', () => {
        if (gameRunning) {
            endGame();
        }
    });
}

// Función para terminar el juego
async function endGame() {
    gameRunning = false;
    
    // Mostrar pantalla de Game Over
    finalScoreElement.textContent = `Puntaje: ${gameState.score}`;
    finalTimeElement.textContent = `Tiempo: ${formatTime(gameState.elapsedTime)}`;
    gameOverScreen.classList.remove('hidden');
    
    // Mostrar "Guardando..." mientras se guarda
    rankingTable.innerHTML = '<h3>🏆 Ranking</h3><p style="color: #666; text-align: center;">Guardando...</p>';
    
    // Guardar en ranking (puede ser async)
    const rankPosition = await addToRanking(currentPlayer, gameState.score, gameState.elapsedTime);
    
    // Actualizar tabla con posición resaltada
    await renderRanking(rankingTable, rankPosition);
}

// Inicialización principal
function init() {
    // Mostrar ranking en pantalla de inicio
    renderRanking(rankingPreview);
    
    // Configurar event listeners
    setupEventListeners();
    
    // Enfocar input de nombre
    playerNameInput.focus();
}

// ============================================================================
// Formatear tiempo para el cronómetro
// ============================================================================
function formatTime(milliseconds) {
    const totalSeconds = Math.floor(milliseconds / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

// ============================================================================
// Actualización del juego
// ============================================================================
function update(deltaTime) {
    // Actualizar tiempo de juego
    if (gameState.startTime) {
        gameState.elapsedTime = Date.now() - gameState.startTime;
        timerElement.textContent = `Tiempo: ${formatTime(gameState.elapsedTime)}`;
    }

    // Actualizar torres
    gameState.towers.forEach(tower => tower.update(deltaTime));

    // Generar aviones
    const now = Date.now();
    if (now - gameState.lastPlaneSpawn > CONFIG.PLANE_SPAWN_INTERVAL) {
        gameState.planes.push(new Plane());
        gameState.lastPlaneSpawn = now;
    }

    // Generar UFOs automáticamente (intervalo aleatorio)
    if (now - gameState.lastUFOSpawn > gameState.nextUFOSpawnInterval) {
        gameState.ufos.push(new UFO());
        gameState.lastUFOSpawn = now;
        // Calcular próximo intervalo aleatorio
        gameState.nextUFOSpawnInterval = CONFIG.UFO_SPAWN_INTERVAL_MIN + Math.random() * (CONFIG.UFO_SPAWN_INTERVAL_MAX - CONFIG.UFO_SPAWN_INTERVAL_MIN);
    }

    // Actualizar aviones
    gameState.planes.forEach(plane => plane.update(deltaTime));
    
    // Eliminar aviones fuera de pantalla
    gameState.planes = gameState.planes.filter(plane => !plane.isOffScreen());

    // Actualizar UFOs
    gameState.ufos.forEach(ufo => ufo.update(deltaTime));
    
    // Eliminar UFOs fuera de pantalla
    gameState.ufos = gameState.ufos.filter(ufo => !ufo.isOffScreen());

    // Actualizar proyectiles
    gameState.projectiles.forEach(projectile => projectile.update(deltaTime));
    
    // Eliminar proyectiles fuera de pantalla
    gameState.projectiles = gameState.projectiles.filter(projectile => !projectile.isOffScreen());

    // Detectar colisiones con aviones
    for (let i = gameState.projectiles.length - 1; i >= 0; i--) {
        const projectile = gameState.projectiles[i];
        const projBounds = projectile.getBounds();

        for (let j = gameState.planes.length - 1; j >= 0; j--) {
            const plane = gameState.planes[j];
            const planeBounds = plane.getBounds();

            if (checkCollision(projBounds, planeBounds)) {
                // Colisión detectada
                const explosion = new Explosion(plane.x, plane.y);
                gameState.explosions.push(explosion);
                
                // Reproducir sonido de explosión normal
                playExplosionSound();
                
                gameState.score += plane.points;
                scoreElement.textContent = `Puntaje: ${gameState.score}`;

                // Eliminar proyectil y avión
                gameState.projectiles.splice(i, 1);
                gameState.planes.splice(j, 1);
                break;
            }
        }
    }

    // Detectar colisiones con UFOs
    for (let i = gameState.projectiles.length - 1; i >= 0; i--) {
        const projectile = gameState.projectiles[i];
        const projBounds = projectile.getBounds();

        for (let j = gameState.ufos.length - 1; j >= 0; j--) {
            const ufo = gameState.ufos[j];
            const ufoBounds = ufo.getBounds();

            if (checkCollision(projBounds, ufoBounds)) {
                // Colisión detectada con UFO
                const explosion = new Explosion(ufo.x, ufo.y);
                gameState.explosions.push(explosion);
                
                // Reproducir sonido único de explosión de UFO
                playUFOExplosionSound();
                
                gameState.score += ufo.points;
                scoreElement.textContent = `Puntaje: ${gameState.score}`;

                // Eliminar proyectil y UFO
                gameState.projectiles.splice(i, 1);
                gameState.ufos.splice(j, 1);
                break;
            }
        }
    }

    // Actualizar explosiones
    gameState.explosions.forEach(explosion => explosion.update(deltaTime));
    
    // Eliminar explosiones muertas
    gameState.explosions = gameState.explosions.filter(explosion => !explosion.isDead());
}

// ============================================================================
// Renderizado
// ============================================================================
function render() {
    // Limpiar canvas
    ctx.fillStyle = '#111';
    ctx.fillRect(0, 0, CONFIG.CANVAS_WIDTH, CONFIG.CANVAS_HEIGHT);

    // Dibujar rejilla sutil de fondo (efecto retro)
    ctx.strokeStyle = '#222';
    ctx.lineWidth = 1;
    for (let i = 0; i < CONFIG.CANVAS_WIDTH; i += 60) {
        ctx.beginPath();
        ctx.moveTo(i, 0);
        ctx.lineTo(i, CONFIG.CANVAS_HEIGHT);
        ctx.stroke();
    }
    for (let i = 0; i < CONFIG.CANVAS_HEIGHT; i += 60) {
        ctx.beginPath();
        ctx.moveTo(0, i);
        ctx.lineTo(CONFIG.CANVAS_WIDTH, i);
        ctx.stroke();
    }

    // Renderizar explosiones (primero para que queden debajo)
    gameState.explosions.forEach(explosion => explosion.render());

    // Renderizar aviones
    gameState.planes.forEach(plane => plane.render());

    // Renderizar UFOs
    if (gameState.ufos.length > 0) {
        gameState.ufos.forEach(ufo => {
            if (ufo && typeof ufo.render === 'function') {
                ufo.render();
            }
        });
    }

    // Renderizar proyectiles
    gameState.projectiles.forEach(projectile => projectile.render());

    // Renderizar torres
    gameState.towers.forEach(tower => tower.render());
}

// ============================================================================
// Game Loop
// ============================================================================
let lastTime = performance.now();

function gameLoop(currentTime) {
    const deltaTime = currentTime - lastTime;
    lastTime = currentTime;

    if (gameRunning) {
        update(deltaTime);
        render();
    }

    requestAnimationFrame(gameLoop);
}

// Iniciar aplicación
init();
gameLoop(performance.now());
