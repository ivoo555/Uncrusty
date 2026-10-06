const inputPedido = document.getElementById("inputPedido");
const inputNombre = document.getElementById("inputNombre");
const inputIdentificacion = document.getElementById("inputIdentificacion");
const selectComprobante = document.getElementById("selectComprobante");
const selectMetodoPago = document.getElementById("selectMetodoPago");
const inputCupon = document.getElementById("inputCupon");

const pServicios = document.getElementById("pServicios");
const pDescuento = document.getElementById("pDescuento");
const pEnvio = document.getElementById("pEnvio");
const h2Total = document.getElementById("h2Total");

const btnFactura = document.getElementById("btnFactura");

const tabla = document.querySelector("#Tabla table");

let subtotalOriginal = 0;
let descuentoOriginal = 0;
let costoEnvioOriginal = 0;
let totalOriginal = 0;

let descuentoCuponActual = 0;
let totalFinal = 0;


// ==========================================
// CARGAR PEDIDOS
// ==========================================

fetch("../../conexiones/cargarDatosFactura.php")

    .then(response => response.json())

    .then(listaPedidos => {

        const opcionInicial =
            document.createElement("option");

        opcionInicial.value = "";
        opcionInicial.textContent =
            "Seleccione un pedido";

        opcionInicial.disabled = true;
        opcionInicial.selected = true;

        inputPedido.appendChild(
            opcionInicial
        );


        listaPedidos.forEach(pedido => {

            const opcion =
                document.createElement("option");

            opcion.value =
                pedido.id_pedido;

            opcion.textContent =
                "Pedido #" +
                pedido.id_pedido;

            inputPedido.appendChild(
                opcion
            );
        });

    })

    .catch(error => {

        console.error(
            "Error al cargar los pedidos:",
            error
        );

    });


// ==========================================
// CARGAR CUPONES
// ==========================================

fetch("../../conexiones/cargarCupones.php")

    .then(response => response.json())

    .then(listaCupones => {

        listaCupones.forEach(cupon => {

            const opcion =
                document.createElement("option");

            opcion.value =
                cupon.id_cupon;

            opcion.textContent =
                cupon.codigo +
                " - " +
                cupon.valor_descuento +
                "%";

            opcion.dataset.descuento =
                cupon.valor_descuento;

            inputCupon.appendChild(
                opcion
            );

        });

    })

    .catch(error => {

        console.error(
            "Error al cargar los cupones:",
            error
        );

    });


// ==========================================
// CAMBIAR PEDIDO
// ==========================================

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


// ==========================================
// CARGAR PEDIDO
// ==========================================

function cargarPedido(idPedido) {

    fetch(
        "../../conexiones/obtenerDatosFactura.php?id_pedido=" +
        idPedido
    )

        .then(response => response.json())

        .then(datos => {

            console.log(
                "Datos del pedido:",
                datos
            );


            if (datos.error) {

                alert(datos.error);

                return;
            }


            // ==================================
            // DATOS DEL CLIENTE
            // ==================================

            inputNombre.value =
                datos.pedido.nombreCompleto;

            inputIdentificacion.value =
                datos.pedido.DNI || "";


            // ==================================
            // DATOS ECONÓMICOS
            // ==================================

            subtotalOriginal =
                parseFloat(
                    datos.pedido.subtotal
                ) || 0;

            descuentoOriginal = 0;

            costoEnvioOriginal =
                parseFloat(
                    datos.pedido.costo_envio
                ) || 0;

            totalOriginal =
                parseFloat(
                    datos.pedido.total
                ) || 0;


            // Al cargar un pedido
            // no hay cupón aplicado

            descuentoCuponActual = 0;

            totalFinal =
                subtotalOriginal -
                descuentoOriginal +
                costoEnvioOriginal;


            // ==================================
            // MOSTRAR RESUMEN
            // ==================================

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


            // ==================================
            // REINICIAR CUPÓN
            // ==================================

            inputCupon.selectedIndex = 0;


            // ==================================
            // LIMPIAR TABLA
            // ==================================

            tabla.innerHTML = "";


            const encabezado =
                document.createElement("tr");

            encabezado.innerHTML = `
                <th>Servicio/Prenda</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            `;

            tabla.appendChild(
                encabezado
            );


            // ==================================
            // MOSTRAR DETALLES
            // ==================================

            datos.detalles.forEach(
                detalle => {

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

                    tabla.appendChild(
                        fila
                    );
                }
            );

        })

        .catch(error => {

            console.error(
                "Error al cargar los datos del pedido:",
                error
            );

        });
}


// ==========================================
// CAMBIAR CUPÓN
// ==========================================

inputCupon.addEventListener(
    "change",
    function () {

        calcularTotal();

    }
);


// ==========================================
// CALCULAR TOTAL
// ==========================================

function calcularTotal() {

    const opcion =
        inputCupon.options[
            inputCupon.selectedIndex
        ];


    const porcentaje =
        parseFloat(
            opcion.dataset.descuento
        ) || 0;


    descuentoCuponActual =
        subtotalOriginal *
        porcentaje /
        100;


    totalFinal =
        subtotalOriginal -
        descuentoCuponActual -
        descuentoOriginal +
        costoEnvioOriginal;


    pDescuento.textContent =
        "Descuento Aplicado: $" +
        descuentoCuponActual.toFixed(2);


    h2Total.textContent =
        "Total: $" +
        totalFinal.toFixed(2);
}


// ==========================================
// GENERAR FACTURA
// ==========================================

btnFactura.addEventListener(
    "click",
    function () {

        // ==============================
        // VALIDAR PEDIDO
        // ==============================

        if (
            !inputPedido.value
        ) {

            alert(
                "Primero seleccioná un pedido."
            );

            return;
        }


        // ==============================
        // VALIDAR CLIENTE
        // ==============================

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


        // ==============================
        // CONFIRMAR
        // ==============================

        const confirmar = confirm(
            "¿Querés generar la factura del pedido #" +
            inputPedido.value +
            "?"
        );


        if (!confirmar) {
            return;
        }


        // ==============================
        // TIPO DE FACTURA
        // ==============================

        let tipoComprobante =
            selectComprobante.value;


        if (
            tipoComprobante === "facturaA"
        ) {

            tipoComprobante =
                "Factura A";

        } else if (
            tipoComprobante === "FacturaB"
        ) {

            tipoComprobante =
                "Factura B";

        } else if (
            tipoComprobante === "FacturaC"
        ) {

            tipoComprobante =
                "Factura C";
        }


        // ==============================
        // CREAR FORM DATA
        // ==============================

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


        // ==============================
        // ENVIAR A PHP
        // ==============================

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


            // ==========================
            // FACTURA GENERADA
            // ==========================

            alert(
                "Factura generada correctamente.\n\n" +

                "Número de factura: " +
                data.numero_factura +

                "\nCAE: " +
                data.cae +

                "\nVencimiento del CAE: " +
                data.fecha_vencimiento_cae
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