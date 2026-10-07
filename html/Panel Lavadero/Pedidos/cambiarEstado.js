
document.addEventListener("DOMContentLoaded", function () {

    console.log("CAMBIAR ESTADO JS CARGADO");

    const parametros = new URLSearchParams(window.location.search);
    const idPedido = parametros.get("id");

    console.log("URL actual:", window.location.href);
    console.log("ID del pedido:", idPedido);


    const inputPedido = document.getElementById("inputPedido");
    const estadoActual = document.getElementById("estadoActual");
    const nuevoEstado = document.getElementById("nuevoEstado");
    const formulario = document.getElementById("formEstado");
    const cancelar = document.getElementById("btnCancelar");


    if (!idPedido) {

        alert("No se recibió el ID del pedido.");

        return;
    }


    inputPedido.value = "Pedido #" + idPedido;



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

            estadoActual.value = datos.estado;

        })
        .catch(error => {

            console.error("Error al cargar el estado:", error);

            alert("No se pudo cargar el estado del pedido.");

        });



    formulario.addEventListener("submit", function (evento) {

        evento.preventDefault();

        const estado = nuevoEstado.value;



        if (!estado) {

            alert("Seleccione un nuevo estado.");

            return;
        }

        if (estado === estadoActual.value) {

            alert("El pedido ya tiene ese estado.");

            return;
        }


        const datos = new FormData();

        datos.append("id_pedido", idPedido);
        datos.append("estado", estado);


        console.log("Enviando ID:", idPedido);
        console.log("Enviando estado:", estado);



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


            alert(resultado.mensaje);


            estadoActual.value = resultado.estado;


            nuevoEstado.value = "";

        })
        .catch(error => {

            console.error("Error al cambiar el estado:", error);

            alert("Ocurrió un error al cambiar el estado.");

        });

    });



    cancelar.addEventListener("click", function () {

        window.location.href = "index.html";

    });

});

