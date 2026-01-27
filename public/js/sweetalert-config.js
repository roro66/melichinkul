/**
 * Configuración estándar de SweetAlert2 para la aplicación Melichinkul
 * Incluye: confirmaciones, mensajes flash, modo oscuro
 */

// Detectar modo oscuro
function getColorScheme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

// Configuración base para todos los SweetAlert
const swalConfig = {
    colorScheme: getColorScheme(),
    allowOutsideClick: false,
    allowEscapeKey: true,
    customClass: {
        popup: 'swal-popup',
        title: 'swal-title',
        content: 'swal-content',
        confirmButton: 'swal-confirm',
        cancelButton: 'swal-cancel'
    }
};

/**
 * Confirmación estándar para eliminar
 * @param {string} title - Título del diálogo
 * @param {string} text - Texto descriptivo
 * @param {string} confirmText - Texto del botón confirmar
 * @returns {Promise} Promise que resuelve si se confirma
 */
window.swalConfirmDelete = function(title = '¿Estás seguro?', text = 'Esta acción no se puede deshacer', confirmText = 'Sí, eliminar') {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        ...swalConfig
    });
};

/**
 * Mensaje de éxito
 * @param {string} message - Mensaje a mostrar
 * @param {number} timer - Tiempo en ms (default: 3000)
 */
window.swalSuccess = function(message, timer = 3000) {
    return Swal.fire({
        title: '¡Éxito!',
        text: message,
        icon: 'success',
        timer: timer,
        showConfirmButton: timer === 0,
        toast: timer > 0,
        position: timer > 0 ? 'top-end' : 'center',
        ...swalConfig
    });
};

/**
 * Mensaje de error
 * @param {string} message - Mensaje a mostrar
 */
window.swalError = function(message) {
    return Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonColor: '#dc2626',
        ...swalConfig
    });
};

/**
 * Mensaje de advertencia
 * @param {string} message - Mensaje a mostrar
 */
window.swalWarning = function(message) {
    return Swal.fire({
        title: 'Advertencia',
        text: message,
        icon: 'warning',
        confirmButtonColor: '#f59e0b',
        ...swalConfig
    });
};

/**
 * Mensaje informativo
 * @param {string} message - Mensaje a mostrar
 */
window.swalInfo = function(message) {
    return Swal.fire({
        title: 'Información',
        text: message,
        icon: 'info',
        confirmButtonColor: '#3b82f6',
        ...swalConfig
    });
};

// Observar cambios en el modo oscuro
const swalThemeObserver = new MutationObserver(function() {
    // Los nuevos SweetAlert usarán el modo correcto automáticamente
    swalConfig.colorScheme = getColorScheme();
});
swalThemeObserver.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});

// Escuchar eventos de Livewire para mostrar mensajes flash con SweetAlert
document.addEventListener('livewire:init', () => {
    Livewire.on('swal:success', (message) => {
        swalSuccess(message);
    });
    
    Livewire.on('swal:error', (message) => {
        swalError(message);
    });
    
    Livewire.on('swal:warning', (message) => {
        swalWarning(message);
    });
    
    Livewire.on('swal:info', (message) => {
        swalInfo(message);
    });
});

// Interceptar wire:confirm de Livewire para usar SweetAlert
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', ({ el, component }) => {
        // Buscar todos los elementos con wire:confirm y reemplazar con SweetAlert
        el.querySelectorAll('[wire\\:confirm]').forEach(element => {
            const originalConfirm = element.getAttribute('wire:confirm');
            if (originalConfirm && !element.hasAttribute('data-swal-converted')) {
                element.removeAttribute('wire:confirm');
                element.setAttribute('data-swal-converted', 'true');
                
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    swalConfirmDelete('¿Estás seguro?', originalConfirm, 'Sí, eliminar')
                        .then((result) => {
                            if (result.isConfirmed) {
                                // Ejecutar la acción original de Livewire
                                const wireClick = element.getAttribute('wire:click');
                                if (wireClick) {
                                    Livewire.find(component.id).call(wireClick.replace('delete(', '').replace(')', ''));
                                }
                            }
                        });
                });
            }
        });
    });
});

// También interceptar cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Interceptar wire:confirm en elementos existentes
    document.querySelectorAll('[wire\\:confirm]').forEach(element => {
        const originalConfirm = element.getAttribute('wire:confirm');
        if (originalConfirm && !element.hasAttribute('data-swal-converted')) {
            const wireClick = element.getAttribute('wire:click');
            element.removeAttribute('wire:confirm');
            element.setAttribute('data-swal-converted', 'true');
            
            element.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                swalConfirmDelete('¿Estás seguro?', originalConfirm, 'Sí, eliminar')
                    .then((result) => {
                        if (result.isConfirmed) {
                            // Ejecutar la acción de Livewire
                            if (wireClick) {
                                const match = wireClick.match(/delete\((\d+)\)/);
                                if (match) {
                                    const id = match[1];
                                    const component = Livewire.find(element.closest('[wire\\:id]')?.getAttribute('wire:id'));
                                    if (component) {
                                        component.call('delete', id);
                                    }
                                }
                            }
                        }
                    });
            });
        }
    });
});
