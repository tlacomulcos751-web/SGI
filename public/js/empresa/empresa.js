document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById("modalActualizarEmpresa"); // Reemplaza con el ID de tu modal

    modal.addEventListener("shown.bs.modal", function () {
        document.body.style.overflow = ""; // Quita el scroll
    });

    modal.addEventListener("hidden.bs.modal", function () {
        document.body.style.overflow = ""; // Restaura el scroll cuando se cierra el modal
    });

    const botonesEditar = document.querySelectorAll('.btn-editar-empresa');

    botonesEditar.forEach(btn => {
        btn.addEventListener('click', function () {
            const empresa = {
                id: btn.dataset.id,
                nombre: btn.dataset.nombre,
                nombre_registrado: btn.dataset.nombre_registrado,
                rfc: btn.dataset.rfc,
                direccion: btn.dataset.direccion,
                latitud: btn.dataset.latitud,
                longitud: btn.dataset.longitud,
                tipo: btn.dataset.tipo || '',
            };

            // También puedes actualizar el atributo action del formulario:
            const form = document.getElementById('insertarProductoFijoModal');
            form.action = btn.dataset.action;

            // Llenar y abrir el modal
            abrirModalActualizarEmpresa(empresa);
            document.body.style.removeProperty("overflow");
        });
    });
});

let mapActualizar, markerActualizar;

function initMapActualizar() {
    const defaultPos = {
        lat: 19.432608,
        lng: -99.133209
    };
    mapActualizar = new google.maps.Map(document.getElementById("mapActualizar"), {
        zoom: 12,
        center: defaultPos,
    });

    markerActualizar = new google.maps.Marker({
        position: defaultPos,
        map: mapActualizar,
        draggable: true
    });

    google.maps.event.addListener(markerActualizar, 'dragend', function (event) {
        updateLatLngFieldsActualizar(event.latLng);
    });

    mapActualizar.addListener('click', function (event) {
        markerActualizar.setPosition(event.latLng);
        updateLatLngFieldsActualizar(event.latLng);
    });
}

function updateLatLngFieldsActualizar(latLng) {
    document.getElementById("act_latitud").value = latLng.lat().toFixed(6);
    document.getElementById("act_longitud").value = latLng.lng().toFixed(6);
}

function abrirModalActualizarEmpresa(empresa) {
    document.getElementById("act_nombre").value = empresa.nombre || '';
    document.getElementById("act_nombre_registrado").value = empresa.nombre_registrado || '';
    document.getElementById("act_rfc").value = empresa.rfc || '';
    document.getElementById("act_direccion").value = empresa.direccion || '';
    document.getElementById("act_latitud").value = empresa.latitud || '';
    document.getElementById("act_longitud").value = empresa.longitud || '';
    document.getElementById("act_tipo").value = empresa.tipo || '';
    document.getElementById("act_empresa_id").value = empresa.id || '';

    const lat = parseFloat(empresa.latitud) || 19.432608;
    const lng = parseFloat(empresa.longitud) || -99.133209;
    const newPos = {
        lat: lat,
        lng: lng
    };

    if (mapActualizar && markerActualizar) {
        mapActualizar.setCenter(newPos);
        markerActualizar.setPosition(newPos);
    }

    const modal = new bootstrap.Modal(document.getElementById('modalActualizarEmpresa'));
    modal.show();
}

// Llamar esta función al cargar scripts de Google Maps
window.initMapActualizar = initMapActualizar;

