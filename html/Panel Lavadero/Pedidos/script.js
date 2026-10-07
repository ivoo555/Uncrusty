
document.addEventListener("DOMContentLoaded", function () {

    const selectEstado = document.getElementById("Estado");
    const selectRepartidor = document.getElementById("Repartidor");
    const btnFiltrar = document.getElementById("btnFiltrar");
    const tablaPedidos = document.getElementById("tablaPedidos");

    fetch("../../conexiones/cargarDatosPedido.php")
        .then(response => {
            if (!response.ok) {
                throw new Error("Error HTTP: " + response.status);
            }
            return response.json();
        })
        .then(datos => {

            console.log("Datos recibidos:", datos);

            if (datos.error) {
                console.error(datos.error);
                return;
            }

            datos.repartidores.forEach(repartidor => {

                const option = document.createElement("option");

                option.value = repartidor.id_repartidor;
                option.textContent =
                    repartidor.nombreCompleto || "Sin nombre";

                selectRepartidor.appendChild(option);
            });

            mostrarPedidos(datos.pedidos);
        })
        .catch(error => {
            console.error("Error al cargar los datos:", error);
        });


    function mostrarPedidos(pedidos) {

        tablaPedidos.innerHTML = "";

        if (!pedidos || pedidos.length === 0) {

            const fila = document.createElement("tr");

            fila.innerHTML = `
                <td colspan="7">
                    No hay pedidos para mostrar
                </td>
            `;

            tablaPedidos.appendChild(fila);

            return;
        }

        pedidos.forEach(pedido => {

            console.log("Pedido:", pedido);

            const fila = document.createElement("tr");

            const cliente =
                pedido.cliente || "Sin cliente";

            const servicio =
                pedido.servicio || "Sin servicio";

            const estado =
                pedido.estado || "Sin estado";

            const repartidor =
                pedido.repartidor || "Sin asignar";

            const direccion =
                pedido.direccion || "Sin direccion";

            const fecha =
                pedido.fecha_pedido || "Sin fecha";

            fila.innerHTML = `
                <td>${cliente}</td>
                <td>${servicio} 
                <td>${estado}</td>
                <td>${repartidor}</td>
                <td>${direccion}</td>
                <td>${fecha}</td>
                <td class="acciones">

                    <button
                        class="btnMenu"
                        onclick="mostrarMenu(this)"
                    >
                        ⋮
                    </button>

                    <div class="menuAcciones">

                        <button
                            onclick="cambiarEstado(${pedido.id_pedido})"
                        >
                            Cambiar estado
                        </button>

                        <button
                            onclick="editarPedido(${pedido.id_pedido})"
                        >
                            Editar
                        </button>

                        <button
                            onclick="cancelarPedido(${pedido.id_pedido})"
                        >
                            Cancelar
                        </button>

                    </div>

                </td>
            `;

            tablaPedidos.appendChild(fila);
        });
    }


    btnFiltrar.addEventListener("click", function () {

        const estado = selectEstado.value;
        const idRepartidor = selectRepartidor.value;

        const datos = new FormData();

        datos.append("estado", estado);
        datos.append("id_repartidor", idRepartidor);

        fetch("../../conexiones/filtrarPedidos.php", {
            method: "POST",
            body: datos
        })
            .then(response => {

                if (!response.ok) {
                    throw new Error(
                        "Error HTTP: " + response.status
                    );
                }

                return response.json();
            })
            .then(respuestaFiltro => {

                console.log(
                    "Respuesta del filtro:",
                    respuestaFiltro
                );

                if (respuestaFiltro.error) {
                    console.error(
                        respuestaFiltro.error
                    );
                    return;
                }

                if (respuestaFiltro.pedidos) {
                    mostrarPedidos(
                        respuestaFiltro.pedidos
                    );
                } else {
                    mostrarPedidos(
                        respuestaFiltro
                    );
                }
            })
            .catch(error => {

                console.error(
                    "Error al filtrar los pedidos:",
                    error
                );

            });

    });

});


function mostrarMenu(boton) {

    const menu =
        boton.parentElement.querySelector(
            ".menuAcciones"
        );

    document
        .querySelectorAll(".menuAcciones")
        .forEach(elemento => {

            if (elemento !== menu) {
                elemento.classList.remove("mostrar");
            }

        });

    menu.classList.toggle("mostrar");
}


document.addEventListener("click", function (event) {

    if (!event.target.closest(".acciones")) {

        document
            .querySelectorAll(".menuAcciones")
            .forEach(menu => {

                menu.classList.remove("mostrar");

            });

    }

});


function cambiarEstado(idPedido) {

    window.location.href =
        "cambiarEstado.html?id=" + idPedido;

}


function editarPedido(idPedido) {

    window.location.href =
        "modificarPedido.html?id=" + idPedido;

}


function cancelarPedido(idPedido) {

    const confirmar = confirm(
        "¿Está seguro de eliminar el pedido #" +
        idPedido +
        "?"
    );

    if (!confirmar) {
        return;
    }

    const datos = new FormData();

    datos.append(
        "id_pedido",
        idPedido
    );

    fetch("../../conexiones/cancelarPedido.php", {
        method: "POST",
        body: datos
    })
        .then(response => response.text())
        .then(texto => {

            console.log(
                "Respuesta REAL del servidor:"
            );

            console.log(texto);

            try {

                const resultado =
                    JSON.parse(texto);

                console.log(
                    "Respuesta al eliminar:",
                    resultado
                );

                if (resultado.error) {

                    alert(
                        resultado.mensaje
                    );

                    return;
                }

                alert(
                    resultado.mensaje
                );

                location.reload();

            } catch (error) {

                console.error(
                    "La respuesta del servidor NO es JSON valido."
                );

                console.error(
                    "Respuesta recibida:",
                    texto
                );

                alert(
                    "El servidor no devolvio una respuesta valida. Revisa la consola."
                );

            }

        })
        .catch(error => {

            console.error(
                "Error al eliminar el pedido:",
                error
            );

            alert(
                "Ocurrio un error al eliminar el pedido."
            );

        });

}

