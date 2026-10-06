
document.addEventListener("DOMContentLoaded", function () {

    const tablaProductos = document.getElementById("tablaProductos");

    const productosAlerta = document.getElementById("productosAlerta");

    const btnAgregarProducto = document.getElementById("btnAgregarProducto");


    

    fetch("../../conexiones/cargarDatosStock.php")

        .then(response => response.json())

        .then(datos => {

            console.log("Productos recibidos:", datos);

            mostrarProductos(datos);

        })

        .catch(error => {
            console.error(
                "Error al cargar los productos:",
                error
            );

        });



    function mostrarProductos(productos) {

        tablaProductos.innerHTML = "";

        productosAlerta.innerHTML = "";


        productos.forEach(producto => {

            let estado = "";


            if (Number(producto.cantidad_actual) <= Number(producto.cantidad_minima) ) {

                estado = "Alerta";

            } else {

                estado = "Disponible";

            }



            const fila = document.createElement("tr");


            fila.innerHTML = `

                <td>${producto.id_producto}</td>

                <td>${producto.producto}</td>

                <td>${producto.marca}</td>

                <td>${producto.cantidad_actual}</td>

                <td>${producto.cantidad_minima}</td>

                <td>${estado}</td>

                <td class="acciones">
                    <button class="btnMenu" onclick="mostrarMenu(this)"> ⋮ </button>
                    <div class="menuAcciones">

                        <button
                            onclick="editarStock(${producto.id_producto})">
                            Editar Stock
                        </button>

                        <button
                            onclick="cancelarStock(${producto.id_producto})">
                            Eliminar Producto
                        </button>

                    </div>

                </td>

            `;


            tablaProductos.appendChild(fila);


            if (estado === "Alerta") {

                const alerta =  document.createElement("div");

                alerta.innerHTML = `

                    <p>
                        <strong>${producto.producto}</strong>
                        - Stock actual:
                        ${producto.cantidad_actual}
                        | Mínimo:
                        ${producto.cantidad_minima}
                    </p>

                `;

                productosAlerta.appendChild(alerta);

            }

        });




        if (productosAlerta.innerHTML === "") {

            productosAlerta.innerHTML ="<p>No hay productos en alerta.</p>";

        }

    }



    btnAgregarProducto.addEventListener("click", function () {

        alert("Aquí se abrirá el formulario para agregar un producto.");

    });

});




function editarStock(idProducto) {
    window.location.href = "modificarStock.html?id=" + idProducto;
}

function cancelarStock() {
    window.location.href = "index.html";
}

function mostrarMenu(boton) {

    const menu =
        boton.parentElement.querySelector(".menuAcciones");

    document.querySelectorAll(".menuAcciones").forEach(elemento => {

        if (elemento !== menu) {
            elemento.classList.remove("mostrar");
        }

    });

    menu.classList.toggle("mostrar");

}