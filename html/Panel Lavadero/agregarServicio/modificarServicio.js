const formulario = document.getElementById("formServicio");
const cancelar = document.getElementById("cancelar");

const parametros = new URLSearchParams(window.location.search);
const idServicio = parametros.get("id");

if (!idServicio) {

    alert("No se encontró el ID del servicio");

} else {

    cargarServicio();
}


function cargarServicio() {

    fetch("../../conexiones/obtenerServicio.php?id=" + idServicio)

        .then(respuesta => respuesta.json())

        .then(data => {

            if (data.error) {
                alert(data.mensaje);
                return;
            }

            document.getElementById("nombre").value = data.nombre;
            document.getElementById("descripcion").value = data.descripcion || "";
            document.getElementById("categoria").value = data.categoria;
            document.getElementById("precio").value = data.precio;

        })

        .catch(error => {

            console.error("Error:", error);
            alert("Error al cargar los datos del servicio");

        });
}


formulario.addEventListener("submit", function(event) {

    event.preventDefault();

    const datos = new FormData();

    datos.append("id_servicio", idServicio);
    datos.append(
        "nombre",
        document.getElementById("nombre").value
    );
    datos.append(
        "descripcion",
        document.getElementById("descripcion").value
    );
    datos.append(
        "categoria",
        document.getElementById("categoria").value
    );
    datos.append(
        "precio",
        document.getElementById("precio").value
    );

    fetch("../../conexiones/modificarServicio.php", {
        method: "POST",
        body: datos
    })

    .then(respuesta => respuesta.json())

    .then(data => {

        if (data.error) {

            alert(data.mensaje);
            return;

        }

        alert(data.mensaje);

        window.location.href = "index.html";

    })

    .catch(error => {

        console.error("Error:", error);
        alert("Error al modificar el servicio");

    });

});


cancelar.addEventListener("click", function() {

    window.history.back();

});
