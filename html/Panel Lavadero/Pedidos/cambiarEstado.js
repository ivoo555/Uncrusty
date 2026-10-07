
document.addEventListener("DOMContentLoaded", function () {

    console.log("CAMBIAR ESTADO JS CARGADO");

    // Obtener el ID del pedido desde la URL
    const parametros = new URLSearchParams(window.location.search);
    const idPedido = parametros.get("id");

    console.log("URL actual:", window.location.href);
    console.log("ID del pedido:", idPedido);


    // Obtener elementos del HTML
    const inputPedido = document.getElementById("inputPedido");
    const estadoActual = document.getElementById("estadoActual");
    const nuevoEstado = document.getElementById("nuevoEstado");
    const formulario = document.getElementById("formEstado");
    const cancelar = document.getElementById("btnCancelar");


    // Verificar que exista el ID
    if (!idPedido) {

        alert("No se recibió el ID del pedido.");

        return;
    }


    // Mostrar número del pedido
    inputPedido.value = "Pedido #" + idPedido;


    // ==========================================
    // OBTENER ESTADO ACTUAL DEL PEDIDO
    // ==========================================

    fetch("../../conexiones/obtenerEstadoPedido.php?id_pedido=" + idPedido)
        .then(response => {

            console.log("Respuesta obtener estado:", response);

            return response.json();

        })
        .then(datos => {

            console.log("Datos del pedido:", datos);

            if (datos.error) {

                alert(datos.mensaje);

                return;
            }

            // Mostrar estado actual
            estadoActual.value = datos.estado;

        })
        .catch(error => {

            console.error("Error al cargar el estado:", error);

            alert("No se pudo cargar el estado del pedido.");

        });


    // ==========================================
    // CAMBIAR ESTADO
    // ==========================================

    formulario.addEventListener("submit", function (evento) {

        evento.preventDefault();

        const estado = nuevoEstado.value;


        // Verificar que se haya seleccionado un estado
        if (!estado) {

            alert("Seleccione un nuevo estado.");

            return;
        }


        // Verificar que no sea el mismo estado
        if (estado === estadoActual.value) {

            alert("El pedido ya tiene ese estado.");

            return;
        }


        // Crear los datos que se enviarán al PHP
        const datos = new FormData();

        datos.append("id_pedido", idPedido);
        datos.append("estado", estado);


        console.log("Enviando ID:", idPedido);
        console.log("Enviando estado:", estado);


        // Enviar al PHP
        fetch("../../conexiones/cambiarEstadoPedido.php", {

            method: "POST",
            body: datos

        })
        .then(response => {

            console.log("Respuesta cambiar estado:", response);

            return response.json();

        })
        .then(resultado => {

            console.log("Resultado del servidor:", resultado);

            if (resultado.error) {

                alert(resultado.mensaje);

                return;
            }


            // Mostrar mensaje de éxito
            alert(resultado.mensaje);


            // Actualizar el estado mostrado
            estadoActual.value = resultado.estado;


            // Limpiar el select
            nuevoEstado.value = "";

        })
        .catch(error => {

            console.error("Error al cambiar el estado:", error);

            alert("Ocurrió un error al cambiar el estado.");

        });

    });


    // ==========================================
    // BOTÓN CANCELAR
    // ==========================================

    cancelar.addEventListener("click", function () {

        window.location.href = "index.html";

    });

});

