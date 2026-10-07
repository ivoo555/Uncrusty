document.addEventListener("DOMContentLoaded", function () {

    const buscarCliente = document.getElementById("BuscarCliente");

    const h1Nombre = document.getElementById("h1Nombre");

    const h1DNI = document.getElementById("h1DNI");

    const h1PedidosTotales = document.getElementById("h1PedidosTotales");

    const tablaPedidos = document.getElementById("tablaPedidos");

    const servicioFavorito = document.getElementById("servicioFavorito");

    const cantidadServicio = document.getElementById("cantidadServicio");

    const prendaFavorita = document.getElementById("prendaFavorita");

    const cantidadPrenda = document.getElementById("cantidadPrenda");


    buscarCliente.addEventListener("keydown", function (evento) {

        if (evento.key !== "Enter") {
            return;
        }

        const nombre = buscarCliente.value.trim();

        if (nombre === "") {
            alert("Ingrese el nombre del cliente.");
            return;
        }

        fetch(
            "../../conexiones/buscarCliente.php?nombreCompleto="
            + encodeURIComponent(nombre)
        )
        .then(response => response.text())
        .then(texto => {

            console.log("Respuesta buscarCliente:", texto);

            let datos;

            try {
                datos = JSON.parse(texto);
            } catch (error) {
                console.error("La respuesta no es JSON:", texto);
                return;
            }

            if (datos.error) {
                alert(datos.error);
                return;
            }

            h1Nombre.textContent = "Cliente: " + datos.cliente.nombreCompleto;

            h1DNI.textContent = "DNI: " + datos.cliente.DNI;

            h1PedidosTotales.textContent =  "Pedidos Totales: " + datos.pedidos_totales;


            tablaPedidos.innerHTML = "";


            if (datos.pedidos.length === 0) {

                tablaPedidos.innerHTML = `
                    <tr>
                        <td colspan="4">
                            Este cliente no tiene pedidos.
                        </td>
                    </tr>
                `;

            } else {

                datos.pedidos.forEach(pedido => {

                    const fila =
                        document.createElement("tr");

                    let detallesTexto = "";

                    if ( pedido.detalles && pedido.detalles.length > 0) {
                        detallesTexto =
                            pedido.detalles
                                .map(detalle => {

                                    return (
                                        detalle.prenda
                                        + " - "
                                        + detalle.servicio
                                        + " x"
                                        + detalle.cantidad
                                    );

                                })
                                .join(", ");

                    } else {

                        detallesTexto =
                            "Sin detalles";

                    }


                    let fecha = new Date(pedido.fecha_pedido);

                    let fechaFormateada = fecha.toLocaleDateString("es-AR");


                    let total = parseFloat(pedido.total) || 0;


                    fila.innerHTML = `
                        <td>${pedido.id_pedido}</td>

                        <td>${detallesTexto}</td>

                        <td>${fechaFormateada}</td>

                        <td>$${total.toFixed(2)}</td>
                    `;


                    tablaPedidos.appendChild(fila);

                });

            }

            cargarPreferencias( datos.cliente.id_cliente);
        })
        .catch(error => {

            console.error(
                "Error al buscar el cliente:",
                error
            );

        });

    });


    function cargarPreferencias(idCliente) {

        fetch(
            "../../conexiones/obtenerPreferencia.php?id_cliente="
            + idCliente
        )
        .then(response => response.text())
        .then(texto => {

            console.log(
                "Respuesta preferencias:",
                texto
            );

            let datos;

            try {

                datos = JSON.parse(texto);

            } catch (error) {

                console.error(
                    "La respuesta de preferencias no es JSON:",
                    texto
                );

                return;

            }


            if (datos.error) {

                console.error(datos.error);

                return;

            }


            const preferencias = datos.preferencias;


            if (!preferencias) {

                servicioFavorito.textContent = "Sin datos";

                cantidadServicio.textContent = "0 prendas";

                prendaFavorita.textContent = "Sin datos";

                cantidadPrenda.textContent = "0 prendas";

                return;

            }


            servicioFavorito.textContent = preferencias.nombre_servicio || "Sin datos";

            cantidadServicio.textContent = ( preferencias.cantidad_servicio || 0) + " prendas";


            prendaFavorita.textContent =preferencias.prenda_favorita|| "Sin datos";

            cantidadPrenda.textContent =( preferencias.cantidad_prenda || 0)+ " prendas";

        })
        .catch(error => {

            console.error(
                "Error al cargar las preferencias:",
                error
            );

        });

    }

});