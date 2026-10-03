document.addEventListener('DOMContentLoaded', function () {
    const estadoSelect = document.getElementById('estadoSelect');
    const documentoField = document.getElementById('documentoBajaField');

    function toggleDocumentoField() {
        if (estadoSelect.value === 'baja') {
            documentoField.style.display = 'block';
        } else {
            documentoField.style.display = 'none';
        }
    }

    estadoSelect.addEventListener('change', toggleDocumentoField);
    toggleDocumentoField(); // Ejecutar al cargar
});

function showImage(src) {
    document.getElementById('modalImage').src = src;
}
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("actualizarInfoModal"); // Reemplaza con el ID de tu modal

    modal.addEventListener("shown.bs.modal", function () {
        document.body.style.overflow = ""; // Quita el scroll
    });

    modal.addEventListener("hidden.bs.modal", function () {
        document.body.style.overflow = ""; // Restaura el scroll cuando se cierra el modal
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("actualizarUbicacionModal"); // Reemplaza con el ID de tu modal

    modal.addEventListener("shown.bs.modal", function () {
        document.body.style.overflow = ""; // Quita el scroll
    });

    modal.addEventListener("hidden.bs.modal", function () {
        document.body.style.overflow = ""; // Restaura el scroll cuando se cierra el modal
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("actualizarResponsableModal"); // Reemplaza con el ID de tu modal

    modal.addEventListener("shown.bs.modal", function () {
        document.body.style.overflow = ""; // Quita el scroll
    });

    modal.addEventListener("hidden.bs.modal", function () {
        document.body.style.overflow = ""; // Restaura el scroll cuando se cierra el modal
    });
});