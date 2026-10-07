console.log("CATALOGO.JS CARGADO");

let servicios = [];
let pedido = JSON.parse(localStorage.getItem("pedido")) || [];

fetch("catalogoServicio.php")
    .then(response => {

        if (!response.ok) {
            throw new Error("Error HTTP: " + response.status);
        }

        return response.json();

    })
    .then(data => {

        console.log("Servicios recibidos:", data);

        servicios = data;

        mostrarServicios();
        actualizarPedido();

    })
    .catch(error => {

        console.error(
            "Error al cargar catalogoServicio.php:",
            error
        );

    });

function mostrarServicios() {

    const lista = document.getElementById("service-list");

    const busqueda = document
        .getElementById("search-input")
        .value
        .toLowerCase();

    const categoriaActiva = document
        .querySelector(".chip.active")
        .dataset.category
        .toLowerCase();

    lista.innerHTML = "";

    let encontrados = 0;

    servicios.forEach(servicio => {

        const nombre = String(servicio.nombre).toLowerCase();

        const categoria = String(servicio.categoria).toLowerCase();

        if (
            !nombre.includes(busqueda) ||
            (
                categoriaActiva !== "todos" &&
                categoria !== categoriaActiva
            )
        ) {
            return;
        }

        encontrados++;

        const elemento = document.createElement("li");

        elemento.className = "service-item";

        elemento.dataset.category = categoria;

        elemento.dataset.name = servicio.nombre;

        elemento.dataset.price = servicio.precio;

        elemento.innerHTML = `

            <div class="service-info">

                <p class="service-name">
                    ${servicio.nombre}
                </p>

                <p class="service-detail">
                    ${servicio.descripcion}
                </p>

                <p class="service-price">
                    $${Number(servicio.precio).toLocaleString("es-AR")}
                </p>

            </div>

            <div class="item-action">

                <input
                    type="number"
                    class="cantidad-input"
                    value="1"
                    min="1"
                    aria-label="Cantidad de ${servicio.nombre}">

                <button
                    type="button"
                    class="add-btn"
                    aria-label="Agregar ${servicio.nombre}">

                    +

                </button>

            </div>

        `;

        const boton = elemento.querySelector(".add-btn");

        const cantidadInput =
            elemento.querySelector(".cantidad-input");

        boton.addEventListener("click", function() {

            let cantidad = Number(cantidadInput.value);

            if (cantidad < 1 || isNaN(cantidad)) {

                cantidad = 1;

            }

            console.log(
                "AGREGANDO:",
                servicio.nombre,
                "CANTIDAD:",
                cantidad
            );

            const existente = pedido.find(
                producto => producto.id == servicio.id
            );

            if (existente) {

                existente.cantidad += cantidad;

            } else {

                pedido.push({

                    id: servicio.id,

                    nombre: servicio.nombre,

                    descripcion: servicio.descripcion,

                    categoria: servicio.categoria,

                    precio: Number(servicio.precio),

                    cantidad: cantidad

                });

            }

            localStorage.setItem(
                "pedido",
                JSON.stringify(pedido)
            );

            console.log(
                "PEDIDO GUARDADO:",
                pedido
            );

            actualizarPedido();

        });

        lista.appendChild(elemento);

    });

    document.getElementById("empty-state").style.display =
        encontrados === 0 ? "block" : "none";

}

function actualizarPedido() {

    console.log(
        "ACTUALIZANDO PEDIDO:",
        pedido
    );

    const lista =
        document.getElementById("cart-lines");

    const vacio =
        document.getElementById("cart-empty");

    const totalSheet =
        document.getElementById("cart-sheet-total");

    lista.innerHTML = "";

    let precioTotal = 0;

    pedido.forEach((producto, indice) => {

        if (!producto.cantidad) {

            producto.cantidad = 1;

        }

        precioTotal +=
            Number(producto.precio) *
            Number(producto.cantidad);

        const elemento =
            document.createElement("li");

        elemento.className = "cart-line";

        elemento.innerHTML = `

            <div class="cart-line-info">

                <p class="service-name">

                    ${producto.nombre}

                    <strong>
                        x${producto.cantidad}
                    </strong>

                </p>

                <p class="service-detail">

                    ${producto.descripcion}

                </p>

                <p class="service-price">

                    $${(
                        Number(producto.precio) *
                        Number(producto.cantidad)
                    ).toLocaleString("es-AR")}

                </p>

            </div>

            <button
                type="button"
                class="remove-btn"
                aria-label="Quitar ${producto.nombre}">

                -

            </button>

        `;

        elemento
            .querySelector(".remove-btn")
            .addEventListener(
                "click",
                function() {

                    console.log(
                        "QUITANDO:",
                        producto.nombre
                    );

                    pedido.splice(indice, 1);

                    localStorage.setItem(
                        "pedido",
                        JSON.stringify(pedido)
                    );

                    actualizarPedido();

                }
            );

        lista.appendChild(elemento);

    });

    totalSheet.textContent =
        "$" + precioTotal.toLocaleString("es-AR");

    if (pedido.length === 0) {

        vacio.style.display = "block";

    } else {

        vacio.style.display = "none";

    }

}

document
    .getElementById("search-input")
    .addEventListener(
        "input",
        function() {

            mostrarServicios();

        }
    );

document
    .getElementById("chips")
    .addEventListener(
        "click",
        function(event) {

            const boton =
                event.target.closest(".chip");

            if (!boton) {

                return;

            }

            document
                .querySelectorAll(".chip")
                .forEach(chip => {

                    chip.classList.remove("active");

                });

            boton.classList.add("active");

            mostrarServicios();

        }
    );

actualizarPedido();
