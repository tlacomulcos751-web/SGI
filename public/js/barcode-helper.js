/**
 * SGI - Sistema de Gestión de Inventario
 * Helper para Códigos de Barras, Impresión de Etiquetas y Pistola Lectora
 */

// Sonido de confirmación con Web Audio API (sin archivos de audio externos)
const SgiAudio = {
    ctx: null,
    getCtx: function() {
        if (!this.ctx) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) this.ctx = new AudioCtx();
        }
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume();
        }
        return this.ctx;
    },
    beepSuccess: function() {
        try {
            const ctx = this.getCtx();
            if (!ctx) return;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1400, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.12);
        } catch(e) {
            console.debug('Audio not allowed yet:', e);
        }
    },
    beepError: function() {
        try {
            const ctx = this.getCtx();
            if (!ctx) return;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(320, ctx.currentTime);
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.25);
        } catch(e) {
            console.debug('Audio error:', e);
        }
    }
};

/**
 * Autogenera un código de barras único y lo asigna al input destino
 */
async function generarCodigoBarra(inputId, previewSvgId = null) {
    const input = document.getElementById(inputId);
    if (!input) return;

    try {
        // Consultar endpoint API para garantizar que no exista en la BD
        const response = await fetch('/api/generar-codigo-barra', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        let codigoGenerado = '';
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.data && data.data.codigo) {
                codigoGenerado = data.data.codigo;
            }
        }

        // Fallback si la petición fallara
        if (!codigoGenerado) {
            const year = new Date().getFullYear().toString().slice(-2);
            const prefix = '20' + year;
            let base = prefix + Math.floor(10000000 + Math.random() * 90000000).toString();
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                sum += parseInt(base[i]) * (i % 2 === 0 ? 1 : 3);
            }
            const check = (10 - (sum % 10)) % 10;
            codigoGenerado = base + check;
        }

        input.value = codigoGenerado;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        // Renderizar previsualización si existe
        if (previewSvgId) {
            renderBarcodeVisual('#' + previewSvgId, codigoGenerado);
        } else {
            // Buscar si hay un preview hermano
            const previewSnooped = input.parentElement ? input.parentElement.parentElement.querySelector('.barcode-auto-preview') : null;
            if (previewSnooped) {
                previewSnooped.classList.remove('d-none');
                const svg = previewSnooped.querySelector('svg');
                if (svg) renderBarcodeVisual(svg, codigoGenerado);
            }
        }

        // Efecto visual de feedback
        input.classList.add('is-valid');
        setTimeout(() => input.classList.remove('is-valid'), 2000);

        SgiAudio.beepSuccess();

    } catch (error) {
        console.error('Error generando código de barras:', error);
    }
}

/**
 * Renderiza de forma segura un código de barras con JsBarcode
 */
function renderBarcodeVisual(elementOrSelector, code, options = {}) {
    if (!window.JsBarcode || !code || code === 'SIN CODIGO' || code === 'N/A') return false;

    const defaults = {
        format: "CODE128",
        width: 1.8,
        height: 50,
        displayValue: true,
        fontSize: 13,
        textMargin: 2,
        margin: 5,
        background: "#ffffff",
        lineColor: "#000000"
    };

    const finalOptions = Object.assign({}, defaults, options);

    try {
        JsBarcode(elementOrSelector, String(code).trim(), finalOptions);
        return true;
    } catch (e) {
        // Si falló con CODE128 o formato específico, intentar genérico
        try {
            JsBarcode(elementOrSelector, String(code).trim(), Object.assign({}, finalOptions, { format: "CODE128" }));
            return true;
        } catch (err) {
            console.warn('No se pudo renderizar código de barras:', code, err);
            return false;
        }
    }
}

/**
 * Abre el modal de impresión de etiquetas con los datos del producto
 */
