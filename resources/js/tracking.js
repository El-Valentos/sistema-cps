/**
 * Sistema de Tracking en Tiempo Real
 * CPS - Caja Petrolera de Salud
 */

// Variables globales
let trackingInterval = null;
let eventSource = null;
let ordenActualId = null;

// Inicializar tracking cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar WebSocket si está disponible
    if (window.EventSource) {
        iniciarConexionWebSocket();
    }
    
    // Configurar polling como fallback
    iniciarPolling();
    
    // Configurar botones de actualización
    configurarBotonesTracking();
});

/**
 * Inicia conexión WebSocket para eventos en tiempo real
 */
function iniciarConexionWebSocket() {
    const ordenId = document.querySelector('[data-orden-id]')?.dataset.ordenId;
    if (!ordenId) return;
    
    try {
        // Usar Laravel Echo si está configurado
        if (window.Echo) {
            window.Echo.channel(`tracking.${ordenId}`)
                .listen('.estado.actualizado', (event) => {
                    mostrarNotificacion(event.mensaje, 'info');
                    actualizarEstadoOrden(event.estado);
                    agregarEventoLineaTiempo(event);
                });
        }
    } catch (error) {
        console.log('WebSocket no disponible, usando polling');
    }
}

/**
 * Inicia polling periódico para actualizar tracking
 */
function iniciarPolling() {
    const ordenId = document.querySelector('[data-orden-id]')?.dataset.ordenId;
    if (!ordenId) return;
    
    ordenActualId = ordenId;
    
    if (trackingInterval) {
        clearInterval(trackingInterval);
    }
    
    trackingInterval = setInterval(() => {
        actualizarTracking(ordenActualId);
    }, 30000); // Cada 30 segundos
}

/**
 * Detiene el polling
 */
function detenerTracking() {
    if (trackingInterval) {
        clearInterval(trackingInterval);
        trackingInterval = null;
    }
    
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
}

/**
 * Actualiza el tracking vía AJAX
 */
function actualizarTracking(ordenId) {
    fetch(`/tracking/${ordenId}/notificaciones`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                procesarNotificaciones(data);
            }
        })
        .catch(error => console.error('Error al actualizar tracking:', error));
}

/**
 * Procesa las notificaciones recibidas
 */
function procesarNotificaciones(notificaciones) {
    notificaciones.forEach(notificacion => {
        const existe = document.querySelector(`[data-notif-id="${notificacion.id}"]`);
        if (!existe) {
            mostrarNotificacion(notificacion.mensaje, notificacion.tipo);
            agregarNotificacionLista(notificacion);
            
            if (notificacion.estado) {
                actualizarBadgeEstado(notificacion.estado);
                actualizarBarraProgreso(notificacion.estado);
            }
        }
    });
}

/**
 * Muestra una notificación en la interfaz
 */
