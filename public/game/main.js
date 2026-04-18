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
    /** Por debajo de esta Y el avión viola el espacio aéreo (pérdida de vida). */
    GROUND_THREAT_Y: 605,
    LIVES_START: 3,
    TOWER_AMMO_MAX: 8,
    TOWER_RELOAD_MS: 1600,
    COMBO_DECAY_MS: 2600,
    WAVE_INTERVAL_MS: 40000,
    PLANE_SPAWN_MIN_MS: 380,
    UFO_DIVE_CHANCE: 0.18,
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
const waveDisplayEl = document.getElementById('waveDisplay');
const livesDisplayEl = document.getElementById('livesDisplay');
const comboDisplayEl = document.getElementById('comboDisplay');
const ammoHudEl = document.getElementById('ammoHud');
const dailySeedHintEl = document.getElementById('dailySeedHint');
const achievementsBoxEl = document.getElementById('achievementsBox');
const highContrastToggle = document.getElementById('highContrastToggle');
const muteAudioToggle = document.getElementById('muteAudioToggle');
const gameRootEl = document.getElementById('gameRoot');

// Nombre del jugador actual
let currentPlayer = 'AAA';
let gameRunning = false;

const ACH_STORAGE_KEY = 'dai_melichinkul_ach_v1';
const PB_STORAGE_KEY = 'dai_melichinkul_pb_v1';

/** RNG con semilla diaria (misma secuencia para todos en un día). */
function createDailyRng() {
    const d = new Date();
    const seedStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let h = 2166136261;
    for (let i = 0; i < seedStr.length; i++) {
        h = Math.imul(h ^ seedStr.charCodeAt(i), 16777619);
    }
    let state = h >>> 0;
    return () => {
        state = (Math.imul(state, 1664525) + 1013904223) >>> 0;
        return (state & 0xffffff) / 0x1000000;
    };
}

let gameRandom = Math.random;
function rng() {
    return gameRandom();
}

let soundMuted = false;
let prefersReducedMotion = false;
try {
    prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
} catch (e) {
    /* noop */
}

let ambientOscillator = null;
let ambientGainNode = null;

function stopAmbientDrone() {
    try {
        if (ambientOscillator) {
            ambientOscillator.stop();
        }
    } catch (e) {
        /* noop */
    }
    ambientOscillator = null;
    ambientGainNode = null;
}

function startAmbientDrone() {
    if (!audioContext || soundMuted || prefersReducedMotion) return;
    stopAmbientDrone();
    ambientOscillator = audioContext.createOscillator();
    ambientGainNode = audioContext.createGain();
    ambientOscillator.type = 'triangle';
    ambientOscillator.frequency.value = 52;
    ambientGainNode.gain.value = 0.012;
    ambientOscillator.connect(ambientGainNode);
    ambientGainNode.connect(audioContext.destination);
    ambientOscillator.start();
}

function setMuted(muted) {
    soundMuted = muted;
    if (ambientGainNode && audioContext) {
        const t = audioContext.currentTime;
        ambientGainNode.gain.cancelScheduledValues(t);
        ambientGainNode.gain.linearRampToValueAtTime(muted ? 0 : 0.012, t + 0.08);
    } else if (!muted) {
        startAmbientDrone();
    }
}

