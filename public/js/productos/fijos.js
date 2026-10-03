document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("insertarProductoFijoModal");
    if (modal) {
        modal.addEventListener("hidden.bs.modal", function () {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = "";
            document.body.style.paddingRight = "";
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        });
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const scannerCameraSelector = document.getElementById('scannerCameraSelector');
    const cameraSelector = document.getElementById('cameraSelector');
    const video = document.getElementById('videoPreview');
    const canvas = document.getElementById('canvasFoto');
    const capturaInput = document.getElementById('capturaCamara');
    const btnTomarFoto = document.getElementById('btnTomarFoto');
    const btnScanCodigo = document.getElementById('btnScanCodigo');
    const scannerModalEl = document.getElementById('modalScanner');
    const scannerModal = (scannerModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal)
        ? new bootstrap.Modal(scannerModalEl)
        : null;

    let currentStream = null;
    let isQuaggaRunning = false;

    // --- Escáner de Códigos de Barras ---
    if (btnScanCodigo && scannerModal) {
        btnScanCodigo.addEventListener('click', () => {
            scannerModal.show();

            if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                navigator.mediaDevices.enumerateDevices().then(devices => {
                    const videoDevices = devices.filter(d => d.kind === 'videoinput');
                    if (scannerCameraSelector) {
                        scannerCameraSelector.innerHTML = '';
                        videoDevices.forEach((device, index) => {
                            const option = document.createElement('option');
                            option.value = device.deviceId;
                            option.text = device.label || `Cámara ${index + 1}`;
                            scannerCameraSelector.appendChild(option);
                        });
                    }

                    if (videoDevices.length > 0) {
                        startQuaggaScanner(videoDevices[0].deviceId);
                    }
                }).catch(err => {
                    console.error("Error al enumerar dispositivos:", err);
                });
            }
        });
    }

    if (scannerCameraSelector) {
        scannerCameraSelector.addEventListener('change', () => {
            const selectedDeviceId = scannerCameraSelector.value;
            startQuaggaScanner(selectedDeviceId);
        });
    }

    function startQuaggaScanner(deviceId) {
        if (typeof Quagga === 'undefined') {
            console.warn("Quagga no está disponible.");
            return;
        }

        if (isQuaggaRunning) {
            Quagga.stop();
            isQuaggaRunning = false;
        }

        const targetEl = document.getElementById('scanner-container');
        if (!targetEl) return;

        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: targetEl,
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

    if (typeof Quagga !== 'undefined') {
        Quagga.onDetected(result => {
            const code = result?.codeResult?.code;
            if (code) {
                const codigoBarraInput = document.getElementById('codigoBarra');
                if (codigoBarraInput) codigoBarraInput.value = code;
                if (isQuaggaRunning) {
                    Quagga.stop();
                    isQuaggaRunning = false;
                }
                if (scannerModal) scannerModal.hide();
            }
        });
    }

    if (scannerModalEl) {
        scannerModalEl.addEventListener('hidden.bs.modal', () => {
            if (isQuaggaRunning && typeof Quagga !== 'undefined') {
                Quagga.stop();
                isQuaggaRunning = false;
            }
        });
    }

    // --- Cámara para Tomar Fotos ---
    function stopStream() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
    }

    function startCamera(deviceId = null) {
        stopStream();

        const constraints = {
            video: deviceId ? { deviceId: { exact: deviceId } } : true
        };

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia(constraints)
                .then(stream => {
                    currentStream = stream;
                    if (video) {
                        video.srcObject = stream;
                        video.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Error al acceder a la cámara:', err);
                    alert('No se pudo acceder a la cámara.');
                });
        }
    }

    function listarCamaras() {
        if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
            navigator.mediaDevices.enumerateDevices().then(devices => {
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                if (cameraSelector) {
                    cameraSelector.innerHTML = '';
                    videoDevices.forEach((device, index) => {
                        const option = document.createElement('option');
                        option.value = device.deviceId;
                        option.text = device.label || `Cámara ${index + 1}`;
                        cameraSelector.appendChild(option);
                    });
                }

                if (videoDevices.length > 0) {
                    startCamera(videoDevices[0].deviceId);
                }
            }).catch(err => console.error(err));
        }
    }

    if (btnTomarFoto) {
        btnTomarFoto.addEventListener('click', () => {
            if (!currentStream) {
                listarCamaras();
            } else {
                if (canvas && video && capturaInput) {
                    const context = canvas.getContext("2d");
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const imageData = canvas.toDataURL("image/png");
                    capturaInput.value = imageData;

                    const preview = document.querySelector('.preview-images');
                    if (preview) {
                        preview.innerHTML += `<img src="${imageData}" class="img-thumbnail" title="Captura de cámara">`;
                    }
                }

                stopStream();
                if (video) video.style.display = "none";
            }
        });
    }

    if (cameraSelector) {
        cameraSelector.addEventListener('change', () => {
            startCamera(cameraSelector.value);
        });
    }

    if (video) {
        video.addEventListener('click', () => {
            if (canvas && capturaInput) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                const base64Image = canvas.toDataURL('image/png');
                capturaInput.value = base64Image;

                const preview = document.querySelector('.preview-images');
                if (preview) {
                    preview.innerHTML += `<img src="${base64Image}" class="img-thumbnail" title="Foto desde cámara">`;
                }
            }

            stopStream();
            video.style.display = 'none';
        });
    }

    const modalFijo = document.getElementById('insertarProductoFijoModal');
    if (modalFijo) {
        modalFijo.addEventListener('hidden.bs.modal', () => {
            stopStream();
            if (video) video.style.display = 'none';
        });
    }
});

