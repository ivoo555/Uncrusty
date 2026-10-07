SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS uncrustyBD
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE uncrustyBD;

-- =========================================================
-- CLIENTES
-- =========================================================

CREATE TABLE clientes (
    id_cliente INT(11) NOT NULL AUTO_INCREMENT,
    nombreCompleto VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    telefono INT(11) DEFAULT NULL,
    DNI VARCHAR(50) NOT NULL,

    PRIMARY KEY (id_cliente),
    UNIQUE KEY email (email),
    UNIQUE KEY DNI (DNI)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- CUPONES
-- =========================================================

CREATE TABLE cupones (
    id_cupon INT(11) NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL,
    valor_descuento DECIMAL(10,2) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado ENUM('Activo','Inactivo','Vencido')
        NOT NULL DEFAULT 'Activo',

    PRIMARY KEY (id_cupon),
    UNIQUE KEY codigo (codigo)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- SERVICIOS
-- =========================================================

CREATE TABLE servicios (
    id_servicio INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    categoria VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado ENUM('Activo','Inactivo')
        NOT NULL DEFAULT 'Activo',

    PRIMARY KEY (id_servicio)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- PERSONAL DE LAVANDERÍA
-- =========================================================

CREATE TABLE personallavanderia (
    id_personal INT(11) NOT NULL AUTO_INCREMENT,
    nombreCompleto VARCHAR(50) NOT NULL,
    DNI VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,

    PRIMARY KEY (id_personal),
    UNIQUE KEY DNI_email (DNI,email)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- REPARTIDORES
-- =========================================================

CREATE TABLE repartidores (
    id_repartidor INT(11) NOT NULL AUTO_INCREMENT,
    nombreCompleto VARCHAR(50) NOT NULL,
    DNI VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contrasena VARCHAR(50) NOT NULL,
    telefono INT(11) DEFAULT NULL,
    disponibilidad ENUM('Disponible','Ocupado')
        NOT NULL DEFAULT 'Disponible',

    PRIMARY KEY (id_repartidor),
    UNIQUE KEY DNI_email (DNI,email)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- PEDIDOS
-- =========================================================

CREATE TABLE pedidos (
    id_pedido INT(11) NOT NULL AUTO_INCREMENT,
    id_cliente INT(11) NOT NULL,
    fecha_pedido DATETIME NOT NULL,
    direccion VARCHAR(150) NOT NULL,

    estado ENUM(
        'Pendiente',
        'En Recolección',
        'En Limpieza',
        'Listo para entrega',
        'Entregado'
    ) NOT NULL DEFAULT 'Pendiente',

    id_descuento INT(11) DEFAULT NULL,
    observaciones TEXT DEFAULT NULL,
    id_servicio INT(11) DEFAULT NULL,

    PRIMARY KEY (id_pedido),

    KEY id_cliente (id_cliente),
    KEY id_servicio (id_servicio),
    KEY id_descuento (id_descuento),

    CONSTRAINT pedidos_ibfk_1
        FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente),

    CONSTRAINT pedidos_ibfk_2
        FOREIGN KEY (id_servicio)
        REFERENCES servicios(id_servicio),

    CONSTRAINT fk_pedidos_cupon
        FOREIGN KEY (id_descuento)
        REFERENCES cupones(id_cupon)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- DETALLE DE PEDIDOS
-- =========================================================

CREATE TABLE detalle_pedido (
    id_detalle INT(11) NOT NULL AUTO_INCREMENT,
    id_pedido INT(11) NOT NULL,
    id_servicio INT(11) NOT NULL,
    prenda VARCHAR(100) NOT NULL,
    cantidad INT(11) NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    PRIMARY KEY (id_detalle),

    KEY id_pedido (id_pedido),
    KEY id_servicio (id_servicio),

    CONSTRAINT detalle_pedido_ibfk_1
        FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido),

    CONSTRAINT detalle_pedido_ibfk_2
        FOREIGN KEY (id_servicio)
        REFERENCES servicios(id_servicio)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- REPARTOS
-- =========================================================

CREATE TABLE repartos (
    id_reparto INT(11) NOT NULL AUTO_INCREMENT,
    id_pedido INT(11) NOT NULL,
    id_repartidor INT(11) NOT NULL,

    tipo ENUM('Recoleccion','Entrega') NOT NULL,

    direccion VARCHAR(150) NOT NULL,
    fecha_programada DATETIME NOT NULL,
    fecha_realizada DATETIME DEFAULT NULL,

    estado ENUM(
        'Pendiente',
        'En proceso',
        'Completado'
    ) NOT NULL DEFAULT 'Pendiente',

    observaciones TEXT DEFAULT NULL,

    PRIMARY KEY (id_reparto),

    KEY id_pedido (id_pedido),
    KEY id_repartidor (id_repartidor),

    CONSTRAINT repartos_ibfk_1
        FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido),

    CONSTRAINT repartos_ibfk_2
        FOREIGN KEY (id_repartidor)
        REFERENCES repartidores(id_repartidor)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- PREFERENCIAS
-- =========================================================

CREATE TABLE preferencias (
    id_preferencia INT(11) NOT NULL AUTO_INCREMENT,
    id_cliente INT(11) NOT NULL,
    servicio_favorito INT(11) DEFAULT NULL,
    prenda_favorita VARCHAR(100) DEFAULT NULL,
    cantidad_servicio INT(11) NOT NULL DEFAULT 0,
    cantidad_prenda INT(11) NOT NULL DEFAULT 0,

    PRIMARY KEY (id_preferencia),

    UNIQUE KEY uq_preferencia_cliente (id_cliente),
    KEY servicio_favorito (servicio_favorito),

    CONSTRAINT preferencias_ibfk_1
        FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente),

    CONSTRAINT preferencias_ibfk_2
        FOREIGN KEY (servicio_favorito)
        REFERENCES servicios(id_servicio)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- HISTORIAL
-- =========================================================

CREATE TABLE historial (
    idHistorial INT(11) NOT NULL AUTO_INCREMENT,
    idPedido INT(11) NOT NULL,
    estrellas INT(11) NOT NULL,

    PRIMARY KEY (idHistorial),

    KEY idPedido (idPedido),

    CONSTRAINT historial_ibfk_1
        FOREIGN KEY (idPedido)
        REFERENCES pedidos(id_pedido)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- FACTURAS
-- =========================================================

CREATE TABLE facturas (
    id_factura INT(11) NOT NULL AUTO_INCREMENT,
    id_pedido INT(11) DEFAULT NULL,

    tipo_comprobante ENUM(
        'Factura A',
        'Factura B',
        'Factura C'
    ) NOT NULL,

    numero_factura VARCHAR(20) NOT NULL,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,

    nombre_razon_social VARCHAR(100) NOT NULL,
    identificacion VARCHAR(20) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,

    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0.00,
    costo_envio DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,

    estado ENUM('Emitida','Anulada')
        DEFAULT 'Emitida',

    cae VARCHAR(20) DEFAULT NULL,
    fecha_vencimiento_cae DATE DEFAULT NULL,

    PRIMARY KEY (id_factura),

    KEY id_pedido (id_pedido),

    CONSTRAINT facturas_ibfk_1
        FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- STOCK
-- =========================================================

CREATE TABLE stock (
    id_producto INT(11) NOT NULL AUTO_INCREMENT,
    producto VARCHAR(100) NOT NULL,
    marca VARCHAR(100) DEFAULT NULL,
    cantidad_actual INT(11) NOT NULL DEFAULT 0,
    cantidad_minima INT(11) NOT NULL DEFAULT 0,

    estado ENUM(
        'Disponible',
        'En alerta',
        'Agotado'
    ) NOT NULL DEFAULT 'Disponible',

    PRIMARY KEY (id_producto)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- =========================================================
-- DATOS DE CLIENTES
-- =========================================================

INSERT INTO clientes
(id_cliente, nombreCompleto, email, contrasena, telefono, DNI)
VALUES
(1, 'Gabriel Huallata',
 'gabriel.huallata.sanchez@gmail.com',
 'equipopaloma',
 1130518472,
 '48716983'),

(3, '',
 'popcato@gmail.com',
 'safsfagds',
 0,
 '48460123');


-- =========================================================
-- DATOS DE CUPONES
-- =========================================================

INSERT INTO cupones
(id_cupon, codigo, valor_descuento, fecha_inicio, fecha_vencimiento, estado)
VALUES
(1, 'VERANO2026', 20.00, '2026-09-29', '2026-09-30', 'Activo'),

(2, 'HOLA123', 21.00, '2026-09-29', '2026-10-01', 'Activo'),

(3, 'ELIASAPPAP', 20.00, '2026-10-06', '2026-10-28', 'Inactivo');


-- =========================================================
-- DATOS DE SERVICIOS
-- =========================================================

INSERT INTO servicios
(id_servicio, nombre, descripcion, categoria, precio, estado)
VALUES
(1, 'Camisas', NULL, 'Planchado', 200.00, 'Inactivo'),

(2, 'Lavado de camisas', NULL, 'Lavanderia', 2000.00, 'Activo'),

(3, 'Lavado de remeras', '', 'Lavanderia', 2000.00, 'Activo'),

(4, 'hola(dde)', NULL, 'Planchado', 122.00, 'Inactivo'),

(5, 'Pantalón', 'Lavado y planchado', 'ropa', 2500.00, 'Activo'),

(6, 'Traje completo', 'Lavado en seco', 'ropa', 6800.00, 'Activo'),

(7, 'Vestido', 'Lavado en seco', 'ropa', 4200.00, 'Activo'),

(8, 'Campera', 'Lavado y planchado', 'ropa', 5200.00, 'Activo'),

(9, 'Cortina', 'Lavado y planchado', 'hogar', 3200.00, 'Activo'),

(10, 'Alfombra', 'Lavado profundo', 'hogar', 4800.00, 'Activo'),

(11, 'Acolchado', 'Lavado y secado', 'hogar', 7500.00, 'Activo');


-- =========================================================
-- DATOS DE PERSONAL
-- =========================================================

INSERT INTO personallavanderia
(id_personal, nombreCompleto, DNI, email, contrasena)
VALUES
(1, '',
 '48460137',
 'solopidouindeseo@gmail.com',
 'kdslgjlks');


-- =========================================================
-- DATOS DE REPARTIDORES
-- =========================================================

INSERT INTO repartidores
(id_repartidor, nombreCompleto, DNI, email, contrasena, telefono, disponibilidad)
VALUES
(1, 'Elias Appap',
 '20302393',
 'eliasurielappap@gmail.com',
 'nigga',
 11022020,
 'Ocupado'),

(2, '',
 '48460158',
 'porfavordios@gmail.con',
 'pruebaprueba',
 0,
 'Disponible'),

(3, '',
 '48460148',
 'pruebitapruebona@gmail.com',
 'ayuda',
 0,
 'Disponible');


-- =========================================================
-- DATOS DE PEDIDO
-- =========================================================

INSERT INTO pedidos
(id_pedido, id_cliente, fecha_pedido, direccion, estado,
 id_descuento, observaciones, id_servicio)
VALUES
(1, 1,
 '2026-09-28 21:30:00',
 'remedios 2952',
 'En Limpieza',
 1,
 'Ninguna',
 3);


-- =========================================================
-- DATOS DEL DETALLE
-- =========================================================

INSERT INTO detalle_pedido
(id_detalle, id_pedido, id_servicio, prenda,
 cantidad, precio_unitario, subtotal)
VALUES
(2, 1, 3, 'remera',
 5, 2000.00, 10000.00);


-- =========================================================
-- DATOS DE REPARTOS
-- =========================================================

INSERT INTO repartos
(id_reparto, id_pedido, id_repartidor, tipo,
 direccion, fecha_programada, fecha_realizada,
 estado, observaciones)
VALUES

(1, 1, 2,
 'Recoleccion',
 'remedios 2932',
 '2026-10-06 02:38:42',
 NULL,
 'Pendiente',
 NULL),

(2, 1, 1,
 'Recoleccion',
 'remedios 29323',
 '2026-10-06 02:38:42',
 NULL,
 'Pendiente',
 NULL),

(3, 1, 1,
 'Recoleccion',
 'remedios 29323',
 '2026-10-06 02:38:42',
 NULL,
 'Pendiente',
 NULL),

(4, 1, 1,
 'Entrega',
 'remedios 2952',
 '2026-09-08 22:45:00',
 NULL,
 'Pendiente',
 NULL);


-- =========================================================
-- DATOS DE PREFERENCIAS
-- =========================================================

INSERT INTO preferencias
(id_preferencia, id_cliente, servicio_favorito,
 prenda_favorita, cantidad_servicio, cantidad_prenda)
VALUES
(1, 1, 3, 'remera', 5, 5);


-- =========================================================
-- DATOS DE STOCK
-- =========================================================

INSERT INTO stock
(id_producto, producto, marca,
 cantidad_actual, cantidad_minima, estado)
VALUES
(3, 'Detergente', 'ALA',
 12, 9, 'Disponible');


-- =========================================================
-- DATOS DE FACTURA
-- =========================================================

INSERT INTO facturas
(id_factura, id_pedido, tipo_comprobante,
 numero_factura, fecha_emision,
 nombre_razon_social, identificacion,
 metodo_pago, subtotal, descuento,
 costo_envio, total, estado, cae,
 fecha_vencimiento_cae)
VALUES
(1, 1,
 'Factura B',
 '0001-00000001',
 '2026-10-05 22:51:20',
 'Gabriel Huallata',
 '48716983',
 'Tarjeta de débito',
 10000.00,
 2000.00,
 0.00,
 8000.00,
 'Emitida',
 '48560186367960',
 '2026-10-16');


-- =========================================================
-- AUTO_INCREMENT
-- =========================================================

ALTER TABLE clientes
AUTO_INCREMENT = 4;

ALTER TABLE cupones
AUTO_INCREMENT = 4;

ALTER TABLE servicios
AUTO_INCREMENT = 12;

ALTER TABLE personallavanderia
AUTO_INCREMENT = 2;

ALTER TABLE repartidores
AUTO_INCREMENT = 4;

ALTER TABLE pedidos
AUTO_INCREMENT = 2;

ALTER TABLE detalle_pedido
AUTO_INCREMENT = 3;

ALTER TABLE repartos
AUTO_INCREMENT = 5;

ALTER TABLE preferencias
AUTO_INCREMENT = 2;

ALTER TABLE historial
AUTO_INCREMENT = 1;

ALTER TABLE facturas
AUTO_INCREMENT = 2;

ALTER TABLE stock
AUTO_INCREMENT = 4;


COMMIT;