function abrirModalImprimirEtiqueta(nombre, codigoBarra, empresa = 'SGI', clave = '', precio = 'N/A', ubicacion = '') {
    const modalEl = document.getElementById('modalImprimirEtiqueta');
    if (!modalEl) {
        console.warn('El modal #modalImprimirEtiqueta no está presente en la página.');
        return;
    }

    // Llenar datos en el modal
    const elEmpresa = document.getElementById('printEtiquetaEmpresa');
    const elNombre = document.getElementById('printEtiquetaNombre');
    const elClave = document.getElementById('printEtiquetaClave');
    const elPrecio = document.getElementById('printEtiquetaPrecio');
    const elUbicacion = document.getElementById('printEtiquetaUbicacion');
    const elSvg = document.getElementById('printEtiquetaSvg');

    if (elEmpresa) elEmpresa.textContent = empresa || 'SGI - Control de Activos';
    if (elNombre) elNombre.textContent = nombre || 'Producto';
    if (elClave) {
        elClave.textContent = clave ? `Clave: ${clave}` : '';
        elClave.style.display = clave ? 'block' : 'none';
    }
    if (elPrecio) {
        elPrecio.textContent = precio && precio !== 'N/A' ? `Precio: ${precio}` : '';
        elPrecio.style.display = (precio && precio !== 'N/A') ? 'inline-block' : 'none';
    }
    if (elUbicacion) {
        elUbicacion.textContent = ubicacion ? `Ubic: ${ubicacion}` : '';
        elUbicacion.style.display = ubicacion ? 'inline-block' : 'none';
    }

    // Renderizar código de barras para impresión (en alta calidad)
    const codigo = codigoBarra || clave || '00000000';
    if (elSvg) {
        renderBarcodeVisual(elSvg, codigo, {
            width: 2,
            height: 50,
            fontSize: 14,
            margin: 5
        });
    }

    // Guardar los datos en el modal para regenerar copias múltiples si se desea
    modalEl.dataset.nombre = nombre || '';
    modalEl.dataset.codigo = codigo;
    modalEl.dataset.empresa = empresa || '';
    modalEl.dataset.clave = clave || '';
    modalEl.dataset.precio = precio || '';
    modalEl.dataset.ubicacion = ubicacion || '';

    // Resetear selector de copias a 1
    const selectCopias = document.getElementById('printCopiasSelect');
    if (selectCopias) selectCopias.value = '1';
    actualizarCopiasEtiqueta(1);

    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();
}

/**
 * Genera la cantidad de copias deseadas en el contenedor de impresión
 */
function actualizarCopiasEtiqueta(cantidad) {
    cantidad = parseInt(cantidad) || 1;
    const modalEl = document.getElementById('modalImprimirEtiqueta');
    const container = document.getElementById('printEtiquetasGrid');
    if (!container || !modalEl) return;

    const nombre = modalEl.dataset.nombre || '';
    const codigo = modalEl.dataset.codigo || '';
    const empresa = modalEl.dataset.empresa || '';
    const clave = modalEl.dataset.clave || '';
    const precio = modalEl.dataset.precio || '';
    const ubicacion = modalEl.dataset.ubicacion || '';

    container.innerHTML = '';

    for (let i = 0; i < cantidad; i++) {
        const wrapper = document.createElement('div');
        wrapper.className = 'sgi-sticker-item';
        wrapper.innerHTML = `
            <div class="sgi-sticker-card border p-2 bg-white rounded text-center shadow-sm">
                <div class="sgi-sticker-empresa fw-bold text-uppercase small text-truncate">${empresa || 'SGI'}</div>
                <div class="sgi-sticker-nombre fw-semibold text-dark text-truncate" style="font-size: 0.85rem;">${nombre}</div>
                ${clave ? `<div class="sgi-sticker-clave text-muted small" style="font-size: 0.72rem;">${clave}</div>` : ''}
                <div class="sgi-sticker-barcode my-1">
                    <svg id="print-barcode-copy-${i}" class="img-fluid"></svg>
                </div>
                <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.7rem;">
                    <span>${ubicacion ? ubicacion : ''}</span>
                    <span class="fw-bold">${precio && precio !== 'N/A' ? precio : ''}</span>
                </div>
            </div>
        `;
        container.appendChild(wrapper);

        // Renderizar cada SVG
        setTimeout(() => {
            renderBarcodeVisual(`#print-barcode-copy-${i}`, codigo, {
                width: 1.8,
                height: 45,
                fontSize: 12,
                margin: 4
            });
        }, 10);
    }
}

