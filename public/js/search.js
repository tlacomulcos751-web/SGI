document.addEventListener('DOMContentLoaded', function () {
    const inputBusqueda = document.getElementById('busqueda-empresa');
    const tbody = document.getElementById('empresas-tbody');

    if (inputBusqueda && tbody) {
        inputBusqueda.addEventListener('keyup', function () {
            const filtro = inputBusqueda.value.toLowerCase();
            const filas = tbody.querySelectorAll('tr');

            filas.forEach(fila => {
                const textoFila = fila.innerText.toLowerCase();
                fila.style.display = textoFila.includes(filtro) ? '' : 'none';
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const inputBusqueda = document.getElementById('busqueda-ubicacion');
    const tbody = document.getElementById('ubicaciones-tbody');

    if (inputBusqueda && tbody) {
        inputBusqueda.addEventListener('keyup', function () {
            const filtro = inputBusqueda.value.toLowerCase();
            const filas = tbody.querySelectorAll('tr');

            filas.forEach(fila => {
                const textoFila = fila.innerText.toLowerCase();
                fila.style.display = textoFila.includes(filtro) ? '' : 'none';
            });
        });
    }
});
