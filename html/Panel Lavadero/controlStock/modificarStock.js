document.addEventListener("DOMContentLoaded", function () {

    const parametros = new URLSearchParams(window.location.search);
    const idProducto = parametros.get("id");

    const marca = document.getElementById("Marca");
    const nuevaCantidad = document.getElementById("nuevaCantidad");
    const stockMinimo = document.getElementById("stockMinimo");
    const formulario = document.getElementById("formProducto");
    const cancelar = document.getElementById("cancelar");

    let productoActual = "";
    let estadoActual = "";

    if (!idProducto) {
        alert("No se recibió el ID del producto.");
        return;
    }

    fetch("../../conexiones/cargarDatosModificarProducto.php?id_producto=" + idProducto)
        .then(response => response.json())
        .then(datos => {

            if (datos.error) {
                alert(datos.error);
                return;
            }

            const producto = datos.producto;

            productoActual = producto.producto;
            estadoActual = producto.estado;

            marca.value = producto.marca || "";
            nuevaCantidad.value = producto.cantidad_actual;
            stockMinimo.value = producto.cantidad_minima;

        })
        .catch(error => {
            console.error("Error al cargar el producto:", error);
            alert("No se pudieron cargar los datos del producto.");
        });

    formulario.addEventListener("submit", function (evento) {

        evento.preventDefault();

        const datos = new FormData();

        datos.append("id_producto", idProducto);
        datos.append("producto", productoActual);
        datos.append("marca", marca.value);
        datos.append("cantidad_actual", nuevaCantidad.value);
        datos.append("cantidad_minima", stockMinimo.value);
        datos.append("estado", estadoActual);

        fetch("../../conexiones/modificarProducto.php", {
            method: "POST",
            body: datos
        })
        .then(response => response.json())
        .then(resultado => {

            if (resultado.error) {
                alert(resultado.error);
                return;
            }

            alert("Producto modificado correctamente.");

            window.location.href = "index.html";

        })
        .catch(error => {
            console.error("Error al modificar el producto:", error);
            alert("Ocurrió un error al modificar el producto.");
        });

    });

    cancelar.addEventListener("click", function () {
        window.location.href = "index.html";
    });

});