/**
 * Disparador de impresión
 */
function ejecutarImpresionEtiqueta() {
    window.print();
}

/**
 * LISTENER GLOBAL PARA PISTOLA LECTORA DE CÓDIGOS DE BARRAS
 * Detecta ráfagas rápidas de pulsaciones (<50ms entre caracteres) que terminan con Enter.
 */
(function setupBarcodeScannerListener() {
    let barcodeBuffer = '';
    let lastKeyTime = 0;
    const KEY_THRESHOLD_MS = 50; // La pistola lectora escribe con < 35ms por carácter
    const MIN_BARCODE_LENGTH = 4;

    document.addEventListener('keydown', function(e) {
        // Atajos de teclado para abrir el modal del escáner: F2 o Alt+B
        if (e.key === 'F2' || (e.altKey && (e.key === 'b' || e.key === 'B'))) {
            e.preventDefault();
            const modalEl = document.getElementById('modalEscanerPistola');
            if (modalEl) {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            }
            return;
        }

        // Si el foco está en un input o textarea normal, NO capturar como ráfaga global
        // a menos que esté en el input del escáner
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const isInputFocused = (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select');
        const isScannerInput = document.activeElement && document.activeElement.id === 'escanerCodigoInput';

        const now = Date.now();
        const diff = now - lastKeyTime;
        lastKeyTime = now;

        // Si ha pasado mucho tiempo, reiniciar el buffer
        if (diff > KEY_THRESHOLD_MS && e.key !== 'Enter') {
            barcodeBuffer = '';
        }

        if (e.key === 'Enter') {
            if (barcodeBuffer.length >= MIN_BARCODE_LENGTH) {
                // Es un escaneo de pistola lectora
                e.preventDefault();
                e.stopPropagation();
                
                const scannedCode = barcodeBuffer.trim();
                barcodeBuffer = '';

                procesarLecturaPistola(scannedCode);
                return;
            }
            barcodeBuffer = '';
            return;
        }

        // Si es un carácter imprimible simple
        if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
            // Si el foco está en un input y no es el escáner, dejamos que el usuario escriba normalmente
            if (isInputFocused && !isScannerInput && diff > KEY_THRESHOLD_MS) {
                barcodeBuffer = '';
                return;
            }
            barcodeBuffer += e.key;
        }
    }, true);

    // Procesar código capturado por la pistola
    window.procesarLecturaPistola = async function(codigo) {
        if (!codigo) return;

        // Abrir el modal del escáner si no está abierto
        const modalEl = document.getElementById('modalEscanerPistola');
        if (modalEl) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }

        const input = document.getElementById('escanerCodigoInput');
        if (input) {
            input.value = codigo;
        }

        ejecutarBusquedaEscaner(codigo);
    };

    // Búsqueda en API
    window.ejecutarBusquedaEscaner = async function(codigoManual) {
        const input = document.getElementById('escanerCodigoInput');
        const codigo = (codigoManual || (input ? input.value : '')).trim();

        const loadingBox = document.getElementById('escanerLoading');
        const resultBox = document.getElementById('escanerResult');
        const emptyBox = document.getElementById('escanerEmpty');
        const errorBox = document.getElementById('escanerError');

        if (!codigo) {
            if (input) input.focus();
            return;
        }

        if (loadingBox) loadingBox.classList.remove('d-none');
        if (resultBox) resultBox.classList.add('d-none');
        if (emptyBox) emptyBox.classList.add('d-none');
        if (errorBox) errorBox.classList.add('d-none');

        try {
            const response = await fetch(`/api/buscar-codigo-barra?codigo=${encodeURIComponent(codigo)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (loadingBox) loadingBox.classList.add('d-none');

            if (response.ok && data.success && data.data) {
                const prod = data.data;
                SgiAudio.beepSuccess();

                // Llenar tarjeta de resultado
                document.getElementById('escanerProdNombre').textContent = prod.nombre;
                document.getElementById('escanerProdTipo').textContent = prod.tipo_label;
                document.getElementById('escanerProdEmpresa').textContent = prod.empresa;
                document.getElementById('escanerProdUbicacion').textContent = prod.ubicacion;
                document.getElementById('escanerProdPrecio').textContent = prod.precio_formateado;
                document.getElementById('escanerProdCodigo').textContent = prod.codigoBarra || prod.clave || 'Sin código';

                const claveRow = document.getElementById('escanerRowClave');
                if (claveRow) {
                    if (prod.clave) {
                        claveRow.classList.remove('d-none');
                        document.getElementById('escanerProdClave').textContent = prod.clave;
                    } else {
                        claveRow.classList.add('d-none');
                    }
                }

                const stockRow = document.getElementById('escanerRowStock');
                if (stockRow) {
                    if (prod.existencia !== null && prod.existencia !== undefined) {
                        stockRow.classList.remove('d-none');
                        document.getElementById('escanerProdStock').textContent = `${prod.existencia} unid.`;
                    } else if (prod.estado) {
                        stockRow.classList.remove('d-none');
                        document.getElementById('escanerProdStock').textContent = `Estado: ${prod.estado}`;
                    } else {
                        stockRow.classList.add('d-none');
                    }
                }

                // Renderizar código de barras en resultado
                const svgEl = document.getElementById('escanerProdSvg');
                if (svgEl) {
                    renderBarcodeVisual(svgEl, prod.codigoBarra || prod.clave, {
                        width: 1.8,
                        height: 40,
                        fontSize: 12
                    });
                }

                // Botones de acción
                const btnVer = document.getElementById('escanerBtnVer');
                if (btnVer) btnVer.href = prod.url_ver;

                const btnVale = document.getElementById('escanerBtnVale');
                if (btnVale) {
                    const responsivaUrl = prod.url_responsiva || prod.url_vale;
                    if (responsivaUrl) {
                        btnVale.href = responsivaUrl;
                        btnVale.target = '_blank';
                        btnVale.innerHTML = '<i class="fas fa-file-signature me-1"></i> Carta Responsiva';
                        btnVale.title = 'Emitir Carta Responsiva / Carta Poder en PDF';
                        btnVale.classList.remove('d-none');
                    } else {
                        btnVale.classList.add('d-none');
                    }
                }

                const btnPrint = document.getElementById('escanerBtnPrint');
                if (btnPrint) {
                    btnPrint.onclick = function() {
                        abrirModalImprimirEtiqueta(
                            prod.nombre,
                            prod.codigoBarra || prod.clave,
                            prod.empresa,
                            prod.clave || '',
                            prod.precio_formateado,
                            prod.ubicacion
                        );
                    };
                }

                if (resultBox) resultBox.classList.remove('d-none');

            } else {
                SgiAudio.beepError();
                const msg = (data && data.message) ? data.message : `No se encontró ningún producto con el código: ${codigo}`;
                if (errorBox) {
                    errorBox.querySelector('.error-text').textContent = msg;
                    errorBox.classList.remove('d-none');
                }
            }

        } catch (err) {
            console.error('Error al consultar escáner:', err);
            if (loadingBox) loadingBox.classList.add('d-none');
            if (errorBox) {
                errorBox.querySelector('.error-text').textContent = 'Error de comunicación con el servidor.';
                errorBox.classList.remove('d-none');
            }
            SgiAudio.beepError();
        }
    };
})();
