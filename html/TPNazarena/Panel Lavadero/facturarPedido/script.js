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



fetch("../../conexiones/cargarDatosFactura.php")
    .then(response => response.json())
    .then(listaPedidos => { 

        const opcionInicial = document.createElement("option");

        opcionInicial.value = "";
        opcionInicial.textContent = "Seleccione un pedido";
        opcionInicial.disabled = true;
        opcionInicial.selected = true;

        inputPedido.appendChild(opcionInicial);


        listaPedidos.forEach(pedido => {

            const opcion = document.createElement("option");

            opcion.value = pedido.id_pedido;

            opcion.textContent = "Pedido #" + pedido.id_pedido;

            inputPedido.appendChild(opcion);

        });

    })
    .catch(error => {

        console.error(
            "Error al cargar los pedidos:",
            error
        );

    });

fetch("../../conexiones/cargarCupones.php")
    .then(response => response.json())
    .then(listaCupones => { 

        listaCupones.forEach(cupon => {

            const opcion = document.createElement("option");

            opcion.value = cupon.id_cupon;

            opcion.textContent =cupon.codigo + " - " + cupon.valor_descuento + "%";
            opcion.dataset.descuento = cupon.valor_descuento;

            inputCupon.appendChild(opcion);

        });

    })
    .catch(error => {

        console.error(
            "Error al cargar los cupones:",
            error
        );

    });


inputPedido.addEventListener("change", function() {

    const idPedido = inputPedido.value;

    if (idPedido === "") {
        return;
    }

    cargarPedido(idPedido);

});



function cargarPedido(idPedido) {

    fetch("../../conexiones/obtenerDatosFactura.php?id_pedido=" + idPedido )

        .then(response => response.json())

        .then(datos => {

            console.log(datos.pedido);


            if (datos.error) {

                alert(datos.error);

                return;

            }


            

            inputNombre.value = datos.pedido.nombreCompleto;

            inputIdentificacion.value = datos.pedido.DNI || ""; 

            subtotalOriginal = parseFloat(datos.pedido.subtotal) || 0;

            descuentoOriginal = 0;

            costoEnvioOriginal = parseFloat(datos.pedido.costo_envio) || 0;

            totalOriginal = parseFloat(datos.pedido.total) || 0;


            pServicios.textContent = "Subtotal Servicios: \$" + subtotalOriginal.toFixed(2);


            pDescuento.textContent = "Descuento Aplicado: \$" + descuentoOriginal.toFixed(2);


            pEnvio.textContent = "Costo Envio: \$" + costoEnvioOriginal.toFixed(2);


            h2Total.textContent ="Total: \$" + totalOriginal.toFixed(2);

            inputCupon.selectedIndex = 0;

            tabla.innerHTML = "";


            const encabezado =  document.createElement("tr");

            encabezado.innerHTML = `
                <th>Servicio/Prenda</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            `;


            tabla.appendChild(encabezado);



            datos.detalles.forEach(detalle => {

                const fila = document.createElement("tr");


                fila.innerHTML = `
                    <td>
                        ${detalle.servicio} -
                        ${detalle.prenda}
                    </td>

                    <td>
                        ${detalle.cantidad}
                    </td>

                    <td>
                        ${detalle.precio_unitario}
                    </td>

                    <td>
                        ${detalle.subtotal}
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

        });

}


inputCupon.addEventListener("change", function() {

    calcularTotal();

});



function calcularTotal() {

   
    const opcion = inputCupon.options[inputCupon.selectedIndex];


    const porcentaje = parseFloat(opcion.dataset.descuento) || 0;


    const descuentoCupon = subtotalOriginal * porcentaje / 100;

    const total =subtotalOriginal - descuentoCupon - descuentoOriginal + costoEnvioOriginal;


    pDescuento.textContent = "Descuento Aplicado: \$" + descuentoCupon.toFixed(2);

    h2Total.textContent = "Total: \$" + total.toFixed(2);
}