function mostrarNotificacion(mensaje, tipo = 'success') {
    const container = document.getElementById('notificaciones-container');
    if (!container) return;
    
    const colores = {
        success: 'green',
        error: 'red',
        warning: 'yellow',
        info: 'blue'
    };
    
    const iconos = {
        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    };
    
    const notificacionHtml = `
        <div class="mb-3 p-3 rounded-lg bg-${colores[tipo]}-50 border-l-4 border-${colores[tipo]}-500 fade-in" data-notif-id="notif_${Date.now()}">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-${colores[tipo]}-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconos[tipo]}"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-gray-700">${mensaje}</p>
                    <p class="text-xs text-gray-500 mt-1">${new Date().toLocaleTimeString()}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('afterbegin', notificacionHtml);
    
    // Limitar a 10 notificaciones
    const notificaciones = container.querySelectorAll('.fade-in');
    if (notificaciones.length > 10) {
        for (let i = 10; i < notificaciones.length; i++) {
            notificaciones[i].remove();
        }
    }
    
    // Notificación del sistema si está permitido
    if (Notification.permission === 'granted' && tipo !== 'info') {
        new Notification('CPS - Actualización de Trámite', {
            body: mensaje,
            icon: '/images/cps-icon.png'
        });
    }
}

/**
 * Agrega una notificación a la lista
 */
function agregarNotificacionLista(notificacion) {
    const timeline = document.getElementById('timeline-container');
    if (!timeline) return;
    
    const eventoHtml = `
        <div class="relative pl-12 pb-6 fade-in" data-event-id="${notificacion.id}">
            <div class="absolute left-0 top-0 w-8 h-8 rounded-full flex items-center justify-center bg-${getColorByAccion(notificacion.accion)}-500">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${getIconByAccion(notificacion.accion)}"></path>
                </svg>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-semibold text-gray-800">${notificacion.titulo || 'Actualización'}</h4>
                        <p class="text-sm text-gray-500">Por: ${notificacion.usuario || 'Sistema'}</p>
                    </div>
                    <span class="text-xs text-gray-400">${notificacion.fecha || new Date().toLocaleString()}</span>
                </div>
                <p class="text-gray-700 text-sm mt-2">${notificacion.mensaje}</p>
            </div>
        </div>
    `;
    
    timeline.insertAdjacentHTML('afterbegin', eventoHtml);
    
    // Limitar a 15 eventos
    const eventos = timeline.querySelectorAll('.fade-in');
    if (eventos.length > 15) {
        for (let i = 15; i < eventos.length; i++) {
            eventos[i].remove();
        }
    }
}

/**
 * Actualiza el badge de estado
 */
function actualizarBadgeEstado(estado) {
    const badge = document.getElementById('estado-badge');
    if (!badge) return;
    
    const estados = {
        'pendiente_tesoreria': { label: 'Pendiente Tesorería', color: 'yellow' },
        'enviado_contabilidad': { label: 'Enviado a Contabilidad', color: 'blue' },
        'cheque_generado': { label: 'Cheque Generado', color: 'purple' },
        'en_caja': { label: 'En Caja', color: 'orange' },
        'entregado': { label: 'Entregado', color: 'green' },
        'cerrado': { label: 'Cerrado', color: 'gray' }
    };
    
    const info = estados[estado] || { label: estado, color: 'gray' };
    
    badge.textContent = info.label;
    badge.className = `px-2 py-1 text-sm rounded-full bg-${info.color}-100 text-${info.color}-800`;
}

/**
 * Actualiza la barra de progreso
 */
function actualizarBarraProgreso(estado) {
    const barra = document.getElementById('progress-bar');
    if (!barra) return;
    
    const ordenEstados = ['pendiente_tesoreria', 'enviado_contabilidad', 'cheque_generado', 'en_caja', 'entregado', 'cerrado'];
    const progreso = (ordenEstados.indexOf(estado) + 1) / ordenEstados.length * 100;
    
    barra.style.width = `${progreso}%`;
    
    const porcentajeTexto = document.getElementById('progress-text');
    if (porcentajeTexto) {
        porcentajeTexto.textContent = `${Math.round(progreso)}%`;
    }
}

/**
 * Agrega evento a la línea de tiempo
 */
function agregarEventoLineaTiempo(event) {
    const timeline = document.getElementById('timeline-container');
    if (!timeline) return;
    
    const eventoHtml = `
        <div class="relative pl-12 pb-6 fade-in">
            <div class="absolute left-0 top-0 w-8 h-8 rounded-full flex items-center justify-center bg-${getColorByAccion(event.accion)}-500">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${getIconByAccion(event.accion)}"></path>
                </svg>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-semibold text-gray-800">${event.titulo}</h4>
                        <p class="text-sm text-gray-500">Por: ${event.usuario}</p>
                    </div>
                    <span class="text-xs text-gray-400">${new Date().toLocaleString()}</span>
                </div>
                <p class="text-gray-700 text-sm mt-2">${event.mensaje}</p>
            </div>
        </div>
    `;
    
    timeline.insertAdjacentHTML('afterbegin', eventoHtml);
}

/**
 * Obtiene color por acción
 */
function getColorByAccion(accion) {
    const colores = {
        'creacion': 'blue',
        'envio_contabilidad': 'indigo',
        'generacion_cheque': 'purple',
        'envio_caja': 'orange',
        'entrega': 'green',
        'cierre': 'gray'
    };
    return colores[accion] || 'gray';
}

/**
 * Obtiene ícono por acción
 */
function getIconByAccion(accion) {
    const iconos = {
        'creacion': 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        'envio_contabilidad': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'generacion_cheque': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'envio_caja': 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4V8h-2',
        'entrega': 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'cierre': 'M5 13l4 4L19 7'
    };
    return iconos[accion] || 'M5 13l4 4L19 7';
}

/**
 * Configura botones de tracking
 */
function configurarBotonesTracking() {
    const refreshBtn = document.getElementById('refresh-tracking');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            if (ordenActualId) {
                actualizarTracking(ordenActualId);
                mostrarNotificacion('Actualizando información...', 'info');
            }
        });
    }
    
    const exportBtn = document.getElementById('export-tracking');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            exportarTracking();
        });
    }
}

/**
 * Exporta el tracking a PDF
 */
function exportarTracking() {
    const contenido = document.getElementById('timeline-container');
    if (!contenido) return;
    
    const ventana = window.open('', '_blank');
    ventana.document.write(`
        <html>
            <head>
                <title>Reporte de Tracking</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    h1 { color: #1E3A8A; }
                    .evento { margin-bottom: 20px; border-left: 3px solid #3B82F6; padding-left: 15px; }
                    .fecha { color: #666; font-size: 12px; }
                    .usuario { color: #888; font-size: 12px; }
                </style>
            </head>
            <body>
                <h1>Reporte de Seguimiento</h1>
                <p>Generado: ${new Date().toLocaleString()}</p>
                <hr>
                ${contenido.innerHTML}
            </body>
        </html>
    `);
    ventana.document.close();
    ventana.print();
}

// Solicitar permiso para notificaciones push
if ('Notification' in window && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
    Notification.requestPermission();
}

// Exportar funciones para uso global
window.tracking = {
    iniciar: iniciarPolling,
    detener: detenerTracking,
    actualizar: actualizarTracking,
    exportar: exportarTracking
};