function loadAchievements() {
    try {
        const raw = localStorage.getItem(ACH_STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return [];
    }
}

function saveAchievements(ids) {
    try {
        localStorage.setItem(ACH_STORAGE_KEY, JSON.stringify(ids));
    } catch (e) {
        /* noop */
    }
}

function unlockAchievement(id) {
    const a = loadAchievements();
    if (!a.includes(id)) {
        a.push(id);
        saveAchievements(a);
        return true;
    }
    return false;
}

function getPersonalBestRecord() {
    try {
        const raw = localStorage.getItem(PB_STORAGE_KEY);
        if (!raw) return null;
        const o = JSON.parse(raw);
        if (typeof o.score === 'number') return o;
    } catch (e) {
        /* noop */
    }
    return null;
}

function savePersonalBestIfBetter(score, name) {
    const prev = getPersonalBestRecord();
    const improved = prev ? score > prev.score : score > 0;
    if (!improved) {
        return false;
    }
    try {
        localStorage.setItem(PB_STORAGE_KEY, JSON.stringify({
            score,
            name: (name || '').toUpperCase().substring(0, 10),
            at: new Date().toISOString(),
        }));
    } catch (e) {
        /* noop */
    }
    return true;
}

function getDailySeedLabel() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function comboMultiplierFromChain(chain) {
    return 1 + 0.12 * Math.min(Math.max(0, chain), 14);
}

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
    if (soundMuted || !audioContext) return;
    
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
    if (soundMuted || !audioContext) return;
    
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
    if (soundMuted || !audioContext) return;
    
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

function playBreachSound() {
    if (soundMuted || !audioContext) return;
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    oscillator.type = 'sawtooth';
    oscillator.frequency.setValueAtTime(120, audioContext.currentTime);
    oscillator.frequency.exponentialRampToValueAtTime(40, audioContext.currentTime + 0.35);
    gainNode.gain.setValueAtTime(0.35, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4);
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.4);
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

function updateAmmoHud() {
    if (!ammoHudEl || !gameState.towers) return;
    const parts = gameState.towers.map(t => t.ammo);
    ammoHudEl.textContent = `Munición: ${parts.join('·')}`;
}

function updateLivesHud() {
    if (!livesDisplayEl) return;
    const n = Math.max(0, gameState.lives | 0);
    livesDisplayEl.textContent = '❤'.repeat(n) + (n === 0 ? ' —' : '');
}

function updateWaveHud() {
    if (!waveDisplayEl) return;
    waveDisplayEl.textContent = `Oleada: ${gameState.wave | 0}`;
}

function updateComboHud() {
    if (!comboDisplayEl) return;
    const m = comboMultiplierFromChain(gameState.comboChain);
    comboDisplayEl.textContent = gameState.comboChain > 0
        ? `Combo ×${m.toFixed(2)}`
        : 'Combo ×1.00';
}

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
    
    const pb = getPersonalBestRecord();
    const pbScore = pb && typeof pb.score === 'number' ? pb.score : -1;
    const pbName = pb && pb.name ? String(pb.name).toUpperCase() : '';

    const modeIndicator = useOnlineRanking ? '🌐' : '💾';
    let html = `<h3>${modeIndicator} Ranking</h3>`;
    ranking.forEach((entry, index) => {
        const isHighlighted = index === highlightIndex;
        const isPb = pbScore >= 0
            && entry.score === pbScore
            && String(entry.name).toUpperCase() === pbName;
        const classes = ['ranking-entry'];
        if (isHighlighted) classes.push('current');
        if (isPb) classes.push('personal-best');
        html += `
            <div class="${classes.join(' ')}">
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
        this.color = '#22d3ee';
        this.canShoot = true;
        this.shootCooldown = 0;
        this.ammo = CONFIG.TOWER_AMMO_MAX;
        this.reloadTimer = 0;
        this.muzzleFlash = 0;
    }

    update(deltaTime) {
        if (this.reloadTimer > 0) {
            this.reloadTimer -= deltaTime;
            if (this.reloadTimer <= 0) {
                this.ammo = CONFIG.TOWER_AMMO_MAX;
                this.canShoot = true;
                this.shootCooldown = 0;
            }
            return;
        }
        if (this.shootCooldown > 0) {
            this.shootCooldown -= deltaTime;
        } else if (this.ammo > 0) {
            this.canShoot = true;
        }
        if (this.muzzleFlash > 0) {
            this.muzzleFlash -= deltaTime;
        }
    }

    shoot() {
        if (this.reloadTimer > 0 || this.ammo <= 0) {
            return;
        }
        if (this.canShoot) {
            const projectile = new Projectile(
                this.x + this.width / 2,
                this.y,
                -CONFIG.PROJECTILE_SPEED
            );
            gameState.projectiles.push(projectile);
            this.ammo -= 1;
            this.muzzleFlash = 90;
            if (this.ammo <= 0) {
                this.reloadTimer = CONFIG.TOWER_RELOAD_MS;
                this.canShoot = false;
            } else {
                this.canShoot = false;
                this.shootCooldown = 280;
            }
            playShootSound();
        }
    }

    render() {
        ctx.fillStyle = 'rgba(0,0,0,0.35)';
        ctx.fillRect(
            this.x + 4,
            this.y + this.height - 14,
            this.width,
            22
        );

        ctx.fillStyle = this.color;
        ctx.fillRect(
            this.x,
            this.y + this.height - 20,
            this.width,
            20
        );

        ctx.fillRect(
            this.x + this.width / 4,
            this.y + this.height - 40,
            this.width / 2,
            20
        );

        if (this.muzzleFlash > 0) {
            const f = this.muzzleFlash / 90;
            ctx.fillStyle = `rgba(255, 255, 200, ${0.55 * f})`;
            ctx.beginPath();
            ctx.arc(this.x + this.width / 2, this.y - this.cannonHeight + 4, 14 * f, 0, Math.PI * 2);
            ctx.fill();
        }

        ctx.fillStyle = '#0369a1';
        ctx.fillRect(
            this.x + this.width / 2 - 3,
            this.y - this.cannonHeight,
            6,
            this.cannonHeight + 5
        );

        ctx.fillStyle = '#0ea5e9';
        ctx.fillRect(
            this.x + this.width / 4 + 2,
            this.y + this.height - 38,
            this.width / 2 - 4,
            16
        );

        if (this.reloadTimer > 0) {
            ctx.fillStyle = 'rgba(251, 191, 36, 0.85)';
            ctx.font = '10px Share Tech Mono, monospace';
            ctx.textAlign = 'center';
            ctx.fillText('RECARGA', this.x + this.width / 2, this.y - this.cannonHeight - 6);
            ctx.textAlign = 'left';
        }
    }
}

// ============================================================================
// Clase Avión
// ============================================================================
class Plane {
    constructor() {
        const t = rng();
        if (t < 0.55) {
            this.planeType = 'scout';
        } else if (t < 0.82) {
            this.planeType = 'interceptor';
        } else {
            this.planeType = 'bomber';
        }

        const sizeMul = this.planeType === 'scout' ? 0.85 : this.planeType === 'bomber' ? 1.15 : 1;
        const speedMul = this.planeType === 'scout' ? 1.25 : this.planeType === 'bomber' ? 0.75 : 1.08;

        this.size = (CONFIG.PLANE_MIN_SIZE + rng() * (CONFIG.PLANE_MAX_SIZE - CONFIG.PLANE_MIN_SIZE)) * sizeMul;
        this.speed = (CONFIG.PLANE_MIN_SPEED + rng() * (CONFIG.PLANE_MAX_SPEED - CONFIG.PLANE_MIN_SPEED)) * speedMul;
        this.y = CONFIG.PLANE_MIN_Y + rng() * (CONFIG.PLANE_MAX_Y - CONFIG.PLANE_MIN_Y);

        if (rng() < 0.5) {
            this.x = -this.size;
            this.direction = 1;
            this.color = this.planeType === 'bomber' ? '#b45309' : '#ea580c';
        } else {
            this.x = CONFIG.CANVAS_WIDTH + this.size;
            this.direction = -1;
            this.color = this.planeType === 'bomber' ? '#be185d' : '#db2777';
        }

        let basePts = (this.speed * 10) + ((CONFIG.PLANE_MAX_SIZE - this.size) * 2);
        if (this.planeType === 'interceptor') basePts *= 1.25;
        if (this.planeType === 'bomber') basePts *= 1.1;
        this.points = Math.max(5, Math.round(basePts));
        this.wobblePhase = rng() * Math.PI * 2;
    }

    update(deltaTime) {
        const dt = deltaTime / 16.67;
        this.wobblePhase += 0.04 * dt;
        let dy = 0;
        if (this.planeType === 'bomber') {
            dy = Math.sin(this.wobblePhase) * 0.35 * dt;
        } else if (this.planeType === 'interceptor') {
            dy = Math.sin(this.wobblePhase * 1.4) * 0.22 * dt;
        }
        this.x += this.speed * this.direction * dt;
        this.y += dy;
        this.y = Math.max(CONFIG.PLANE_MIN_Y, Math.min(CONFIG.PLANE_MAX_Y + 30, this.y));
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
        this.ufoClass = rng() < 0.22 ? 'stalker' : rng() < 0.12 ? 'racer' : 'standard';

        let sizeFactor = 1;
        let speedFactor = 1;
        if (this.ufoClass === 'racer') {
            speedFactor = 1.35;
            sizeFactor = 0.88;
        } else if (this.ufoClass === 'stalker') {
            speedFactor = 0.82;
            sizeFactor = 1.08;
        }

        this.size = (CONFIG.UFO_MIN_SIZE + rng() * (CONFIG.UFO_MAX_SIZE - CONFIG.UFO_MIN_SIZE)) * sizeFactor;
        this.speed = (CONFIG.UFO_MIN_SPEED + rng() * (CONFIG.UFO_MAX_SPEED - CONFIG.UFO_MIN_SPEED)) * speedFactor;

        const margin = this.size * 4;
        this.x = margin + rng() * (CONFIG.CANVAS_WIDTH - margin * 2);
        this.y = CONFIG.PLANE_MIN_Y + rng() * (CONFIG.PLANE_MAX_Y - CONFIG.PLANE_MIN_Y);

        this.directionX = rng() < 0.5 ? -1 : 1;

        this.velocityY = (rng() - 0.5) * 1.5;
        this.verticalMovement = rng() < 0.72;

        this.behaviorState = 'forward';
        this.behaviorTimer = 0;
        this.orbitTarget = null;
        this.orbitAngle = 0;

        let pts = 100 + (CONFIG.UFO_MAX_SPEED - this.speed) * 10 + (CONFIG.UFO_MAX_SIZE - this.size) * 2;
        if (this.ufoClass === 'racer') pts *= 1.2;
        if (this.ufoClass === 'stalker') pts *= 1.08;
        this.points = Math.round(pts);

        this.colorPhase = rng() * Math.PI * 2;
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
        if (this.behaviorState !== 'dive') {
            this.behaviorTimer += deltaTime;
        }

        // Cambiar de color gradualmente
        if (Math.sin(this.colorPhase) > 0.98) {
            this.currentColorIndex = (this.currentColorIndex + 1) % this.colors.length;
        }

        if (this.behaviorState !== 'dive' && this.behaviorTimer > 2000 + rng() * 3000) {
            const rand = rng();
            if (this.behaviorState === 'orbit') {
                this.behaviorState = 'forward';
                this.orbitTarget = null;
            } else if (rand < CONFIG.UFO_DIVE_CHANCE && this.behaviorState !== 'dive') {
                this.behaviorState = 'dive';
                this.orbitTarget = null;
            } else if (rand < 0.3 && gameState.planes.length > 0) {
                this.behaviorState = 'orbit';
                const randomPlane = gameState.planes[Math.floor(rng() * gameState.planes.length)];
                this.orbitTarget = randomPlane;
                this.orbitAngle = Math.atan2(this.y - randomPlane.y, this.x - randomPlane.x);
            }
            this.behaviorTimer = 0;
        }

        const dt = deltaTime / 16.67;

        if (this.behaviorState === 'orbit' && this.orbitTarget && !this.orbitTarget.isOffScreen()) {
            const centerX = this.orbitTarget.x;
            const centerY = this.orbitTarget.y;
            this.orbitAngle += 0.03 * dt;

            const radius = CONFIG.UFO_ORBIT_RADIUS;
            this.x = centerX + Math.cos(this.orbitAngle) * radius;
            this.y = centerY + Math.sin(this.orbitAngle) * radius;
        } else if (this.behaviorState === 'dive') {
            this.x += this.speed * this.directionX * 0.6 * dt;
            this.y += this.speed * 1.35 * dt;
            if (this.y > CONFIG.PLANE_MAX_Y + 40) {
                this.behaviorState = 'forward';
                this.velocityY = -1.2;
                this.y = CONFIG.PLANE_MAX_Y + 20;
                this.behaviorTimer = 0;
            }
        } else {
            this.x += this.speed * this.directionX * dt;

            if (this.verticalMovement) {
                this.velocityY += (rng() - 0.5) * 0.1;
                this.velocityY = Math.max(-2.5, Math.min(2.5, this.velocityY));
                this.y += this.velocityY * dt;

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
        this.color = '#fbbf24';
        this.trail = [];
        const trailLen = prefersReducedMotion ? 3 : 6;
        for (let i = 0; i < trailLen; i++) {
            this.trail.push({ x: this.x, y: this.y + i * 4 });
        }
    }

    update(deltaTime) {
        const dt = deltaTime / 16.67;
        this.y += this.vy * dt;
        this.trail.unshift({ x: this.x, y: this.y });
        const maxT = prefersReducedMotion ? 4 : 8;
        while (this.trail.length > maxT) {
            this.trail.pop();
        }
    }

    render() {
        if (!prefersReducedMotion) {
            for (let i = this.trail.length - 1; i >= 0; i--) {
                const p = this.trail[i];
                const a = 0.15 + (i / this.trail.length) * 0.35;
                ctx.fillStyle = `rgba(251, 191, 36, ${a})`;
                ctx.beginPath();
                ctx.arc(p.x, p.y, this.size * (0.5 + i * 0.08), 0, Math.PI * 2);
                ctx.fill();
            }
        }
        ctx.fillStyle = this.color;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#fffbeb';
        ctx.beginPath();
        ctx.arc(this.x - 1, this.y - 1, this.size * 0.45, 0, Math.PI * 2);
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
    constructor(x, y, isUfo = false) {
        this.x = x;
        this.y = y;
        this.size = 10;
        this.maxSize = prefersReducedMotion ? 28 : 42;
        this.life = 1.0;
        this.decay = prefersReducedMotion ? 0.045 : 0.028;
        this.isUfo = isUfo;
        this.particles = [];
        const n = prefersReducedMotion ? 8 : (isUfo ? 18 : 14);
        for (let i = 0; i < n; i++) {
            const a = (Math.PI * 2 * i) / n + rng() * 0.4;
            const sp = (isUfo ? 2.8 : 2) + rng() * 4;
            this.particles.push({
                px: 0,
                py: 0,
                vx: Math.cos(a) * sp,
                vy: Math.sin(a) * sp,
                life: 0.9 + rng() * 0.4,
                hue: isUfo ? 270 + rng() * 60 : 15 + rng() * 35,
            });
        }
    }

    update(deltaTime) {
        const dt = deltaTime / 16.67;
        this.life -= this.decay * dt;
        this.size += 0.55 * dt;
        this.particles.forEach(p => {
            p.px += p.vx * dt;
            p.py += p.vy * dt;
            p.vy += 0.04 * dt;
            p.life -= 0.035 * dt;
        });
    }

    render() {
        const alpha = Math.min(1, this.life);
        ctx.globalAlpha = alpha;
        ctx.fillStyle = this.isUfo ? 'rgba(168, 85, 247, 0.9)' : 'rgba(234, 88, 12, 0.9)';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = this.isUfo ? 'rgba(216, 180, 254, 0.75)' : 'rgba(253, 224, 71, 0.75)';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size * 0.55, 0, Math.PI * 2);
        ctx.fill();

        if (!prefersReducedMotion) {
            this.particles.forEach(p => {
                if (p.life <= 0) return;
                ctx.globalAlpha = alpha * Math.min(1, p.life);
                ctx.fillStyle = `hsla(${p.hue}, 90%, 55%, ${p.life})`;
                ctx.beginPath();
                ctx.arc(this.x + p.px, this.y + p.py, 3 + p.life * 2, 0, Math.PI * 2);
                ctx.fill();
            });
        }
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
        lives: CONFIG.LIVES_START,
        wave: 1,
        waveTimerStart: null,
        paused: false,
        parallaxX: 0,
        clouds: [],
        comboChain: 0,
        comboTimer: 0,
        planesDestroyed: 0,
        ufosDestroyed: 0,
    };
}

function initGame() {
    gameRandom = createDailyRng();
    resetGameState();

    initAudio();
    try {
        if (audioContext && audioContext.state === 'suspended') {
            audioContext.resume();
        }
    } catch (e) {
        /* noop */
    }
    stopAmbientDrone();
    startAmbientDrone();

    gameState.startTime = Date.now();
    gameState.lastPlaneSpawn = Date.now();
    gameState.lastUFOSpawn = Date.now();
    gameState.waveTimerStart = Date.now();
    gameState.nextUFOSpawnInterval = CONFIG.UFO_SPAWN_INTERVAL_MIN
        + rng() * (CONFIG.UFO_SPAWN_INTERVAL_MAX - CONFIG.UFO_SPAWN_INTERVAL_MIN);

    for (let c = 0; c < 8; c++) {
        gameState.clouds.push({
            x: rng() * CONFIG.CANVAS_WIDTH,
            y: 30 + rng() * 220,
            w: 70 + rng() * 120,
            h: 22 + rng() * 16,
            speed: 0.03 + rng() * 0.07,
            layer: rng() < 0.45 ? 0 : 1,
        });
    }

    CONFIG.TOWER_POSITIONS.forEach(x => {
        const tower = new Tower(x - CONFIG.TOWER_WIDTH / 2, CONFIG.TOWER_Y);
        gameState.towers.push(tower);
    });

    scoreElement.textContent = 'Puntaje: 0';
    timerElement.textContent = 'Tiempo: 00:00';
    playerDisplayElement.textContent = `Jugador: ${currentPlayer}`;
    updateLivesHud();
    updateWaveHud();
    updateComboHud();
    updateAmmoHud();
    if (dailySeedHintEl) {
        dailySeedHintEl.textContent = `Semilla del día: ${getDailySeedLabel()} · misma oleada para todos`;
    }

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

        if (e.key === 'p' || e.key === 'P') {
            e.preventDefault();
            gameState.paused = !gameState.paused;
            return;
        }

        if (gameState.paused) return;

        gameState.keys[e.key] = true;

        if (e.key === '1' && gameState.towers[0]) {
            gameState.towers[0].shoot();
            updateAmmoHud();
        } else if (e.key === '2' && gameState.towers[1]) {
            gameState.towers[1].shoot();
            updateAmmoHud();
        } else if (e.key === '3' && gameState.towers[2]) {
            gameState.towers[2].shoot();
            updateAmmoHud();
        }

        if (e.key === 'Escape') {
            endGame();
        }
    });

    document.addEventListener('keyup', (e) => {
        gameState.keys[e.key] = false;
    });
    
    if (highContrastToggle) {
        try {
            highContrastToggle.checked = sessionStorage.getItem('dai_high_contrast') === '1';
        } catch (err) {
            highContrastToggle.checked = false;
        }
        document.body.classList.toggle('dai-high-contrast', highContrastToggle.checked);
        highContrastToggle.addEventListener('change', () => {
            document.body.classList.toggle('dai-high-contrast', highContrastToggle.checked);
            try {
                sessionStorage.setItem('dai_high_contrast', highContrastToggle.checked ? '1' : '0');
            } catch (err) { /* noop */ }
        });
    }

    if (muteAudioToggle) {
        try {
            muteAudioToggle.checked = localStorage.getItem('dai_mute') === '1';
        } catch (err) {
            muteAudioToggle.checked = false;
        }
        setMuted(muteAudioToggle.checked);
        muteAudioToggle.addEventListener('change', () => {
            setMuted(muteAudioToggle.checked);
            try {
                localStorage.setItem('dai_mute', muteAudioToggle.checked ? '1' : '0');
            } catch (err) { /* noop */ }
        });
    }

    function resizeGameCanvas() {
        if (!canvas) return;
        const maxW = Math.min(1200, (window.innerWidth || 1200) - 20);
        const scale = maxW / CONFIG.CANVAS_WIDTH;
        canvas.style.width = `${CONFIG.CANVAS_WIDTH * scale}px`;
        canvas.style.height = `${CONFIG.CANVAS_HEIGHT * scale}px`;
    }
    resizeGameCanvas();
    window.addEventListener('resize', resizeGameCanvas);

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

function collectSessionAchievements() {
    const earnedTitles = [];
    const totalKills = gameState.planesDestroyed + gameState.ufosDestroyed;
    if (totalKills >= 1 && unlockAchievement('dai_first_blood')) {
        earnedTitles.push('Primera baja');
    }
    if (gameState.ufosDestroyed >= 5 && unlockAchievement('dai_ufo_hunter')) {
        earnedTitles.push('Cazador de OVNIs');
    }
    if (gameState.elapsedTime >= 180000 && unlockAchievement('dai_survivor')) {
        earnedTitles.push('Superviviente (3 min)');
    }
    if (gameState.score >= 500 && unlockAchievement('dai_scorer')) {
        earnedTitles.push('Artillero (500+)');
    }
    if (gameState.wave >= 5 && unlockAchievement('dai_wave5')) {
        earnedTitles.push('Oleada 5');
    }
    return earnedTitles;
}

// Función para terminar el juego
async function endGame() {
    gameRunning = false;
    gameState.paused = false;
    stopAmbientDrone();

    const beatPb = savePersonalBestIfBetter(gameState.score, currentPlayer);
    const ach = collectSessionAchievements();

    finalScoreElement.textContent = `Puntaje: ${gameState.score}`;
    finalTimeElement.textContent = `Tiempo: ${formatTime(gameState.elapsedTime)}`;
    if (achievementsBoxEl) {
        let achHtml = '';
        if (ach.length > 0) {
            achHtml = '<h4>Logros desbloqueados</h4>' + ach.map(a => `<div>★ ${a}</div>`).join('');
        }
        if (beatPb) {
            achHtml += '<div style="margin-top:8px;color:#fbbf24;">¡Nueva mejor marca personal!</div>';
        }
        achievementsBoxEl.innerHTML = achHtml || '<span style="color:#64748b;">Sigue jugando para desbloquear logros locales.</span>';
    }

    gameOverScreen.classList.remove('hidden');

    rankingTable.innerHTML = '<h3>🏆 Ranking</h3><p style="color: #666; text-align: center;">Guardando...</p>';

    const rankPosition = await addToRanking(currentPlayer, gameState.score, gameState.elapsedTime);

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
    if (gameState.paused) return;

    if (gameState.startTime) {
        gameState.elapsedTime = Date.now() - gameState.startTime;
        timerElement.textContent = `Tiempo: ${formatTime(gameState.elapsedTime)}`;
    }

    if (gameState.comboTimer > 0) {
        gameState.comboTimer -= deltaTime;
        if (gameState.comboTimer <= 0) {
            gameState.comboChain = 0;
            updateComboHud();
        }
    }

    if (gameState.waveTimerStart && Date.now() - gameState.waveTimerStart > CONFIG.WAVE_INTERVAL_MS) {
        gameState.wave += 1;
        gameState.waveTimerStart = Date.now();
        updateWaveHud();
    }

    gameState.parallaxX += deltaTime * 0.014;
    gameState.clouds.forEach(c => {
        const mul = c.layer === 0 ? 0.12 : 0.22;
        c.x += c.speed * deltaTime * mul;
        if (c.x > CONFIG.CANVAS_WIDTH + c.w) {
            c.x = -c.w - rng() * 40;
            c.y = 30 + rng() * 200;
        }
    });

    gameState.towers.forEach(tower => tower.update(deltaTime));
    updateAmmoHud();

    const now = Date.now();
    const waveFactor = 1 + (gameState.wave - 1) * 0.14;
    let planeInterval = CONFIG.PLANE_SPAWN_INTERVAL / waveFactor;
    planeInterval = Math.max(CONFIG.PLANE_SPAWN_MIN_MS, planeInterval);

    if (now - gameState.lastPlaneSpawn > planeInterval) {
        gameState.planes.push(new Plane());
        gameState.lastPlaneSpawn = now;
    }

    if (now - gameState.lastUFOSpawn > gameState.nextUFOSpawnInterval) {
        gameState.ufos.push(new UFO());
        gameState.lastUFOSpawn = now;
        gameState.nextUFOSpawnInterval = CONFIG.UFO_SPAWN_INTERVAL_MIN
            + rng() * (CONFIG.UFO_SPAWN_INTERVAL_MAX - CONFIG.UFO_SPAWN_INTERVAL_MIN);
    }

    gameState.planes.forEach(plane => plane.update(deltaTime));

    for (let j = gameState.planes.length - 1; j >= 0; j--) {
        const plane = gameState.planes[j];
        if (plane.y > CONFIG.GROUND_THREAT_Y) {
            gameState.planes.splice(j, 1);
            gameState.lives -= 1;
            playBreachSound();
            gameState.explosions.push(new Explosion(plane.x, CONFIG.GROUND_THREAT_Y - 10, false));
            updateLivesHud();
            gameState.comboChain = 0;
            gameState.comboTimer = 0;
            updateComboHud();
            if (gameState.lives <= 0) {
                endGame();
                return;
            }
        }
    }

    gameState.planes = gameState.planes.filter(plane => !plane.isOffScreen());

    gameState.ufos.forEach(ufo => ufo.update(deltaTime));
    gameState.ufos = gameState.ufos.filter(ufo => !ufo.isOffScreen());

    gameState.projectiles.forEach(projectile => projectile.update(deltaTime));
    gameState.projectiles = gameState.projectiles.filter(projectile => !projectile.isOffScreen());

    for (let i = gameState.projectiles.length - 1; i >= 0; i--) {
        const projectile = gameState.projectiles[i];
        const projBounds = projectile.getBounds();

        for (let j = gameState.planes.length - 1; j >= 0; j--) {
            const plane = gameState.planes[j];
            const planeBounds = plane.getBounds();

            if (checkCollision(projBounds, planeBounds)) {
                gameState.explosions.push(new Explosion(plane.x, plane.y, false));
                playExplosionSound();

                gameState.comboChain += 1;
                gameState.comboTimer = CONFIG.COMBO_DECAY_MS;
                const mult = comboMultiplierFromChain(gameState.comboChain);
                const gained = Math.max(1, Math.floor(plane.points * mult));
                gameState.score += gained;
                gameState.planesDestroyed += 1;
                scoreElement.textContent = `Puntaje: ${gameState.score}`;
                updateComboHud();

                gameState.projectiles.splice(i, 1);
                gameState.planes.splice(j, 1);
                break;
            }
        }
    }

    for (let i = gameState.projectiles.length - 1; i >= 0; i--) {
        const projectile = gameState.projectiles[i];
        const projBounds = projectile.getBounds();

        for (let j = gameState.ufos.length - 1; j >= 0; j--) {
            const ufo = gameState.ufos[j];
            const ufoBounds = ufo.getBounds();

            if (checkCollision(projBounds, ufoBounds)) {
                gameState.explosions.push(new Explosion(ufo.x, ufo.y, true));
                playUFOExplosionSound();

                gameState.comboChain += 1;
                gameState.comboTimer = CONFIG.COMBO_DECAY_MS;
                const mult = comboMultiplierFromChain(gameState.comboChain);
                const gained = Math.max(1, Math.floor(ufo.points * mult));
                gameState.score += gained;
                gameState.ufosDestroyed += 1;
                scoreElement.textContent = `Puntaje: ${gameState.score}`;
                updateComboHud();

                gameState.projectiles.splice(i, 1);
                gameState.ufos.splice(j, 1);
                break;
            }
        }
    }

    gameState.explosions.forEach(explosion => explosion.update(deltaTime));
    gameState.explosions = gameState.explosions.filter(explosion => !explosion.isDead());
}

// ============================================================================
// Renderizado
// ============================================================================
function drawSkyAndBackground() {
    const w = CONFIG.CANVAS_WIDTH;
    const h = CONFIG.CANVAS_HEIGHT;
    const dayPhase = (gameState.elapsedTime || 0) / 140000;
    const g = ctx.createLinearGradient(0, 0, 0, h);
    const h1 = 230 + Math.sin(dayPhase * Math.PI * 2) * 12;
    const h2 = 265 + Math.cos(dayPhase * Math.PI * 2) * 8;
    g.addColorStop(0, `hsla(${h1}, 42%, 14%, 1)`);
    g.addColorStop(0.45, `hsla(${h2}, 38%, 10%, 1)`);
    g.addColorStop(1, '#0a0f1a');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, w, h);

    const px = (gameState.parallaxX || 0) * 0.08;
    ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
    ctx.beginPath();
    ctx.moveTo(0, h);
    for (let i = 0; i <= 14; i++) {
        const x = (i / 14) * w;
        const nx = x + Math.sin(i * 0.7 + px * 0.02) * 40;
        const ny = 520 + Math.sin(i * 0.9 + px * 0.03) * 35;
        ctx.lineTo(nx, ny);
    }
    ctx.lineTo(w, h);
    ctx.lineTo(0, h);
    ctx.closePath();
    ctx.fill();

    ctx.fillStyle = 'rgba(30, 41, 59, 0.75)';
    ctx.beginPath();
    ctx.moveTo(0, h);
    for (let i = 0; i <= 12; i++) {
        const x = (i / 12) * w;
        const nx = x + Math.sin(i * 0.55 + px * 0.05) * 55;
        const ny = 560 + Math.sin(i * 0.65 + px * 0.04) * 28;
        ctx.lineTo(nx, ny);
    }
    ctx.lineTo(w, h);
    ctx.lineTo(0, h);
    ctx.closePath();
    ctx.fill();

    gameState.clouds.forEach(c => {
        ctx.fillStyle = c.layer === 0 ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.2)';
        ctx.beginPath();
        ctx.ellipse(c.x, c.y, c.w * 0.35, c.h, 0, 0, Math.PI * 2);
        ctx.ellipse(c.x + c.w * 0.25, c.y + 4, c.w * 0.28, c.h * 0.85, 0, 0, Math.PI * 2);
        ctx.ellipse(c.x - c.w * 0.22, c.y + 2, c.w * 0.3, c.h * 0.9, 0, 0, Math.PI * 2);
        ctx.fill();
    });

    if (!prefersReducedMotion) {
        ctx.strokeStyle = 'rgba(51, 65, 85, 0.35)';
        ctx.lineWidth = 1;
        for (let i = 0; i < w; i += 80) {
            ctx.beginPath();
            ctx.moveTo(i, 0);
            ctx.lineTo(i, h);
            ctx.stroke();
        }
        for (let i = 0; i < h; i += 80) {
            ctx.beginPath();
            ctx.moveTo(0, i);
            ctx.lineTo(w, i);
            ctx.stroke();
        }
    }
}

function drawThreatLineAndRadar() {
    const y = CONFIG.GROUND_THREAT_Y;
    ctx.save();
    ctx.setLineDash([10, 8]);
    ctx.strokeStyle = 'rgba(248, 113, 113, 0.55)';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(CONFIG.CANVAS_WIDTH, y);
    ctx.stroke();
    ctx.setLineDash([]);
    ctx.restore();

    const rw = 200;
    const rh = 44;
    const rx = CONFIG.CANVAS_WIDTH / 2 - rw / 2;
    const ry = CONFIG.CANVAS_HEIGHT - rh - 12;
    ctx.fillStyle = 'rgba(15, 23, 42, 0.72)';
    ctx.strokeStyle = 'rgba(99, 102, 241, 0.45)';
    ctx.lineWidth = 1;
    ctx.fillRect(rx, ry, rw, rh);
    ctx.strokeRect(rx, ry, rw, rh);
    ctx.fillStyle = 'rgba(148, 163, 184, 0.35)';
    ctx.font = '10px Share Tech Mono, monospace';
    ctx.fillText('RADAR', rx + 6, ry + 14);

    const plotX = (worldX) => rx + 8 + (worldX / CONFIG.CANVAS_WIDTH) * (rw - 16);
    gameState.planes.forEach(p => {
        ctx.fillStyle = '#fb923c';
        ctx.fillRect(plotX(p.x) - 2, ry + rh / 2 - 2, 4, 4);
    });
    gameState.ufos.forEach(u => {
        ctx.fillStyle = '#a78bfa';
        ctx.beginPath();
        ctx.arc(plotX(u.x), ry + rh / 2 + 8, 3, 0, Math.PI * 2);
        ctx.fill();
    });
}

function render() {
    drawSkyAndBackground();

    gameState.explosions.forEach(explosion => explosion.render());

    gameState.planes.forEach(plane => plane.render());

    if (gameState.ufos.length > 0) {
        gameState.ufos.forEach(ufo => {
            if (ufo && typeof ufo.render === 'function') {
                ufo.render();
            }
        });
    }

    gameState.projectiles.forEach(projectile => projectile.render());

    drawThreatLineAndRadar();

    gameState.towers.forEach(tower => tower.render());

    if (gameState.paused) {
        ctx.fillStyle = 'rgba(3, 7, 18, 0.55)';
        ctx.fillRect(0, 0, CONFIG.CANVAS_WIDTH, CONFIG.CANVAS_HEIGHT);
        ctx.font = 'bold 32px Orbitron, sans-serif';
        ctx.fillStyle = '#e2e8f0';
        ctx.textAlign = 'center';
        ctx.fillText('PAUSA', CONFIG.CANVAS_WIDTH / 2, CONFIG.CANVAS_HEIGHT / 2 - 10);
        ctx.font = '14px Share Tech Mono, monospace';
        ctx.fillStyle = '#94a3b8';
        ctx.fillText('Pulsa P para continuar', CONFIG.CANVAS_WIDTH / 2, CONFIG.CANVAS_HEIGHT / 2 + 22);
        ctx.textAlign = 'left';
    }
}

// ============================================================================
// Game Loop
// ============================================================================
let lastTime = performance.now();

function gameLoop(currentTime) {
    const deltaTime = currentTime - lastTime;
    lastTime = currentTime;

    if (gameRunning) {
        if (!gameState.paused) {
            update(deltaTime);
        }
        render();
    }

    requestAnimationFrame(gameLoop);
}

// Iniciar aplicación
init();
gameLoop(performance.now());
