document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("insertarProductoCompraVentaModal");

    // Evita el scroll extraño al mostrar/ocultar el modal
    modal.addEventListener("shown.bs.modal", function () {
        document.body.style.overflow = "";
    });

    modal.addEventListener("hidden.bs.modal", function () {
        document.body.style.overflow = "";
    });

    const scannerCameraSelector = document.getElementById('scannerCameraSelector');
    const btnScanCodigo = document.getElementById('btnScanCodigo');
    const scannerModalElement = document.getElementById('modalScanner');
    const scannerModal = new bootstrap.Modal(scannerModalElement);
    const codigoBarraInput = document.getElementById('codigoBarra');

    let isQuaggaRunning = false;

    // Escanear código
    btnScanCodigo.addEventListener('click', () => {
        scannerModal.show();

        navigator.mediaDevices.enumerateDevices().then(devices => {
            const videoDevices = devices.filter(d => d.kind === 'videoinput');
            scannerCameraSelector.innerHTML = '';

            videoDevices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || `Cámara ${index + 1}`;
                scannerCameraSelector.appendChild(option);
            });

            if (videoDevices.length > 0) {
                startQuaggaScanner(videoDevices[0].deviceId);
            }
        });
    });

    // Cambio de cámara
    scannerCameraSelector.addEventListener('change', () => {
        const selectedDeviceId = scannerCameraSelector.value;
        startQuaggaScanner(selectedDeviceId);
    });

    // Iniciar escáner
    function startQuaggaScanner(deviceId) {
        if (isQuaggaRunning) {
            Quagga.stop();
            isQuaggaRunning = false;
        }

        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.getElementById('scanner-container'),
                constraints: {
                    deviceId: deviceId,
                    facingMode: "environment"
                }
            },
            decoder: {
                readers: ["ean_reader", "code_128_reader", "ean_8_reader"]
            }
        }, err => {
            if (err) {
                console.error("Error al iniciar Quagga:", err);
                alert("No se pudo iniciar el escáner.");
                return;
            }
            Quagga.start();
            isQuaggaRunning = true;
        });
    }

    // Detectar código
    Quagga.onDetected(result => {
        const code = result.codeResult.code;
        if (code) {
            codigoBarraInput.value = code;
            if (isQuaggaRunning) {
                Quagga.stop();
                isQuaggaRunning = false;
            }
            scannerModal.hide();
        }
    });

    // Detener al cerrar modal del escáner
    scannerModalElement.addEventListener('hidden.bs.modal', () => {
        if (isQuaggaRunning) {
            Quagga.stop();
            isQuaggaRunning = false;
        }
    });
});
