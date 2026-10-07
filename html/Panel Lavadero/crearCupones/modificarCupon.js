const formulario = document.getElementById("formCupon");
const cancelar = document.getElementById("cancelar");



const parametros = new URLSearchParams(window.location.search);
const idCupon = parametros.get("id");



if (!idCupon) {

    alert("No se encontró el cupón");

} else {

   
    document.getElementById("id_cupon").value = idCupon;


  
    fetch("../../conexiones/obtenerCupon.php?id=" + idCupon)

        .then(respuesta => respuesta.json())

        .then(data => {

            if (data.error) {

                alert(data.mensaje);
                return;

            }


            document.getElementById("codigo").value = data.codigo;

            document.getElementById("descuento").value =
                data.valor_descuento;

            document.getElementById("fechaInicio").value =
                data.fecha_inicio;

            document.getElementById("fechaFin").value =
                data.fecha_vencimiento;

        })

        .catch(error => {

            console.error("Error:", error);

            alert("Error al cargar los datos del cupón");

        });
}



formulario.addEventListener("submit", function(e) {

    e.preventDefault();


    const datos = new FormData();


    datos.append(
        "id_cupon",
        document.getElementById("id_cupon").value
    );


    datos.append(
        "codigo",
        document.getElementById("codigo").value
    );


    datos.append(
        "valor_descuento",
        document.getElementById("descuento").value
    );


    datos.append(
        "fecha_inicio",
        document.getElementById("fechaInicio").value
    );


    datos.append(
        "fecha_vencimiento",
        document.getElementById("fechaFin").value
    );


    fetch("../../conexiones/modificarCupon.php", {
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

        window.history.back();

    })

    .catch(error => {

        console.error("Error:", error);

        alert("Error al modificar el cupón");

    });

});



cancelar.addEventListener("click", function() {

    window.history.back();

});