let qrReader;

async function scanQR() {
    const scannerRow = document.getElementById("scannerRow");
    if (scannerRow) scannerRow.style.display = "table-row";

    if (typeof Html5Qrcode === 'undefined') {
        alert("El lector QR no se encuentra disponible.");
        return;
    }

    if (!qrReader) {
        qrReader = new Html5Qrcode("qr-reader");
    }

    try {
        const cameras = await Html5Qrcode.getCameras();
        const cameraSelect = document.getElementById("cameraSelect");

        if (cameraSelect && cameras) {
            cameraSelect.innerHTML = "";
            cameras.forEach((cam, index) => {
                const option = document.createElement("option");
                option.value = cam.id;
                option.text = cam.label || `Cámara ${index + 1}`;
                cameraSelect.appendChild(option);
            });

            cameraSelect.onchange = () => {
                qrReader.stop().then(() => {
                    startScanner(cameraSelect.value);
                }).catch(console.error);
            };
        }

        const startScanner = (cameraId) => {
            qrReader.start(
                cameraId,
                { fps: 10, qrbox: 250 },
                (decodedText) => {
                    const claveInput = document.getElementById("claveInput");
                    if (claveInput) claveInput.value = decodedText;
                    stopQR();
                    const form = document.getElementById("form-filtros");
                    if (form) fetchFijosApi();
                },
                () => {}
            ).catch(err => {
                console.error("No se pudo iniciar el escáner:", err);
            });
        };

        const cameraId = (cameraSelect && cameraSelect.value) ? cameraSelect.value : (cameras[0]?.id || null);
        if (cameraId) startScanner(cameraId);

    } catch (err) {
        console.error("Error al obtener cámaras:", err);
    }
}

function stopQR() {
    if (qrReader) {
        qrReader.stop().then(() => {
            qrReader.clear();
            const scannerRow = document.getElementById("scannerRow");
            if (scannerRow) scannerRow.style.display = "none";
        }).catch(err => {
            console.error("No se pudo detener el escáner:", err);
        });
    } else {
        const scannerRow = document.getElementById("scannerRow");
        if (scannerRow) scannerRow.style.display = "none";
    }
}
