const inputPedido = document.getElementById("inputPedido");
const inputNombre = document.getElementById("inputNombre");
const inputIdentificacion = document.getElementById("inputIdentificacion");
const selectComprobante = document.getElementById("selectComprobante");
const selectMetodoPago = document.getElementById("selectMetodoPago");

const pServicios = document.getElementById("pServicios");
const pDescuento = document.getElementById("pDescuento");
const pEnvio = document.getElementById("pEnvio");
const h2Total = document.getElementById("h2Total");

const btnFactura = document.getElementById("btnFactura");

const tabla = document.querySelector("#Tabla table");

let subtotalOriginal = 0;
let costoEnvioOriginal = 0;
let descuentoCuponActual = 0;
let totalFinal = 0;
let porcentajeCupon = 0;




fetch("../../conexiones/cargarDatosFactura.php")
    .then(response => {

        if (!response.ok) {
            throw new Error(
                "Error HTTP: " + response.status
            );
        }

        return response.json();
    })
    .then(listaPedidos => {

        const opcionInicial =
            document.createElement("option");

        opcionInicial.value = "";
        opcionInicial.textContent =
            "Seleccione un pedido";

        opcionInicial.disabled = true;
        opcionInicial.selected = true;

        inputPedido.appendChild(opcionInicial);


        listaPedidos.forEach(pedido => {

            const opcion =
                document.createElement("option");

            opcion.value =
                pedido.id_pedido;

            opcion.textContent =
                "Pedido #" +
                pedido.id_pedido;

            inputPedido.appendChild(opcion);
        });
    })
    .catch(error => {

        console.error(
            "Error al cargar los pedidos:",
            error
        );

        alert(
            "No se pudieron cargar los pedidos."
        );
    });



inputPedido.addEventListener(
    "change",
    function () {

        const idPedido =
            inputPedido.value;

        if (idPedido === "") {
            return;
        }

        cargarPedido(idPedido);
    }
);



function cargarPedido(idPedido) {

    fetch(
        "../../conexiones/obtenerDatosFactura.php?id_pedido=" +
        idPedido
    )
        .then(response => {

            if (!response.ok) {
                throw new Error(
                    "Error HTTP: " +
                    response.status
                );
            }

            return response.json();
        })
        .then(datos => {

            console.log(
                "Datos del pedido:",
                datos
            );


            if (datos.error) {

                alert(
                    datos.mensaje ||
                    datos.error
                );

                return;
            }


            inputNombre.value =
                datos.pedido.nombreCompleto || "";

            inputIdentificacion.value =
                datos.pedido.DNI || "";


            subtotalOriginal =
                parseFloat(
                    datos.pedido.subtotal
                ) || 0;

            costoEnvioOriginal =
                parseFloat(
                    datos.pedido.costo_envio
                ) || 0;


            porcentajeCupon =
                parseFloat(
                    datos.pedido.porcentaje_descuento
                ) || 0;



            descuentoCuponActual =
                subtotalOriginal *
                porcentajeCupon /
                100;



            totalFinal =
                subtotalOriginal -
                descuentoCuponActual +
                costoEnvioOriginal;



            pServicios.textContent =
                "Subtotal Servicios: $" +
                subtotalOriginal.toFixed(2);


            pDescuento.textContent =
                "Descuento Aplicado: $" +
                descuentoCuponActual.toFixed(2);


            pEnvio.textContent =
                "Costo Envio: $" +
                costoEnvioOriginal.toFixed(2);


            h2Total.textContent =
                "Total: $" +
                totalFinal.toFixed(2);



            tabla.innerHTML = "";


            const encabezado =
                document.createElement("tr");


            encabezado.innerHTML = `
                <th>Servicio/Prenda</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            `;


            tabla.appendChild(encabezado);


            datos.detalles.forEach(detalle => {

                const fila =
                    document.createElement("tr");


                fila.innerHTML = `
                    <td>
                        ${detalle.servicio} -
                        ${detalle.prenda}
                    </td>

                    <td>
                        ${detalle.cantidad}
                    </td>

                    <td>
                        $${parseFloat(
                            detalle.precio_unitario
                        ).toFixed(2)}
                    </td>

                    <td>
                        $${parseFloat(
                            detalle.subtotal
                        ).toFixed(2)}
                    </td>
                `;


                tabla.appendChild(fila);
            });

        })
        .catch(error => {

            console.error(
                "Error al cargar los datos del pedido:",
                error
            );

            alert(
                "No se pudieron cargar los datos del pedido."
            );
        });
}



btnFactura.addEventListener(
    "click",
    function () {


        if (!inputPedido.value) {

            alert(
                "Primero seleccioná un pedido."
            );

            return;
        }


        if (
            inputNombre.value.trim() === ""
        ) {

            alert(
                "El nombre del cliente es obligatorio."
            );

            return;
        }


        if (
            inputIdentificacion.value.trim() === ""
        ) {

            alert(
                "La identificación es obligatoria."
            );

            return;
        }


        if (
            selectMetodoPago.value === ""
        ) {

            alert(
                "Seleccioná un método de pago."
            );

            return;
        }



        const confirmar =
            confirm(
                "¿Querés generar la factura del pedido #" +
                inputPedido.value +
                "?"
            );


        if (!confirmar) {
            return;
        }



        const tipoComprobante =
            selectComprobante.value;



        const datos =
            new FormData();


        datos.append(
            "id_pedido",
            inputPedido.value
        );


        datos.append(
            "tipo_comprobante",
            tipoComprobante
        );


        datos.append(
            "nombre_razon_social",
            inputNombre.value.trim()
        );


        datos.append(
            "identificacion",
            inputIdentificacion.value.trim()
        );


        datos.append(
            "metodo_pago",
            selectMetodoPago.value
        );




        datos.append(
            "subtotal",
            subtotalOriginal.toFixed(2)
        );


        datos.append(
            "descuento",
            descuentoCuponActual.toFixed(2)
        );


        datos.append(
            "costo_envio",
            costoEnvioOriginal.toFixed(2)
        );


        datos.append(
            "total",
            totalFinal.toFixed(2)
        );




        btnFactura.disabled = true;

        btnFactura.textContent =
            "Generando factura...";




        fetch(
            "../../conexiones/generarFactura.php",
            {
                method: "POST",
                body: datos
            }
        )
            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        "Error HTTP: " +
                        response.status
                    );
                }

                return response.json();
            })
            .then(data => {

                console.log(
                    "Respuesta de generación:",
                    data
                );


                if (data.error) {

                    alert(
                        data.mensaje
                    );

                    return;
                }


                alert(
                    "Factura generada correctamente.\n\n" +

                    "Número de factura: " +
                    data.numero_factura +

                    "\nCAE: " +
                    data.cae +

                    "\nVencimiento del CAE: " +
                    data.fecha_vencimiento_cae
                );


                window.open(
                    "../../conexiones/generarFacturaTXT.php?id_factura=" +
                    data.id_factura,
                    "_blank"
                );


                console.log(
                    "ID factura:",
                    data.id_factura
                );
            })
            .catch(error => {

                console.error(
                    "Error al generar la factura:",
                    error
                );

                alert(
                    "Ocurrió un error al generar la factura."
                );
            })
            .finally(() => {

                btnFactura.disabled = false;

                btnFactura.textContent =
                    "Generar Factura en ARCA";
            });
    }
);
