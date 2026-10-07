document.addEventListener("DOMContentLoaded", function () {

    const parametros = new URLSearchParams(window.location.search);
    const idPedido = parametros.get("id");

    const servicio = document.getElementById("servicio");
    const prenda = document.getElementById("prenda");
    const cantidad = document.getElementById("cantidad");
    const direccion = document.getElementById("direccion");
    const descuento = document.getElementById("descuento");
    const observaciones = document.getElementById("observaciones");
    const formulario = document.getElementById("formPedido");
    const cancelar = document.getElementById("cancelar");

    if (!idPedido) {
        alert("No se recibió el ID del pedido.");
        return;
    }

    fetch("../../conexiones/cargarDatosModificarPedido.php?id_pedido=" + idPedido)
        .then(response => response.json())
        .then(datos => {

            if (datos.error) {
                alert(datos.error);
                return;
            }

            datos.servicios.forEach(servicioDatos => {
                const opcion = document.createElement("option");
                opcion.value = servicioDatos.id_servicio;
                opcion.textContent = servicioDatos.nombre;

                servicio.appendChild(opcion);
            });

            const pedido = datos.pedido;

            formulario.dataset.idDetalle = pedido.id_detalle;

            servicio.value = pedido.id_servicio;
            prenda.value = pedido.prenda || "";
            cantidad.value = pedido.cantidad || 1;
            direccion.value = pedido.direccion || "";
            descuento.value = pedido.descuento || 0;
            observaciones.value = pedido.observaciones || "";

        })
        .catch(error => {
            console.error("Error al cargar el pedido:", error);
            alert("No se pudieron cargar los datos del pedido.");
        });


    formulario.addEventListener("submit", function (evento) {

        evento.preventDefault();

        const datos = new FormData();

        datos.append("id_pedido", idPedido);
        datos.append("id_detalle", formulario.dataset.idDetalle);
        datos.append("id_servicio", servicio.value);
        datos.append("prenda", prenda.value);
        datos.append("cantidad", cantidad.value);
        datos.append("direccion", direccion.value);
        datos.append("descuento", descuento.value || 0);
        datos.append("observaciones", observaciones.value);

        fetch("../../conexiones/modificarPedido.php", {
            method: "POST",
            body: datos
        })
        .then(response => response.json())
        .then(resultado => {

            if (resultado.error) {
                alert(resultado.error);
                return;
            }

            alert("Pedido modificado correctamente.");

            window.location.href = "../Pedidos/index.html";

        })
        .catch(error => {
            console.error("Error al modificar el pedido:", error);
            alert("Ocurrió un error al modificar el pedido.");
        });

    });


    cancelar.addEventListener("click", function () {
        window.location.href = "../Pedidos/index.html";
    });

});
