CREATE TABLE public.barrio_domiciliario (
    id bigint NOT NULL,
    domiciliario_id bigint NOT NULL,
    barrio_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.barrio_domiciliario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.barrio_domiciliario_id_seq OWNED BY public.barrio_domiciliario.id;
CREATE TABLE public.barrios (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    zona_id bigint NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.barrios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.barrios_id_seq OWNED BY public.barrios.id;
CREATE TABLE public.carrito_items (
    id bigint NOT NULL,
    sesion_mesa_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    precio numeric(10,2) NOT NULL,
    cantidad integer NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.carrito_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.carrito_items_id_seq OWNED BY public.carrito_items.id;
CREATE TABLE public.categorias (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.categorias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.categorias_id_seq OWNED BY public.categorias.id;
CREATE TABLE public.detalle_pedidos (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    estado character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.detalle_pedidos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.detalle_pedidos_id_seq OWNED BY public.detalle_pedidos.id;
CREATE TABLE public.domiciliarios (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    telefono character varying(255) NOT NULL,
    email character varying(255),
    vehiculo_tipo character varying(255) DEFAULT 'moto'::character varying NOT NULL,
    placa character varying(255),
    documento character varying(255),
    calificacion numeric(3,2) DEFAULT '5'::numeric NOT NULL,
    zona_id bigint NOT NULL,
    estado character varying(255) DEFAULT 'disponible'::character varying NOT NULL,
    pedidos_hoy integer DEFAULT 0 NOT NULL,
    pedidos_totales integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT domiciliarios_estado_check CHECK (((estado)::text = ANY ((ARRAY['disponible'::character varying, 'en_ruta'::character varying, 'ocupado'::character varying, 'fuera_servicio'::character varying])::text[]))),
    CONSTRAINT domiciliarios_vehiculo_tipo_check CHECK (((vehiculo_tipo)::text = ANY ((ARRAY['moto'::character varying, 'bicicleta'::character varying, 'carro'::character varying])::text[])))
);
CREATE SEQUENCE public.domiciliarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.domiciliarios_id_seq OWNED BY public.domiciliarios.id;
CREATE TABLE public.historial_estado_pedidos (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    estado character varying(30) NOT NULL,
    usuario_id bigint,
    fecha timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.historial_estado_pedidos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.historial_estado_pedidos_id_seq OWNED BY public.historial_estado_pedidos.id;
CREATE TABLE public.inventarios (
    id bigint NOT NULL,
    producto_id bigint,
    stock_actual integer NOT NULL,
    stock_minimo integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.inventarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.inventarios_id_seq OWNED BY public.inventarios.id;
CREATE TABLE public.mesas (
    id bigint NOT NULL,
    numero integer NOT NULL,
    capacidad integer,
    estado character varying(20) DEFAULT 'DISPONIBLE'::character varying NOT NULL,
    qr_codigo character varying(255),
    qr_activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.mesas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.mesas_id_seq OWNED BY public.mesas.id;
CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);
CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;
CREATE TABLE public.movimientos_inventario (
    id bigint NOT NULL,
    inventario_id bigint NOT NULL,
    tipo character varying(20) NOT NULL,
    cantidad integer NOT NULL,
    motivo character varying(255),
    fecha timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.movimientos_inventario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.movimientos_inventario_id_seq OWNED BY public.movimientos_inventario.id;
CREATE TABLE public.notificaciones (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    tipo character varying(50),
    titulo character varying(150),
    mensaje text,
    leida boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.notificaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.notificaciones_id_seq OWNED BY public.notificaciones.id;
CREATE TABLE public.pagos (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    metodo_pago character varying(20) NOT NULL,
    monto numeric(10,2) NOT NULL,
    estado character varying(20) DEFAULT 'PENDIENTE'::character varying NOT NULL,
    referencia_transaccion character varying(255),
    fecha_reembolso timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    telefono character varying(255),
    email character varying(255)
);
CREATE SEQUENCE public.pagos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.pagos_id_seq OWNED BY public.pagos.id;
CREATE TABLE public.pedidos (
    id bigint NOT NULL,
    sesion_mesa_id bigint CONSTRAINT pedidos_sub_sesion_id_not_null NOT NULL,
    mesero_id bigint NOT NULL,
    estado character varying(30) DEFAULT 'CREADO'::character varying NOT NULL,
    total numeric(10,2),
    fecha_cancelacion timestamp(0) without time zone,
    motivo_cancelacion character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.pedidos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.pedidos_id_seq OWNED BY public.pedidos.id;
CREATE TABLE public.productos (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    precio numeric(10,2) NOT NULL,
    estado boolean DEFAULT true NOT NULL,
    categoria_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    imagen character varying(255)
);
CREATE SEQUENCE public.productos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.productos_id_seq OWNED BY public.productos.id;
CREATE TABLE public.report_schedules (
    id bigint NOT NULL,
    active boolean DEFAULT true NOT NULL,
    frequency character varying(255) NOT NULL,
    "time" time(0) without time zone NOT NULL,
    days json,
    month_days json,
    custom_config json,
    method character varying(255) NOT NULL,
    recipients json,
    whatsapp_number character varying(255),
    sections json,
    last_run_at timestamp(0) without time zone,
    next_run_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.report_schedules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.report_schedules_id_seq OWNED BY public.report_schedules.id;
CREATE TABLE public.roles (
    id bigint NOT NULL,
    nombre character varying(50) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;
CREATE TABLE public.sesiones (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    token character varying(500) NOT NULL,
    ip character varying(45),
    user_agent character varying(255),
    fecha_expiracion timestamp(0) without time zone,
    activa boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.sesiones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.sesiones_id_seq OWNED BY public.sesiones.id;
CREATE TABLE public.sesiones_mesa (
    id bigint NOT NULL,
    mesa_id bigint NOT NULL,
    estado character varying(20) DEFAULT 'ACTIVA'::character varying NOT NULL,
    fecha_inicio timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    fecha_cierre timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    codigo_grupo character varying(10),
    tipo_sesion character varying(20) DEFAULT 'INDIVIDUAL'::character varying NOT NULL,
    motivo_cierre character varying(20),
    participantes_activos integer DEFAULT 1 NOT NULL,
    token character varying(64)
);
CREATE SEQUENCE public.sesiones_mesa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.sesiones_mesa_id_seq OWNED BY public.sesiones_mesa.id;
CREATE TABLE public.usuarios (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    rol_id bigint NOT NULL,
    estado boolean DEFAULT true NOT NULL,
    ultimo_login timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    remember_token character varying(100)
);
CREATE SEQUENCE public.usuarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;
CREATE TABLE public.zona_coberturas (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    descripcion text,
    costo_envio numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    tiempo_estimado integer DEFAULT 30 NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);
CREATE SEQUENCE public.zona_coberturas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER SEQUENCE public.zona_coberturas_id_seq OWNED BY public.zona_coberturas.id;
ALTER TABLE ONLY public.barrio_domiciliario ALTER COLUMN id SET DEFAULT nextval('public.barrio_domiciliario_id_seq'::regclass);
ALTER TABLE ONLY public.barrios ALTER COLUMN id SET DEFAULT nextval('public.barrios_id_seq'::regclass);
ALTER TABLE ONLY public.carrito_items ALTER COLUMN id SET DEFAULT nextval('public.carrito_items_id_seq'::regclass);
ALTER TABLE ONLY public.categorias ALTER COLUMN id SET DEFAULT nextval('public.categorias_id_seq'::regclass);
ALTER TABLE ONLY public.detalle_pedidos ALTER COLUMN id SET DEFAULT nextval('public.detalle_pedidos_id_seq'::regclass);
ALTER TABLE ONLY public.domiciliarios ALTER COLUMN id SET DEFAULT nextval('public.domiciliarios_id_seq'::regclass);
ALTER TABLE ONLY public.historial_estado_pedidos ALTER COLUMN id SET DEFAULT nextval('public.historial_estado_pedidos_id_seq'::regclass);
ALTER TABLE ONLY public.inventarios ALTER COLUMN id SET DEFAULT nextval('public.inventarios_id_seq'::regclass);
ALTER TABLE ONLY public.mesas ALTER COLUMN id SET DEFAULT nextval('public.mesas_id_seq'::regclass);
ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);
ALTER TABLE ONLY public.movimientos_inventario ALTER COLUMN id SET DEFAULT nextval('public.movimientos_inventario_id_seq'::regclass);
ALTER TABLE ONLY public.notificaciones ALTER COLUMN id SET DEFAULT nextval('public.notificaciones_id_seq'::regclass);
ALTER TABLE ONLY public.pagos ALTER COLUMN id SET DEFAULT nextval('public.pagos_id_seq'::regclass);
ALTER TABLE ONLY public.pedidos ALTER COLUMN id SET DEFAULT nextval('public.pedidos_id_seq'::regclass);
ALTER TABLE ONLY public.productos ALTER COLUMN id SET DEFAULT nextval('public.productos_id_seq'::regclass);
ALTER TABLE ONLY public.report_schedules ALTER COLUMN id SET DEFAULT nextval('public.report_schedules_id_seq'::regclass);
ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);
ALTER TABLE ONLY public.sesiones ALTER COLUMN id SET DEFAULT nextval('public.sesiones_id_seq'::regclass);
ALTER TABLE ONLY public.sesiones_mesa ALTER COLUMN id SET DEFAULT nextval('public.sesiones_mesa_id_seq'::regclass);
ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);
ALTER TABLE ONLY public.zona_coberturas ALTER COLUMN id SET DEFAULT nextval('public.zona_coberturas_id_seq'::regclass);
INSERT INTO public.barrio_domiciliario (id, domiciliario_id, barrio_id, created_at, updated_at) VALUES ('1', '7', '13', NULL, NULL);
INSERT INTO public.barrio_domiciliario (id, domiciliario_id, barrio_id, created_at, updated_at) VALUES ('2', '7', '15', NULL, NULL);
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('12', 'Timanco', '3', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('13', 'Canaima', '3', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('14', 'Limonar', '3', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('15', 'Arismendi Mora', '3', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('16', 'Puertas del Sol', '3', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('17', 'Centro', '4', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('18', 'Altico', '4', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('19', 'San Pedro', '4', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('20', 'Los Mártires', '4', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('21', 'Quirinal', '4', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('22', 'Galindo', '5', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('23', 'Chicalá', '5', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('24', 'Santa Isabel', '5', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('25', 'Villa Regina', '5', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('31', 'Cándido Leguízamo', '1', 't', '2026-04-26 23:16:01', '2026-04-26 23:16:01');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('32', 'Eduardo Santos', '1', 't', '2026-04-26 23:16:01', '2026-04-26 23:16:01');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('33', 'Santa Inés', '1', 't', '2026-04-26 23:16:01', '2026-04-26 23:16:01');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('34', 'Camilo Torres', '1', 't', '2026-04-26 23:16:01', '2026-04-26 23:16:01');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('35', 'Villa del Prado', '1', 't', '2026-04-26 23:16:01', '2026-04-26 23:16:01');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('36', 'Las Palmas', '2', 't', '2026-04-27 00:12:41', '2026-04-27 00:12:41');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('37', 'Ipanema', '2', 't', '2026-04-27 00:12:41', '2026-04-27 00:12:41');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('38', 'Buganviles', '2', 't', '2026-04-27 00:12:41', '2026-04-27 00:12:41');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('39', 'Los Guaduales', '2', 't', '2026-04-27 00:12:41', '2026-04-27 00:12:41');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('40', 'La Rioja', '2', 't', '2026-04-27 00:12:41', '2026-04-27 00:12:41');
INSERT INTO public.barrios (id, nombre, zona_id, activo, created_at, updated_at) VALUES ('41', 'El Tesoro', '2', 't', '2026-04-27 00:12:41', '2026-04-27 00:12:41');
INSERT INTO public.carrito_items (id, sesion_mesa_id, producto_id, nombre, precio, cantidad, subtotal, created_at, updated_at) VALUES ('5', '11', '1', 'Coca Cola', '3500.00', '1', '3500.00', '2026-04-09 22:07:22', '2026-04-09 22:07:22');
INSERT INTO public.carrito_items (id, sesion_mesa_id, producto_id, nombre, precio, cantidad, subtotal, created_at, updated_at) VALUES ('20', '27', '9', 'Speed', '2500.00', '1', '2500.00', '2026-04-10 13:45:08', '2026-04-10 13:45:08');
INSERT INTO public.carrito_items (id, sesion_mesa_id, producto_id, nombre, precio, cantidad, subtotal, created_at, updated_at) VALUES ('32', '44', '21', 'Salchipapa Especial', '15000.00', '3', '45000.00', '2026-04-27 12:40:29', '2026-04-27 12:40:29');
INSERT INTO public.categorias (id, nombre, descripcion, created_at, updated_at) VALUES ('1', 'Bebidas Frias', 'Bebidas Refrescantes', '2026-03-11 17:51:08', '2026-03-11 17:51:08');
INSERT INTO public.categorias (id, nombre, descripcion, created_at, updated_at) VALUES ('2', 'Bebidas Calientes', 'Las bebidas calientes son una forma reconfortante de disfrutar del calor en días fríos y lluviosos.', '2026-03-11 21:35:33', '2026-03-11 21:35:33');
INSERT INTO public.categorias (id, nombre, descripcion, created_at, updated_at) VALUES ('3', 'Jugos Naturales', 'Los jugos naturales son bebidas extraídas de frutas, verduras o raíces, habitualmente por presión y en ocasiones por trituración.', '2026-03-11 21:36:13', '2026-03-11 21:36:13');
INSERT INTO public.categorias (id, nombre, descripcion, created_at, updated_at) VALUES ('4', 'Dulceria', 'variedad de productos dulces diseñados para disfrutar en cualquier momento', '2026-03-11 21:39:14', '2026-04-11 13:37:02');
INSERT INTO public.categorias (id, nombre, descripcion, created_at, updated_at) VALUES ('6', 'Comidas Rápidas', 'Comidas rápidas que se caracterizan por su preparación ágil y práctica, ideales para consumo inmediato. Incluyen productos como hamburguesas, perros calientes, salchipapas, papas fritas, sándwiches y otros alimentos similares, pensados para ofrecer sabor, rapidez en el servicio y facilidad de consumo.', '2026-04-12 18:41:21', '2026-04-12 18:41:21');
INSERT INTO public.categorias (id, nombre, descripcion, created_at, updated_at) VALUES ('7', 'Acompañamientos', 'Deliciosos acompañamientos preparados al momento, perfectos para complementar tus comidas y hacer tu pedido más completo y satisfactorio.', '2026-04-12 18:49:21', '2026-04-12 18:49:21');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('1', '1', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-09 16:24:27', '2026-04-09 16:24:27');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('2', '2', '3', '2', '4300.00', '8600.00', 'ACTIVO', '2026-04-09 20:45:39', '2026-04-09 20:45:39');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('3', '3', '8', '1', '3400.00', '3400.00', 'ACTIVO', '2026-04-09 21:06:47', '2026-04-09 21:06:47');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('4', '4', '3', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-09 21:17:24', '2026-04-09 21:17:24');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('5', '5', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-09 22:10:20', '2026-04-09 22:10:20');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('6', '6', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-09 22:11:19', '2026-04-09 22:11:19');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('7', '7', '7', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-09 22:12:03', '2026-04-09 22:12:03');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('8', '8', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-09 22:32:18', '2026-04-09 22:32:18');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('9', '9', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-09 22:48:52', '2026-04-09 22:48:52');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('10', '10', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-09 23:10:05', '2026-04-09 23:10:05');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('11', '11', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-09 23:41:08', '2026-04-09 23:41:08');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('12', '12', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-10 00:03:49', '2026-04-10 00:03:49');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('13', '13', '2', '1', '4500.00', '4500.00', 'ACTIVO', '2026-04-10 00:11:19', '2026-04-10 00:11:19');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('14', '14', '9', '1', '2500.00', '2500.00', 'ACTIVO', '2026-04-10 00:11:23', '2026-04-10 00:11:23');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('15', '15', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-10 00:25:54', '2026-04-10 00:25:54');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('16', '16', '10', '1', '1300.00', '1300.00', 'ACTIVO', '2026-04-10 00:26:16', '2026-04-10 00:26:16');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('17', '17', '7', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-10 00:27:19', '2026-04-10 00:27:19');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('18', '18', '4', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-10 12:27:00', '2026-04-10 12:27:00');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('19', '19', '3', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('20', '19', '4', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('21', '19', '5', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('22', '19', '6', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('23', '19', '7', '1', '4300.00', '4300.00', 'ACTIVO', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('24', '19', '1', '5', '3500.00', '17500.00', 'ACTIVO', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('25', '19', '14', '3', '6000.00', '18000.00', 'ACTIVO', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('26', '20', '17', '1', '12000.00', '12000.00', 'ACTIVO', '2026-04-17 14:47:03', '2026-04-17 14:47:03');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('27', '21', '17', '1', '12000.00', '12000.00', 'ACTIVO', '2026-04-17 15:52:42', '2026-04-17 15:52:42');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('28', '22', '1', '1', '3500.00', '3500.00', 'ACTIVO', '2026-04-17 21:39:48', '2026-04-17 21:39:48');
INSERT INTO public.detalle_pedidos (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado, created_at, updated_at) VALUES ('29', '23', '17', '1', '12000.00', '12000.00', 'ACTIVO', '2026-04-21 15:17:42', '2026-04-21 15:17:42');
INSERT INTO public.domiciliarios (id, nombre, telefono, email, vehiculo_tipo, placa, documento, calificacion, zona_id, estado, pedidos_hoy, pedidos_totales, created_at, updated_at) VALUES ('1', 'Juan Carlos Perdomo', '315 234 5678', NULL, 'moto', 'XYZ-12A', NULL, '5.00', '1', 'disponible', '0', '0', '2026-04-26 23:21:42', '2026-04-26 23:21:42');
INSERT INTO public.domiciliarios (id, nombre, telefono, email, vehiculo_tipo, placa, documento, calificacion, zona_id, estado, pedidos_hoy, pedidos_totales, created_at, updated_at) VALUES ('2', 'Luis Alberto Rojas', '320 876 5432', NULL, 'moto', 'ABC-34B', NULL, '5.00', '2', 'en_ruta', '0', '0', '2026-04-26 23:21:42', '2026-04-26 23:21:42');
INSERT INTO public.domiciliarios (id, nombre, telefono, email, vehiculo_tipo, placa, documento, calificacion, zona_id, estado, pedidos_hoy, pedidos_totales, created_at, updated_at) VALUES ('3', 'Marta Lucía Castro', '311 456 7890', NULL, 'bicicleta', NULL, NULL, '5.00', '4', 'disponible', '0', '0', '2026-04-26 23:21:42', '2026-04-26 23:21:42');
INSERT INTO public.domiciliarios (id, nombre, telefono, email, vehiculo_tipo, placa, documento, calificacion, zona_id, estado, pedidos_hoy, pedidos_totales, created_at, updated_at) VALUES ('4', 'Ricardo Silva', '300 123 9876', NULL, 'moto', 'FGH-56C', NULL, '5.00', '3', 'ocupado', '0', '0', '2026-04-26 23:21:42', '2026-04-26 23:21:42');
INSERT INTO public.domiciliarios (id, nombre, telefono, email, vehiculo_tipo, placa, documento, calificacion, zona_id, estado, pedidos_hoy, pedidos_totales, created_at, updated_at) VALUES ('5', 'Andrés Felipe Cuenca', '318 555 4433', NULL, 'moto', 'JKL-78D', NULL, '5.00', '5', 'fuera_servicio', '0', '0', '2026-04-26 23:21:42', '2026-04-26 23:21:42');
INSERT INTO public.domiciliarios (id, nombre, telefono, email, vehiculo_tipo, placa, documento, calificacion, zona_id, estado, pedidos_hoy, pedidos_totales, created_at, updated_at) VALUES ('6', 'Leider Fabian Ramos Cano', '3112533941', NULL, 'carro', '123abc', NULL, '5.00', '4', 'disponible', '0', '0', '2026-04-26 23:55:11', '2026-04-26 23:55:11');
INSERT INTO public.domiciliarios (id, nombre, telefono, email, vehiculo_tipo, placa, documento, calificacion, zona_id, estado, pedidos_hoy, pedidos_totales, created_at, updated_at) VALUES ('7', 'Juan Trujillo', '3138002280', NULL, 'bicicleta', NULL, NULL, '5.00', '3', 'disponible', '0', '0', '2026-04-27 00:08:37', '2026-04-27 00:08:37');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('1', '1', 'CREADO', NULL, '2026-04-09 16:24:27', '2026-04-09 16:24:27', '2026-04-09 16:24:27');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('2', '1', 'CREADO', NULL, '2026-04-09 16:24:36', '2026-04-09 16:24:36', '2026-04-09 16:24:36');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('3', '1', 'EN_COCINA', '6', '2026-04-09 16:25:11', '2026-04-09 16:25:11', '2026-04-09 16:25:11');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('4', '1', 'EN_PREPARACION', '6', '2026-04-09 16:25:12', '2026-04-09 16:25:12', '2026-04-09 16:25:12');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('5', '1', 'LISTO', '6', '2026-04-09 16:25:14', '2026-04-09 16:25:14', '2026-04-09 16:25:14');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('6', '1', 'ENTREGADO', '2', '2026-04-09 16:28:45', '2026-04-09 16:28:45', '2026-04-09 16:28:45');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('7', '2', 'CREADO', NULL, '2026-04-09 20:45:39', '2026-04-09 20:45:39', '2026-04-09 20:45:39');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('8', '2', 'CREADO', NULL, '2026-04-09 20:46:51', '2026-04-09 20:46:51', '2026-04-09 20:46:51');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('9', '2', 'EN_COCINA', '6', '2026-04-09 20:49:27', '2026-04-09 20:49:27', '2026-04-09 20:49:27');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('10', '2', 'EN_PREPARACION', '6', '2026-04-09 20:50:00', '2026-04-09 20:50:00', '2026-04-09 20:50:00');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('11', '2', 'LISTO', '6', '2026-04-09 20:50:14', '2026-04-09 20:50:14', '2026-04-09 20:50:14');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('12', '2', 'ENTREGADO', '2', '2026-04-09 20:51:01', '2026-04-09 20:51:01', '2026-04-09 20:51:01');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('13', '3', 'CREADO', NULL, '2026-04-09 21:06:47', '2026-04-09 21:06:47', '2026-04-09 21:06:47');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('14', '3', 'CREADO', NULL, '2026-04-09 21:06:53', '2026-04-09 21:06:53', '2026-04-09 21:06:53');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('15', '3', 'CANCELADO', NULL, '2026-04-09 21:07:24', '2026-04-09 21:07:24', '2026-04-09 21:07:24');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('16', '4', 'CREADO', NULL, '2026-04-09 21:17:24', '2026-04-09 21:17:24', '2026-04-09 21:17:24');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('17', '4', 'CREADO', NULL, '2026-04-09 21:17:32', '2026-04-09 21:17:32', '2026-04-09 21:17:32');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('18', '4', 'EN_COCINA', '6', '2026-04-09 22:09:15', '2026-04-09 22:09:15', '2026-04-09 22:09:15');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('19', '4', 'EN_PREPARACION', '6', '2026-04-09 22:09:16', '2026-04-09 22:09:16', '2026-04-09 22:09:16');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('20', '4', 'LISTO', '6', '2026-04-09 22:09:18', '2026-04-09 22:09:18', '2026-04-09 22:09:18');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('21', '4', 'ENTREGADO', '2', '2026-04-09 22:09:34', '2026-04-09 22:09:34', '2026-04-09 22:09:34');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('22', '5', 'CREADO', NULL, '2026-04-09 22:10:20', '2026-04-09 22:10:20', '2026-04-09 22:10:20');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('23', '6', 'CREADO', NULL, '2026-04-09 22:11:19', '2026-04-09 22:11:19', '2026-04-09 22:11:19');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('24', '7', 'CREADO', NULL, '2026-04-09 22:12:03', '2026-04-09 22:12:03', '2026-04-09 22:12:03');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('25', '5', 'EN_COCINA', '6', '2026-04-09 22:13:58', '2026-04-09 22:13:58', '2026-04-09 22:13:58');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('26', '5', 'EN_PREPARACION', '6', '2026-04-09 22:14:45', '2026-04-09 22:14:45', '2026-04-09 22:14:45');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('27', '6', 'CREADO', NULL, '2026-04-09 22:15:02', '2026-04-09 22:15:02', '2026-04-09 22:15:02');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('28', '5', 'CREADO', NULL, '2026-04-09 22:15:09', '2026-04-09 22:15:09', '2026-04-09 22:15:09');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('29', '7', 'CREADO', NULL, '2026-04-09 22:15:23', '2026-04-09 22:15:23', '2026-04-09 22:15:23');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('30', '5', 'EN_COCINA', '6', '2026-04-09 22:15:43', '2026-04-09 22:15:43', '2026-04-09 22:15:43');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('31', '6', 'EN_COCINA', '6', '2026-04-09 22:15:45', '2026-04-09 22:15:45', '2026-04-09 22:15:45');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('32', '7', 'EN_COCINA', '6', '2026-04-09 22:15:47', '2026-04-09 22:15:47', '2026-04-09 22:15:47');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('33', '5', 'EN_PREPARACION', '6', '2026-04-09 22:15:48', '2026-04-09 22:15:48', '2026-04-09 22:15:48');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('34', '6', 'EN_PREPARACION', '6', '2026-04-09 22:15:50', '2026-04-09 22:15:50', '2026-04-09 22:15:50');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('35', '7', 'EN_PREPARACION', '6', '2026-04-09 22:15:51', '2026-04-09 22:15:51', '2026-04-09 22:15:51');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('36', '5', 'LISTO', '6', '2026-04-09 22:15:52', '2026-04-09 22:15:52', '2026-04-09 22:15:52');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('37', '6', 'LISTO', '6', '2026-04-09 22:15:54', '2026-04-09 22:15:54', '2026-04-09 22:15:54');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('38', '7', 'LISTO', '6', '2026-04-09 22:15:57', '2026-04-09 22:15:57', '2026-04-09 22:15:57');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('39', '7', 'ENTREGADO', '4', '2026-04-09 22:16:21', '2026-04-09 22:16:21', '2026-04-09 22:16:21');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('40', '6', 'ENTREGADO', '5', '2026-04-09 22:16:29', '2026-04-09 22:16:29', '2026-04-09 22:16:29');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('41', '5', 'ENTREGADO', '2', '2026-04-09 22:16:38', '2026-04-09 22:16:38', '2026-04-09 22:16:38');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('42', '8', 'PENDIENTE_PAGO', NULL, '2026-04-09 22:32:18', '2026-04-09 22:32:18', '2026-04-09 22:32:18');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('43', '8', 'CREADO', NULL, '2026-04-09 22:32:32', '2026-04-09 22:32:32', '2026-04-09 22:32:32');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('44', '8', 'EN_COCINA', '6', '2026-04-09 22:32:56', '2026-04-09 22:32:56', '2026-04-09 22:32:56');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('45', '8', 'EN_PREPARACION', '6', '2026-04-09 22:33:13', '2026-04-09 22:33:13', '2026-04-09 22:33:13');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('46', '8', 'LISTO', '6', '2026-04-09 22:33:14', '2026-04-09 22:33:14', '2026-04-09 22:33:14');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('47', '8', 'ENTREGADO', '2', '2026-04-09 22:33:22', '2026-04-09 22:33:22', '2026-04-09 22:33:22');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('48', '9', 'PENDIENTE_PAGO', NULL, '2026-04-09 22:48:52', '2026-04-09 22:48:52', '2026-04-09 22:48:52');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('49', '9', 'CREADO', NULL, '2026-04-09 22:49:02', '2026-04-09 22:49:02', '2026-04-09 22:49:02');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('50', '9', 'EN_COCINA', '6', '2026-04-09 22:50:45', '2026-04-09 22:50:45', '2026-04-09 22:50:45');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('51', '9', 'EN_PREPARACION', '6', '2026-04-09 22:51:01', '2026-04-09 22:51:01', '2026-04-09 22:51:01');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('52', '9', 'LISTO', '6', '2026-04-09 22:51:13', '2026-04-09 22:51:13', '2026-04-09 22:51:13');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('53', '9', 'ENTREGADO', '2', '2026-04-09 22:51:46', '2026-04-09 22:51:46', '2026-04-09 22:51:46');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('54', '10', 'PENDIENTE_PAGO', NULL, '2026-04-09 23:10:05', '2026-04-09 23:10:05', '2026-04-09 23:10:05');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('55', '10', 'CREADO', NULL, '2026-04-09 23:10:50', '2026-04-09 23:10:50', '2026-04-09 23:10:50');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('56', '11', 'PENDIENTE_PAGO', NULL, '2026-04-09 23:41:08', '2026-04-09 23:41:08', '2026-04-09 23:41:08');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('57', '11', 'CREADO', NULL, '2026-04-09 23:41:13', '2026-04-09 23:41:13', '2026-04-09 23:41:13');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('58', '11', 'EN_COCINA', '6', '2026-04-09 23:49:33', '2026-04-09 23:49:33', '2026-04-09 23:49:33');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('59', '11', 'EN_PREPARACION', '6', '2026-04-09 23:49:35', '2026-04-09 23:49:35', '2026-04-09 23:49:35');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('60', '11', 'LISTO', '6', '2026-04-09 23:49:38', '2026-04-09 23:49:38', '2026-04-09 23:49:38');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('61', '11', 'ENTREGADO', '4', '2026-04-09 23:49:51', '2026-04-09 23:49:51', '2026-04-09 23:49:51');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('62', '12', 'PENDIENTE_PAGO', NULL, '2026-04-10 00:03:49', '2026-04-10 00:03:49', '2026-04-10 00:03:49');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('63', '12', 'CREADO', NULL, '2026-04-10 00:03:57', '2026-04-10 00:03:57', '2026-04-10 00:03:57');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('64', '12', 'EN_COCINA', '6', '2026-04-10 00:08:46', '2026-04-10 00:08:46', '2026-04-10 00:08:46');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('65', '12', 'EN_PREPARACION', '6', '2026-04-10 00:08:51', '2026-04-10 00:08:51', '2026-04-10 00:08:51');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('66', '12', 'LISTO', '6', '2026-04-10 00:08:53', '2026-04-10 00:08:53', '2026-04-10 00:08:53');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('67', '12', 'ENTREGADO', '2', '2026-04-10 00:09:04', '2026-04-10 00:09:04', '2026-04-10 00:09:04');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('68', '13', 'PENDIENTE_PAGO', NULL, '2026-04-10 00:11:19', '2026-04-10 00:11:19', '2026-04-10 00:11:19');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('69', '14', 'PENDIENTE_PAGO', NULL, '2026-04-10 00:11:23', '2026-04-10 00:11:23', '2026-04-10 00:11:23');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('70', '13', 'CREADO', NULL, '2026-04-10 00:11:27', '2026-04-10 00:11:27', '2026-04-10 00:11:27');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('71', '14', 'CREADO', NULL, '2026-04-10 00:11:31', '2026-04-10 00:11:31', '2026-04-10 00:11:31');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('72', '13', 'EN_COCINA', '6', '2026-04-10 00:22:58', '2026-04-10 00:22:58', '2026-04-10 00:22:58');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('73', '14', 'EN_COCINA', '6', '2026-04-10 00:22:59', '2026-04-10 00:22:59', '2026-04-10 00:22:59');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('74', '13', 'EN_PREPARACION', '6', '2026-04-10 00:23:07', '2026-04-10 00:23:07', '2026-04-10 00:23:07');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('75', '14', 'EN_PREPARACION', '6', '2026-04-10 00:23:12', '2026-04-10 00:23:12', '2026-04-10 00:23:12');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('76', '13', 'LISTO', '6', '2026-04-10 00:23:18', '2026-04-10 00:23:18', '2026-04-10 00:23:18');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('77', '14', 'LISTO', '6', '2026-04-10 00:23:23', '2026-04-10 00:23:23', '2026-04-10 00:23:23');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('78', '14', 'ENTREGADO', '2', '2026-04-10 00:23:38', '2026-04-10 00:23:38', '2026-04-10 00:23:38');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('79', '13', 'ENTREGADO', '4', '2026-04-10 00:24:25', '2026-04-10 00:24:25', '2026-04-10 00:24:25');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('80', '15', 'PENDIENTE_PAGO', NULL, '2026-04-10 00:25:54', '2026-04-10 00:25:54', '2026-04-10 00:25:54');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('81', '15', 'CREADO', NULL, '2026-04-10 00:26:01', '2026-04-10 00:26:01', '2026-04-10 00:26:01');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('82', '16', 'PENDIENTE_PAGO', NULL, '2026-04-10 00:26:16', '2026-04-10 00:26:16', '2026-04-10 00:26:16');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('83', '16', 'CREADO', NULL, '2026-04-10 00:26:22', '2026-04-10 00:26:22', '2026-04-10 00:26:22');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('84', '17', 'PENDIENTE_PAGO', NULL, '2026-04-10 00:27:19', '2026-04-10 00:27:19', '2026-04-10 00:27:19');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('85', '17', 'CREADO', NULL, '2026-04-10 00:27:26', '2026-04-10 00:27:26', '2026-04-10 00:27:26');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('86', '15', 'EN_COCINA', '6', '2026-04-10 00:28:15', '2026-04-10 00:28:15', '2026-04-10 00:28:15');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('87', '16', 'EN_COCINA', '6', '2026-04-10 00:28:22', '2026-04-10 00:28:22', '2026-04-10 00:28:22');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('88', '17', 'EN_COCINA', '6', '2026-04-10 00:28:27', '2026-04-10 00:28:27', '2026-04-10 00:28:27');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('89', '15', 'EN_PREPARACION', '6', '2026-04-10 00:29:00', '2026-04-10 00:29:00', '2026-04-10 00:29:00');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('90', '16', 'EN_PREPARACION', '6', '2026-04-10 00:29:05', '2026-04-10 00:29:05', '2026-04-10 00:29:05');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('91', '17', 'EN_PREPARACION', '6', '2026-04-10 00:29:09', '2026-04-10 00:29:09', '2026-04-10 00:29:09');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('92', '15', 'LISTO', '6', '2026-04-10 00:29:25', '2026-04-10 00:29:25', '2026-04-10 00:29:25');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('93', '16', 'LISTO', '6', '2026-04-10 00:29:27', '2026-04-10 00:29:27', '2026-04-10 00:29:27');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('94', '17', 'LISTO', '6', '2026-04-10 00:29:28', '2026-04-10 00:29:28', '2026-04-10 00:29:28');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('95', '17', 'ENTREGADO', '2', '2026-04-10 00:29:55', '2026-04-10 00:29:55', '2026-04-10 00:29:55');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('96', '16', 'ENTREGADO', '2', '2026-04-10 00:30:07', '2026-04-10 00:30:07', '2026-04-10 00:30:07');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('97', '15', 'ENTREGADO', '2', '2026-04-10 00:30:19', '2026-04-10 00:30:19', '2026-04-10 00:30:19');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('98', '18', 'PENDIENTE_PAGO', NULL, '2026-04-10 12:27:00', '2026-04-10 12:27:00', '2026-04-10 12:27:00');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('99', '18', 'CREADO', NULL, '2026-04-10 12:41:19', '2026-04-10 12:41:19', '2026-04-10 12:41:19');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('100', '18', 'EN_COCINA', '3', '2026-04-10 12:42:18', '2026-04-10 12:42:18', '2026-04-10 12:42:18');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('101', '18', 'EN_PREPARACION', '3', '2026-04-10 12:42:23', '2026-04-10 12:42:23', '2026-04-10 12:42:23');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('102', '18', 'LISTO', '3', '2026-04-10 12:42:29', '2026-04-10 12:42:29', '2026-04-10 12:42:29');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('103', '18', 'ENTREGADO', '2', '2026-04-10 12:42:48', '2026-04-10 12:42:48', '2026-04-10 12:42:48');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('104', '19', 'PENDIENTE_PAGO', NULL, '2026-04-11 14:08:32', '2026-04-11 14:08:32', '2026-04-11 14:08:32');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('105', '19', 'CREADO', NULL, '2026-04-11 14:08:51', '2026-04-11 14:08:51', '2026-04-11 14:08:51');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('106', '19', 'CANCELADO', '4', '2026-04-11 14:13:00', '2026-04-11 14:13:00', '2026-04-11 14:13:00');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('107', '20', 'PENDIENTE_PAGO', NULL, '2026-04-17 14:47:03', '2026-04-17 14:47:03', '2026-04-17 14:47:03');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('108', '20', 'CREADO', NULL, '2026-04-17 14:47:16', '2026-04-17 14:47:16', '2026-04-17 14:47:16');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('109', '21', 'PENDIENTE_PAGO', NULL, '2026-04-17 15:52:42', '2026-04-17 15:52:42', '2026-04-17 15:52:42');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('110', '21', 'CREADO', NULL, '2026-04-17 15:54:54', '2026-04-17 15:54:54', '2026-04-17 15:54:54');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('111', '21', 'CANCELADO', '2', '2026-04-17 16:24:17', '2026-04-17 16:24:17', '2026-04-17 16:24:17');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('112', '22', 'PENDIENTE_PAGO', NULL, '2026-04-17 21:39:48', '2026-04-17 21:39:48', '2026-04-17 21:39:48');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('113', '22', 'CREADO', NULL, '2026-04-17 21:41:05', '2026-04-17 21:41:05', '2026-04-17 21:41:05');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('114', '22', 'CANCELADO', NULL, '2026-04-17 23:16:10', '2026-04-17 23:16:10', '2026-04-17 23:16:10');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('115', '23', 'PENDIENTE_PAGO', NULL, '2026-04-21 15:17:42', '2026-04-21 15:17:42', '2026-04-21 15:17:42');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('116', '23', 'CREADO', NULL, '2026-04-21 15:18:24', '2026-04-21 15:18:24', '2026-04-21 15:18:24');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('117', '23', 'EN_COCINA', '6', '2026-04-21 15:20:23', '2026-04-21 15:20:23', '2026-04-21 15:20:23');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('118', '23', 'EN_PREPARACION', '6', '2026-04-21 15:20:46', '2026-04-21 15:20:46', '2026-04-21 15:20:46');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('119', '23', 'LISTO', '6', '2026-04-21 15:20:51', '2026-04-21 15:20:51', '2026-04-21 15:20:51');
INSERT INTO public.historial_estado_pedidos (id, pedido_id, estado, usuario_id, fecha, created_at, updated_at) VALUES ('120', '23', 'ENTREGADO', '4', '2026-04-21 15:21:29', '2026-04-21 15:21:29', '2026-04-21 15:21:29');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('5', '6', '2', 'DISPONIBLE', 'bWVzYTo1Ok5mWURkSUxSQnpTTk9DMXc=', 't', '2026-03-24 20:27:09', '2026-04-08 01:37:27');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('4', '4', '4', 'DISPONIBLE', 'bWVzYTo0OnBXMDBnSzBlNW1EVUJ3aGQ=', 't', '2026-03-24 20:27:01', '2026-04-08 01:53:23');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('1', '1', '1', 'OCUPADA', 'bWVzYToxOlVtM3RjUll3cVQ0eEVlZnA=', 't', '2026-03-12 04:21:07', '2026-04-21 19:41:44');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('3', '3', '6', 'DISPONIBLE', 'bWVzYTozOjZod3lvcHlScDl3YkJxRkw=', 't', '2026-03-24 20:26:50', '2026-04-08 04:31:56');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('11', '12', '1', 'OCUPADA', 'bWVzYToxMTpKUmdIY2JqN1NPYzY0VEZ1', 't', '2026-03-24 20:28:13', '2026-04-23 13:46:05');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('10', '11', '4', 'DISPONIBLE', 'bWVzYToxMDo4QmFkVzFvSkVoeWI3MWtp', 't', '2026-03-24 20:28:02', '2026-04-09 21:46:24');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('2', '2', '3', 'OCUPADA', 'bWVzYToyOkU4R01JcExxYzR6NkhHM2o=', 't', '2026-03-19 21:14:30', '2026-04-27 12:40:06');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('12', '13', '1', 'DISPONIBLE', 'bWVzYToxMjpDenQ3RzZhVFlXcGlVZnht', 't', '2026-03-24 20:28:30', '2026-04-09 22:16:59');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('13', '14', '1', 'DISPONIBLE', 'bWVzYToxMzpQa3Q5V2EzMm1KWEc4eHcy', 't', '2026-04-09 16:14:07', '2026-04-09 22:52:33');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('9', '10', '4', 'DISPONIBLE', 'bWVzYTo5OmNGV2pqS1VHaGYzWGJ4RXo=', 't', '2026-03-24 20:27:54', '2026-04-03 23:19:37');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('8', '9', '2', 'DISPONIBLE', 'bWVzYTo4OjNWM0s4akFKdDNsUVZaS1E=', 't', '2026-03-24 20:27:44', '2026-04-03 23:19:41');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('6', '7', '3', 'DISPONIBLE', 'bWVzYTo2Ok9yZDJWZk1XSlZTaWZBVFI=', 't', '2026-03-24 20:27:20', '2026-04-03 23:19:45');
INSERT INTO public.mesas (id, numero, capacidad, estado, qr_codigo, qr_activo, created_at, updated_at) VALUES ('7', '8', '3', 'DISPONIBLE', 'bWVzYTo3Om80emtncjV5RDZpbmdNZ1k=', 't', '2026-03-24 20:27:34', '2026-04-03 23:19:50');
INSERT INTO public.migrations (id, migration, batch) VALUES ('1', '2024_01_01_000001_create_roles_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('2', '2024_01_01_000002_create_usuarios_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('3', '2024_01_01_000003_create_mesas_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('4', '2024_01_01_000004_create_sesiones_mesa_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('5', '2024_01_01_000005_create_sub_sesiones_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('6', '2024_01_01_000006_create_categorias_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('7', '2024_01_01_000007_create_productos_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('8', '2024_01_01_000008_create_inventarios_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('9', '2024_01_01_000009_create_movimientos_inventario_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('10', '2024_01_01_000010_create_pedidos_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('11', '2024_01_01_000011_create_historial_estado_pedidos_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('12', '2024_01_01_000012_create_detalle_pedidos_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('13', '2024_01_01_000013_create_pagos_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('14', '2024_01_01_000014_create_notificaciones_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('15', '2024_01_01_000015_create_sesiones_table', '1');
INSERT INTO public.migrations (id, migration, batch) VALUES ('16', '2024_01_01_000016_reestructurar_sesiones', '2');
INSERT INTO public.migrations (id, migration, batch) VALUES ('17', '2024_01_01_000001_add_token_to_sesiones_mesa_and_create_carrito_items', '3');
INSERT INTO public.migrations (id, migration, batch) VALUES ('18', '2026_04_09_153532_add_imagen_to_productos_table', '4');
INSERT INTO public.migrations (id, migration, batch) VALUES ('19', '2026_04_26_135243_create_report_schedules_table', '5');
INSERT INTO public.migrations (id, migration, batch) VALUES ('20', '2026_04_26_220325_create_zona_coberturas_table', '6');
INSERT INTO public.migrations (id, migration, batch) VALUES ('21', '2026_04_26_220401_create_domiciliarios_table', '6');
INSERT INTO public.migrations (id, migration, batch) VALUES ('22', '2026_04_26_224743_create_barrios_table', '7');
INSERT INTO public.migrations (id, migration, batch) VALUES ('23', '2026_04_27_000549_create_barrio_domiciliario_table', '8');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('1', '1', 'NEQUI', '3500.00', 'COMPLETADO', 'WMP-1-WATFGYOG', NULL, '2026-04-09 16:24:32', '2026-04-09 16:24:36', '3112533941', 'warzonemobile8294q@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('2', '2', 'NEQUI', '8600.00', 'FALLIDO', 'WMP-2-JJPWOV2J', NULL, '2026-04-09 20:46:04', '2026-04-09 20:46:24', '3112533941', 'warzonemobile8294q@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('3', '2', 'NEQUI', '8600.00', 'COMPLETADO', 'WMP-2-ZLXVEC33', NULL, '2026-04-09 20:46:46', '2026-04-09 20:46:51', '3112533941', 'warzonemobile8294q@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('4', '3', 'EFECTIVO', '3400.00', 'CANCELADO', NULL, NULL, '2026-04-09 21:06:53', '2026-04-09 21:07:24', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('5', '4', 'EFECTIVO', '4300.00', 'PENDIENTE', NULL, NULL, '2026-04-09 21:17:32', '2026-04-09 21:17:32', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('7', '6', 'NEQUI', '3500.00', 'COMPLETADO', 'WMP-6-OGME9HUE', NULL, '2026-04-09 22:11:31', '2026-04-09 22:15:02', '3112533941', 'leiderfabianramoscano99@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('6', '5', 'NEQUI', '3500.00', 'COMPLETADO', 'WMP-5-B2QMLKOD', NULL, '2026-04-09 22:10:34', '2026-04-09 22:15:09', '3112533941', 'warzonemobile8294q@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('8', '7', 'NEQUI', '4300.00', 'COMPLETADO', 'WMP-7-NPAYDVQ0', NULL, '2026-04-09 22:13:27', '2026-04-09 22:15:23', '3112533941', 'sih306441@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('9', '8', 'NEQUI', '3500.00', 'COMPLETADO', 'WMP-8-FMSMCBF7', NULL, '2026-04-09 22:32:26', '2026-04-09 22:32:32', '3112533941', 'leiderfabianramoscano99@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('10', '9', 'EFECTIVO', '3500.00', 'COMPLETADO', NULL, NULL, '2026-04-09 22:49:02', '2026-04-09 22:49:02', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('11', '10', 'EFECTIVO', '3500.00', 'COMPLETADO', NULL, NULL, '2026-04-09 23:10:50', '2026-04-09 23:10:50', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('12', '11', 'EFECTIVO', '3500.00', 'COMPLETADO', NULL, NULL, '2026-04-09 23:41:13', '2026-04-09 23:41:13', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('13', '12', 'EFECTIVO', '3500.00', 'COMPLETADO', NULL, NULL, '2026-04-10 00:03:57', '2026-04-10 00:03:57', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('14', '13', 'EFECTIVO', '4500.00', 'COMPLETADO', NULL, NULL, '2026-04-10 00:11:27', '2026-04-10 00:11:27', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('15', '14', 'EFECTIVO', '2500.00', 'COMPLETADO', NULL, NULL, '2026-04-10 00:11:31', '2026-04-10 00:11:31', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('16', '15', 'EFECTIVO', '3500.00', 'COMPLETADO', NULL, NULL, '2026-04-10 00:26:01', '2026-04-10 00:26:01', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('17', '16', 'EFECTIVO', '1300.00', 'COMPLETADO', NULL, NULL, '2026-04-10 00:26:22', '2026-04-10 00:26:22', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('18', '17', 'EFECTIVO', '4300.00', 'COMPLETADO', NULL, NULL, '2026-04-10 00:27:26', '2026-04-10 00:27:26', NULL, NULL);
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('19', '18', 'NEQUI', '4300.00', 'PENDIENTE', 'WMP-18-JISZHJFB', NULL, '2026-04-10 12:27:38', '2026-04-10 12:27:38', '3112533941', 'juandavidnt4@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('20', '18', 'NEQUI', '4300.00', 'COMPLETADO', 'WMP-18-WRHW2Q8L', NULL, '2026-04-10 12:41:16', '2026-04-10 12:41:19', '3112533941', 'juandavidnt4@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('21', '19', 'NEQUI', '57000.00', 'REEMBOLSADO', 'WMP-19-LARFXEOM', '2026-04-11 14:13:00', '2026-04-11 14:08:47', '2026-04-11 14:13:00', '3112533941', 'leiderfabianramoscano99@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('22', '20', 'NEQUI', '12000.00', 'COMPLETADO', 'WMP-20-RTQPCP6W', NULL, '2026-04-17 14:47:12', '2026-04-17 14:47:16', '3112533941', 'leiderfabianramoscano99@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('23', '21', 'NEQUI', '12000.00', 'REEMBOLSADO', 'WMP-21-DHBHT5B9', '2026-04-17 16:24:17', '2026-04-17 15:53:01', '2026-04-17 16:24:17', '3112533941', 'leiderfabianramoscano99@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('24', '22', 'NEQUI', '3500.00', 'REEMBOLSADO', 'WMP-22-FTAEFRWI', '2026-04-17 23:16:10', '2026-04-17 21:40:41', '2026-04-17 23:16:10', '3112533941', 'leiderfabianramoscano99@gmail.com');
INSERT INTO public.pagos (id, pedido_id, metodo_pago, monto, estado, referencia_transaccion, fecha_reembolso, created_at, updated_at, telefono, email) VALUES ('25', '23', 'NEQUI', '12000.00', 'COMPLETADO', 'WMP-23-WRSHCHDV', NULL, '2026-04-21 15:18:11', '2026-04-21 15:18:24', '3112533941', 'leiderfabianramoscano99@gmail.com');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('1', '1', '2', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-09 16:24:27', '2026-04-09 16:28:45');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('2', '3', '2', 'ENTREGADO', '8600.00', NULL, NULL, '2026-04-09 20:45:39', '2026-04-09 20:51:01');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('3', '6', '2', 'CANCELADO', '3400.00', '2026-04-09 21:07:24', 'no lo quiero', '2026-04-09 21:06:47', '2026-04-09 21:07:24');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('14', '22', '2', 'ENTREGADO', '2500.00', NULL, NULL, '2026-04-10 00:11:23', '2026-04-10 00:23:38');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('13', '21', '4', 'ENTREGADO', '4500.00', NULL, NULL, '2026-04-10 00:11:19', '2026-04-10 00:24:25');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('4', '6', '2', 'ENTREGADO', '4300.00', NULL, NULL, '2026-04-09 21:17:24', '2026-04-09 22:09:34');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('7', '15', '4', 'ENTREGADO', '4300.00', NULL, NULL, '2026-04-09 22:12:03', '2026-04-09 22:16:21');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('6', '14', '5', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-09 22:11:19', '2026-04-09 22:16:29');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('5', '13', '2', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-09 22:10:20', '2026-04-09 22:16:38');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('17', '25', '2', 'ENTREGADO', '4300.00', NULL, NULL, '2026-04-10 00:27:19', '2026-04-10 00:29:55');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('16', '24', '2', 'ENTREGADO', '1300.00', NULL, NULL, '2026-04-10 00:26:16', '2026-04-10 00:30:07');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('8', '16', '2', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-09 22:32:18', '2026-04-09 22:33:22');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('15', '23', '2', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-10 00:25:54', '2026-04-10 00:30:19');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('9', '17', '2', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-09 22:48:52', '2026-04-09 22:51:46');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('10', '18', '2', 'CANCELADO', '3500.00', '2026-04-09 23:12:23', 'Sesión de mesa cerrada (manual)', '2026-04-09 23:10:05', '2026-04-09 23:12:23');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('18', '26', '2', 'ENTREGADO', '4300.00', NULL, NULL, '2026-04-10 12:27:00', '2026-04-10 12:42:48');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('11', '19', '4', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-09 23:41:08', '2026-04-09 23:49:51');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('19', '28', '4', 'CANCELADO', '57000.00', '2026-04-11 14:13:00', 'Cancelado por el mesero desde el panel.', '2026-04-11 14:08:32', '2026-04-11 14:13:00');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('20', '32', '4', 'CANCELADO', '12000.00', '2026-04-17 15:45:08', 'Sesión de mesa cerrada (manual)', '2026-04-17 14:47:02', '2026-04-17 15:45:08');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('12', '20', '2', 'ENTREGADO', '3500.00', NULL, NULL, '2026-04-10 00:03:49', '2026-04-10 00:09:04');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('21', '33', '2', 'CANCELADO', '12000.00', '2026-04-17 16:24:17', 'Cancelado por el mesero desde el panel.', '2026-04-17 15:52:42', '2026-04-17 16:24:17');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('22', '36', '4', 'CANCELADO', '3500.00', '2026-04-17 23:16:10', 'Cancelado por el cliente.', '2026-04-17 21:39:48', '2026-04-17 23:16:10');
INSERT INTO public.pedidos (id, sesion_mesa_id, mesero_id, estado, total, fecha_cancelacion, motivo_cancelacion, created_at, updated_at) VALUES ('23', '37', '4', 'ENTREGADO', '12000.00', NULL, NULL, '2026-04-21 15:17:42', '2026-04-21 15:21:29');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('1', 'Coca Cola', 'Bebida Refrescante', '3500.00', 't', '1', '2026-04-09 16:12:15', '2026-04-09 16:13:12', 'productos/htAva7trhNJaPcmHxwWH6s6sdQ0GKebPR69df1Hm.webp');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('3', 'Jugo de Fresa', 'Fresas frescas', '4300.00', 't', '3', '2026-04-09 16:46:20', '2026-04-09 16:46:20', 'productos/5d1CaUCSz7vosTstr1x3f4sk4UhK6KZnNHGjWPe5.webp');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('4', 'Jugo de Mango', 'jugo delicioso', '4300.00', 't', '3', '2026-04-09 16:48:46', '2026-04-09 16:48:46', 'productos/df0VmnmpbOmO99O56VLMAWZNL6Aj7gqoRWbCtNoy.webp');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('5', 'Jugo de Mora', 'Refrescante Jugo', '4300.00', 't', '3', '2026-04-09 16:55:43', '2026-04-09 16:55:43', 'productos/U5fNSuM7inUKLYRjJIBDaElZOcsCzIuwSwHrzD4H.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('6', 'Jugo de Piña', 'Refrescante', '4300.00', 't', '3', '2026-04-09 16:58:35', '2026-04-09 16:58:35', 'productos/HcF65OuNYfpfc2VbCgUYGJBtXcPqpknyZvEKb9Mc.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('7', 'Jugo de Lulo', 'Refrescante', '4300.00', 't', '3', '2026-04-09 17:01:36', '2026-04-09 17:01:36', 'productos/Zxo5mdXJ2TiabDZxPeTVGF1t0kQqz3lNEzZelsw6.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('8', 'Gaseosa Manzana', 'Refrescante', '3400.00', 't', '1', '2026-04-09 17:03:26', '2026-04-09 17:03:26', 'productos/wVLIfTGmaYicp1rZsuV9zNuwj0G66jgUQdpcHyvB.png');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('9', 'Speed', 'Refrescante', '2500.00', 't', '1', '2026-04-09 17:09:34', '2026-04-09 17:09:34', 'productos/055TIMtMY3GlNesRg3z5BKAyIEpnOd0inh1xXlIM.webp');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('10', 'Cafe', 'Bebida Caliente', '1300.00', 't', '2', '2026-04-09 17:14:06', '2026-04-09 17:14:06', 'productos/IDf0KhCHyDjwSdYsWVWBvU3pyfO0OcoGIQwyYqbp.webp');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('11', 'Café con Leche', 'Bebida Cálida', '2000.00', 't', '2', '2026-04-09 17:18:43', '2026-04-09 17:18:43', 'productos/ELUek6mitklPLofMARNQ47q20h1J6AvIzZ0bueH7.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('12', 'Milo', 'Bebida Caliente', '1700.00', 't', '2', '2026-04-09 17:25:00', '2026-04-09 19:58:00', 'productos/g2P4KaXybElYNWHyWsVq93ylMKz9uPrIpvIKr1di.webp');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('2', 'Jugo Hit de Mango', 'Bebida sabor a Mango', '4500.00', 't', '1', '2026-04-09 16:31:56', '2026-04-10 13:44:59', 'productos/aY8MJEoI09sUZby5xmyv6qJpm9u23J616mt3DjJj.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('14', 'Capuccino', 'Espresso con leche vaporizada y espuma suave', '6000.00', 't', '2', '2026-04-10 15:27:34', '2026-04-10 15:27:34', 'productos/c29a48cf-4317-41ef-9839-cb0cc00b1db0.Pe-S01F7qQmyT8Md6QyK_wHaE8');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('15', 'Té Verde con Miel', 'Infusión relajante con miel natural', '4000.00', 't', '2', '2026-04-10 15:27:36', '2026-04-10 15:27:36', 'productos/d64090d6-55ca-451b-9f2e-f459d07e64e0.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('13', 'Café Espresso', 'Café negro intenso preparado al momento', '4500.00', 't', '2', '2026-04-10 15:27:33', '2026-04-10 15:28:20', 'productos/6bOKKdLtZLLjuh3rAgMeUNBPXaZF3Plaviujg8PD.webp');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('30', 'Helado de Fresa', NULL, '4700.00', 'f', '4', '2026-04-15 21:21:35', '2026-04-27 12:15:32', 'productos/J7Mnzt93gVpBPNhOzbvIoMPJozcpyslxwREQuV47.png');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('17', 'Hamburguesa Clásica', 'Carne de res, lechuga, tomate y salsa especial', '12000.00', 't', '6', '2026-04-12 18:42:10', '2026-04-12 18:42:10', 'productos/cf5f8562-ceba-40c5-94c0-95bba67e8e37.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('18', 'Hamburguesa Doble', 'Doble carne, queso, lechuga y salsa BBQ', '16000.00', 't', '6', '2026-04-12 18:42:10', '2026-04-12 18:42:10', 'productos/a34c86f7-d18c-4558-b769-c53dc4bb9abc.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('19', 'Hamburguesa con Queso', 'Carne de res con queso cheddar derretido', '13000.00', 't', '6', '2026-04-12 18:42:11', '2026-04-12 18:42:11', 'productos/00eaa995-893d-4ca7-9e1f-8ece9cf38c2f.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('20', 'Salchipapa Tradicional', 'Papas fritas con salchichas y salsas', '10000.00', 't', '6', '2026-04-12 18:42:12', '2026-04-12 18:42:12', 'productos/692bf2ca-c5ff-404e-82a9-e05252bbd741.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('21', 'Salchipapa Especial', 'Papas, salchicha, pollo, queso y salsas', '15000.00', 't', '6', '2026-04-12 18:42:13', '2026-04-12 18:42:13', 'productos/6de009d4-7cad-45d9-8b44-c891b849d88d.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('22', 'Perro Caliente', 'Pan con salchicha, papas trituradas y salsas', '9000.00', 't', '6', '2026-04-12 18:42:14', '2026-04-12 18:42:14', 'productos/523f8679-85d0-4c68-8030-836c1a7536d0.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('27', 'Sandwich de Pollo', 'Pan con pollo, lechuga y mayonesa', '9500.00', 't', '6', '2026-04-12 18:42:16', '2026-04-12 18:45:47', 'productos/0PhOlPiGrd68qJOxUlcP97Rxpww1N3OvKZct3Bg1.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('26', 'Arepa con Carne', 'Arepa rellena de carne desmechada', '10000.00', 't', '6', '2026-04-12 18:42:15', '2026-04-12 18:45:58', 'productos/G40ByDb5dxikjUbe5v4ZiDyHogvspMiZM9UmXkRJ.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('25', 'Nuggets de Pollo', 'Trozos de pollo apanado con salsa', '11000.00', 't', '6', '2026-04-12 18:42:15', '2026-04-12 18:46:12', 'productos/Kfxx9fW1P1vhAgaHDpNoGpwzARZbvsncuvOhiXrn.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('23', 'Perro Especial', 'Perro con queso, tocineta y salsas', '12000.00', 't', '6', '2026-04-12 18:42:15', '2026-04-12 18:46:24', 'productos/yk0khAXW9G3EKLdRvKbpd7OXn0GiEVazIkjuvgOv.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('24', 'Papas Fritas', 'Porción de papas fritas crujientes', '6000.00', 't', '6', '2026-04-12 18:42:15', '2026-04-12 18:46:38', 'productos/RUcSQjnvyTdezXYi2HHOlmdyWGIjRjEFXlyTG6F3.png');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('16', 'Helado de Fresa', 'Delicioso Helado de Fresas', '4700.00', 't', '4', '2026-04-11 13:31:25', '2026-04-12 18:47:47', 'productos/3pFqEjy1A2L8rrEF8j51NC5R0qnizXcK1YFmh9y6.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('29', 'Pizza', 'Deliciosa', '15000.00', 't', '6', '2026-04-12 18:57:44', '2026-04-12 18:57:44', 'productos/9005634e-2b2d-4d05-941d-52fac687f282.jpg');
INSERT INTO public.productos (id, nombre, descripcion, precio, estado, categoria_id, created_at, updated_at, imagen) VALUES ('28', 'Ensalada Simple', 'Ensalada fresca como acompañamiento', '7000.00', 't', '7', '2026-04-12 18:57:43', '2026-04-12 18:58:32', 'productos/ItmFybqxtN7dR3j7XQ1ysgomT8qWp5P9T8ipjy2o.jpg');
INSERT INTO public.report_schedules (id, active, frequency, "time", days, month_days, custom_config, method, recipients, whatsapp_number, sections, last_run_at, next_run_at, created_at, updated_at) VALUES ('1', 't', 'weekly', '09:00:00', '["L","X","V"]', '[]', '{"value":2,"unit":"days","start":"2026-04-26","end":null}', 'whatsapp', '["leiderfabianramoscano99@gmail.com"]', '+57 3112533941', '["kpis","chart","categories","products"]', NULL, '2026-04-27 09:00:00', '2026-04-26 14:02:59', '2026-04-26 14:02:59');
INSERT INTO public.roles (id, nombre, created_at, updated_at) VALUES ('3', 'mesero', NULL, NULL);
INSERT INTO public.roles (id, nombre, created_at, updated_at) VALUES ('2', 'cocina', NULL, NULL);
INSERT INTO public.roles (id, nombre, created_at, updated_at) VALUES ('1', 'administrador', NULL, NULL);
INSERT INTO public.roles (id, nombre, created_at, updated_at) VALUES ('4', 'cliente', NULL, NULL);
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('168', '5', 'MtcRbcZjQPepbCifg2XBXGzsZulSOJNo02Lrk9uWw1Mvj9cNPHQxkVehTRQgEbSmWObkxAzBXHkD38KB', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 15:19:15', 'f', '2026-04-21 15:19:09', '2026-04-21 15:19:15');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('3', '2', 'IdAdfruXYktVQKjyHSmlyYJMfWHe5uapwIp8FX97eCIkI20spO5UmzVb3i9yiFbhrfBhq7fzfeM9C7N3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-19 18:28:24', 'f', '2026-03-12 18:28:24', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('9', '2', 'ApIluoTLqrdIBkN5zl0rauxfzpYN86YbFDChjny497USdO79FdCYcE4J0BrPsQYJLK4xLxbpvuy0hMiJ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-20 20:30:51', 'f', '2026-03-13 20:30:51', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('73', '2', 'SGimtdGbg30Y9veqz3Ck6AVrDnQN0WueJdl5kZx6RzOwBpPH6X0ypdftUf6OeRqZo5HVy241I60ZI1fy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-12 01:17:57', 'f', '2026-04-05 00:20:44', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('130', '5', 'XdIJ1CjaxY6B4z5ryiKYIxQATUvEJEyVhGB9giwLdIKg672of6wnwv0zzDw6HxOvwJM7Vt1v0rrP0H0V', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 23:43:39', 'f', '2026-04-09 13:28:57', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('17', '3', 'nUwilQOKp7uuauQj1LZPMFX0GcvbFMSccZhucAQkd7dsyCnGdFuh4Af9x6AhQLCe6FSWkLgRTaDuRS85', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 21:50:17', 'f', '2026-03-19 21:50:17', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('13', '3', 'T2SvduW7Rz1DjtGohb3SflmX5D6AZvjHvNjtq9He6p1ouBqAg813mMJfDZeIgOv4r4xBUxn5gpaBzVVt', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 19:48:05', 'f', '2026-03-17 19:48:05', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('46', '5', 'EEv2RHTEpdzXltTVAWQsiwY9vr985vHgM6h3KnkNniDAQZesWPWM4VTY9XXT0D9rg4SH5pDIL7YCspry', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 21:08:40', 'f', '2026-03-26 21:08:40', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('83', '5', 'uHhY7aCU4Ia2BtHw81O0siBOCIrxKiydEfZBjQexHqJ6mBajqmaiUk3BW9XVuLLSTOD0I12cSTdsSzIR', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 17:51:39', 'f', '2026-04-06 17:51:39', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('5', '3', 'LKxcrt8IDpKqafFLD2UkDKUOj6ftq3D8h3Di4KZHMqZhMrQKX4iubleA6dU9PKj9wKbWpKPSnYKkZntx', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-19 18:37:27', 'f', '2026-03-12 18:37:27', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('126', '5', 'YozXQGsZdbhn6LRw0r0tzlH5rjLCUkfCJHApjN8q1DydtYcc4TBCPYyHIESgNj5ebcSRrOj4imV7mDPw', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 22:43:39', 'f', '2026-04-08 22:41:20', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('1', '1', 'ust4Ps26JcW1FEo6h8olmpSNRY8y83gLvbiHTJKyrLGhFrzpj2Q1D1fYF5trME9wwlH5OJyCr6jjQ3Wy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-19 04:00:27', 'f', '2026-03-12 04:00:27', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('2', '1', 'oIdw2CsGftTqDxtPBRdDPhbtnXrjFtLTe2sCUB9NcDPrpNiJpsGrnCRvyswhCQe2jVjvWjM94V8Ak2ve', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-19 18:24:07', 'f', '2026-03-12 18:24:07', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('124', '4', 'S4HRpyyCdUCA9NoynLxmRxwuwYQLy5EdJOfCDaRH93Rlrt2Sr5rNVsH6Bzuf6JDMoSQzKXYSSkM9Ppmh', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 22:41:03', 'f', '2026-04-08 22:31:24', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('113', '1', 'UudVIPVlNqx3f5LF4xf4mpUTl0yUTsuROaYqskCPn7YPnXVluLeNKNgV9gzNa8E2hefLffIlbkVnvdqa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 22:24:22', 'f', '2026-04-08 18:49:54', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('114', '6', '6vLu0rtvFlnu8b5r11MLOosTctCD93TOmBtYFLrgnHfZly6aZVviVkXs5ms0EQSkcwylEO086B0iSg3d', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 21:14:52', 'f', '2026-04-08 21:13:06', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('122', '6', 'Lmb7gmf4ZRwyR2zUQqe8KUNxNojWL7391DCCCm2yvNicZ9PtuGEEuEieKvyfsMXcwLZQ8coz3fkxOl5e', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 22:43:43', 'f', '2026-04-08 21:46:41', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('8', '3', 'UkCCClloTM3xKIsUlg0vwOcKlKopPo9PeFL6BLhqAeLWf58BqKSlvieg8Z4ICLbl2B04QubULm4G11zI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-20 20:29:45', 'f', '2026-03-13 20:29:45', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('42', '4', 'eTzUFV3Qb2a5giIgFx8c6upgAwqZhr9aHoqcdmdxO1sZoL5kL6Fue7DtvhaSASUwYEFlFRDh6wuLUKjQ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 20:59:54', 'f', '2026-03-26 20:59:54', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('139', '1', 'Rnj1zgnujTyKWkVKoPXdX8lhkHDVpricAXrWTptldCJUUh2QOdieDX1n12AYexoNoycq2KQxjlDjddZc', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 23:43:38', 'f', '2026-04-09 23:40:32', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('35', '1', 'ceOLDy9xfpQpxyZt0XL8zPoIysK4BYKAf5d87gSx8eseNiIwn1nX7PnJZQFECsg3yLUhcrkVs3LLZSAw', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 17:48:28', 'f', '2026-03-26 17:48:28', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('178', '1', 'Q2vVytjGow1uSgcaHLAyUejBePMwdJge2x9ToriLBjNyNOshqTesUL2cubmBrTq2hGGz204JEoDoaG4B', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-30 13:45:45', 'f', '2026-04-23 13:45:17', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('184', '1', 'Y5K2mZ1fbcMHThJ2MfO555mRn6EGzTMJ6bNcX70r6zA8xUmISUYyoQYzvJLShQKsVtuwS4lXMlZZ0wpP', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-02 22:44:38', 'f', '2026-04-25 20:33:15', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('156', '1', 'X3IkINYJJ3xDSoizs4e6f1gcPGxn4QZLdmzRJq4VT3p6T7UalLVPL52F6OErQeL6p1MrdCdMLDC1bwjj', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-24 16:24:43', 'f', '2026-04-17 14:44:22', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('71', '5', 'ES5r00LHAE8pI6khYZK32GJwLUW4XEBy8JjnqhHoaMvHf1660PKb2ndqPRbks1Ow4aQ96EjWUW4sRL0a', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-10 22:07:15', 'f', '2026-04-03 22:06:57', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('152', '1', 'LsPuAJfBnmTV6BAxHgwOOMKi8EUnvw4vVEdg82f7dOanlsxP2AJPvf9r32mW9maig7du8DKGJ8sh5XXv', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-21 21:10:04', 'f', '2026-04-14 20:40:56', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('26', '5', '8bCpXHY7oXpKIWTOHOuL5zF2xV8cSwY07mobU6eRgFRsVHBdR33Mj6Ife9AluBY0UIXdFnTaaSF5yPNe', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 20:38:25', 'f', '2026-03-24 20:38:25', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('187', '1', '4AOl15HR0lRKyZ3QlBpEy27T2vjcDhG7xTr58kKjHtdQkPyjCnILI7wiMwIx1Hh50cDUAUyXGO96SZEZ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-04 00:49:23', 'f', '2026-04-26 23:52:06', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('24', '3', 'saYKaeSzOwIJnWk5Jc8y0ittdiekWElQDCycLRB0d1QZYPscMkkGv0AQ9iMflRBfVN0MEe5K9VbbbzCz', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 20:21:44', 'f', '2026-03-24 20:21:44', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('131', '6', 'PCw7OcX3PmtbTnxF7otGyPeCsIPcaICTuSv7MEbRygDOOcNEuR3Oa2iI0P1Mv6wajV4M9nv1bYLaZTfq', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 23:43:44', 'f', '2026-04-09 13:29:19', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('188', '1', 'wXCGQztBYjHAEPD3SKtijIEfyNtD2yCjQcYwRaiYa2DJXSuTzrS9FCSka8ussKI9mLeQrYcuofuHVLEx', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-04 12:40:01', 't', '2026-04-27 12:05:44', '2026-04-27 12:40:01');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('30', '3', '9Vb3BpdqQCrV7MYkFcaSGIHeZiDzUZiZFWPQBD61VGUkZUfKKcpxI0D8fBzjyrT4zjDjzJ1D2O3XfR0u', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-01 18:46:50', 'f', '2026-03-25 18:46:50', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('68', '3', 'TGXL6hpAOqLMX67xVaHspggdA6LXOiMLDIDtm133R7Zkg9tMoeQw1tcQbsPS3hgTeOsjp4EKl83KJ8Tc', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-10 04:01:05', 'f', '2026-04-03 02:40:54', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('141', '6', '7NnoammsmiPZnV2pPHtdTcmCdQ4gcp1E8L0CIHRXVjTGLgS1J2isdafmOF5xqE3LFO8aZ1nZLueZfm44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-17 00:32:08', 'f', '2026-04-09 23:49:22', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('105', '4', '8y9B4pXgYTTjSMGimQSAav1UFs7QZGz1LCkOx9B2cCHIK4B0702Dx32uYL5Pq6M3RS7knM93sFp7akkg', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:27:24', 'f', '2026-04-08 04:27:23', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('175', '4', '61XOoxmwRrEL58gKUWl4KGYZWMT1v01PubRoyfkQ8oRAJVveCRM4aVeUvyskNq9WBuzD7dlxSQu5JQ85', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-30 13:22:29', 'f', '2026-04-23 13:22:28', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('90', '4', '0T4NeJfO4a2pdRYAbg5i1OuYeWSQsTYypEGOb1pfN8XqXRso2J1IZmCo5kcBqRZjq210U0lgWpPHyTkI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 20:02:25', 'f', '2026-04-07 20:02:25', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('106', '4', 'A8HH0xy6A1pwJdY2vXOZmUGNqovxjmG63OSfr0zj46ENIEnuWAcktqyvzLmuGoP9oNyxyu81yAv2vaSq', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 06:07:34', 'f', '2026-04-08 04:30:14', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('158', '2', '4ALhHfGuw0Ja58WiuF2pT6gsb3iVDKiou8w2E4JgHgP6XjgN1oYKa1ZWkSgEZAVNe93lBzUdyw9wbOKT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-24 19:57:32', 'f', '2026-04-17 15:56:19', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('125', '2', 'Zp2SBEVf2LX1O3CPUk0j0yz3CVCwb1TSq8vS2VIqeiqII2JO3sHBVQUBfXFTqVrMlwC62YRy4GpBTXdp', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 22:41:14', 'f', '2026-04-08 22:41:09', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('10', '2', 'jmv35zvfB3dOdDSyYjRuhsa8qBpoEZnVNvdZOEpiqxrDiTxrYE3gTDyKTFMqecJBoKtApqT4SzDq0wva', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 18:10:17', 'f', '2026-03-17 18:10:17', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('12', '2', 'IfYqJNDFMQzC4huYwsAyKPV1LAIuzJAjW8bLYuT8Qpr4txDofB54BgBxHoq51pzR3r3sOfFYM2O3JIwV', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 19:47:26', 'f', '2026-03-17 19:47:26', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('27', '4', 'dHuPKOKRyYDxB4XqReBNyRpoBi3WHogTjDwGDV3VQwNZt5SP1KQyoGJ3YXsm9Moi0LHpN1JX5IFVIFiY', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 20:39:39', 'f', '2026-03-24 20:39:39', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('169', '4', 'RV8uixaQ3040D10a0blROMLONIytCXCm7sVidQEBqBevR231azwc5AbvxUZVr3BjQOsZNTC8MwUFqmq5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 17:45:41', 'f', '2026-04-21 15:19:22', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('7', '1', 'OTzh8tIrASQbUJteqjGQlRU0XSIjgnQzI5cdW05ZJFl1KIAHQHy724s1tTe9Qi1s6f4Qgr9soQBlTvNS', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-20 20:29:04', 'f', '2026-03-13 20:29:04', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('11', '1', 'dfLh2eElERZWKA3AL0TRjjFEKkUKiofD0cYUmeOI1GHFqHqcGQvJ7YGa6UdfWdSOYEYAmSo2fwINzHOF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 19:45:27', 'f', '2026-03-17 19:45:27', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('57', '4', 'O5LAJlo1sarWlwwN91u5Fnm878fvt7tCu7db1ctPzi1M7Scz2DGrRVbCANEO47Ttqq5hMiSky4bC4kdH', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 21:08:00', 'f', '2026-03-30 21:08:00', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('47', '2', 'aJUTjxQJGhEgTNwPqzuzGfQTXhSOgFmMdXVpl8dzJB7T48UhGM3gIBk0zrHPZz1LFix1oonPPdOzpp7i', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 21:10:52', 'f', '2026-03-26 21:10:52', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('166', '2', 'CqVEnkk2eIrH72sKuP5KTdLD8RlVFGwSfH9Z9ycCDmSQGIeU6Byl5SOXHXy6WtCTL9lsYQBo978zfaUi', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 15:19:01', 'f', '2026-04-21 15:16:29', '2026-04-21 15:19:01');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('89', '5', 'zElxlovwOS6G8WBUZMAOdDEtA5IxVy7hkqRnb32rlkpv97LtHeE2YtLeEe8r8jl83c0kuaWrjPTJanmr', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 20:00:15', 'f', '2026-04-07 20:00:15', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('4', '3', 'BsyqLr3Zyg1Us2Lnf4lKWLB22WBMzoEXmECe76dpeC60gfe1fdAkfs0sy31DBhdwGKyBJwT3krH9t9VY', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-19 18:28:57', 'f', '2026-03-12 18:28:57', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('54', '5', 'Dk14wj97L3f05JJdhVoDmPG9bwhY8myyetK6baSrYABXx9eARpbIEEiKdyDArb1tp99oOcFXAabys564', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 21:05:29', 'f', '2026-03-30 21:05:29', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('85', '5', '2fpMsNUHAUYhLIZvSKS9nZZSlgRpLoERGUd0OjGhiDK6EUaXc9g0LX0boibRqVLUj9fN9JYG1aKpFhIh', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 17:52:15', 'f', '2026-04-06 17:52:15', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('37', '3', 'JnsAiMFNpGsZ4vr3I1Mw4x1rBNsMw3hJkEFaU5Ev222J3k2e2SFFmhnDxwedC6KlDE5G6aaa6lEUqFkJ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 17:49:48', 'f', '2026-03-26 17:49:48', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('92', '3', 'H1yACDEHjheHmhNQQt8sxSSF21YwuKydxQtFqqa8GAqM923aCLDsFDcVapdViEpELSlVYJio6igsjLM1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 20:12:01', 'f', '2026-04-07 20:12:01', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('72', '1', 'vxjvBheYcLrt0P58CKPT1QwN7qtxGqCbL5p5KXshcIMCHYVBaj4NoPqQfCsE2pUgjbeW6FTlVT9BxoOT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-11 00:13:54', 'f', '2026-04-03 23:18:43', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('163', '1', 'DfCzr882XnjDyiXeCDv1NJ5GURhoVYKyv7fm3mQ504bUdzCO0rFPNdlWWrgEsllX917eSEWnoq9er6mc', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 12:31:57', 'f', '2026-04-21 12:30:36', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('170', '6', 'UbhLEdEDcnlLyivTLD25xfUoQPUIRAgBUIoOisKAdQ1KhnQegPEYiIMBZSAP8BWLFkzZMWxUjxtG85EH', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 17:45:34', 'f', '2026-04-21 15:20:06', '2026-04-21 17:45:34');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('18', '2', 'xxiNgXw9kyPkmH6qXlzq2PkHBFBpdO4V53ibnMBhJ0HoF7YAsGxO0v3fLCCgqjdO4yvY9AE0BijA9PiV', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 21:50:47', 'f', '2026-03-19 21:50:47', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('161', '2', 'wuHIF2joFHDNj7ze9qlh4ux3Z0HU6dKx6RIvqIQPKSfIPMnOtEaP4wMYNbJU7gG70kU4XPJ9gHHO3XO9', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-27 23:14:01', 'f', '2026-04-20 23:13:54', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('116', '5', '1hEOePgEMKMdmHDwpmG3odyxEtrBJoWloKmxzQqxAitlP2Hd0a3N195h4h4jjALRPgxINnBXrgTAdNPy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 19:25:20', 'f', '2026-04-08 19:25:17', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('176', '4', 'RJuKvaffezIiGSCWreiR2knbCKu9l27XiPuZmynzG07RFpZ3iUmuBXGGq2OE5EnLoJUo0bDhXRB7iTph', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-30 13:31:56', 'f', '2026-04-23 13:31:52', '2026-04-23 13:31:56');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('96', '2', 'z8gpiC2P6FpIZPrnjF4PYqC0yKGMcXBYpRlSNZhtEFCghoHCrf4JPOOHflStU3cjO0Zy4NvCukqdnlQA', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 02:30:52', 'f', '2026-04-08 02:28:40', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('127', '6', 'QHLtv7MCZ7PSA0jwVNJEFalwi0TeIBCuhIahBPSchJLIhY4g0dXbrsG793HKqkHEaI5id0DxPLRlQlnR', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:29:15', 'f', '2026-04-09 12:14:00', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('58', '5', 'Jc0fDc518eWZuB0ejmoeem6A8QmfgqZozReh2paGDQxgBxZnlwVM8KPwrHDILwlEWFSTfFDo1wxZ89zd', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 21:08:41', 'f', '2026-03-30 21:08:41', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('167', '1', 'afWY8g68uhfECgKZY2sER3ZsPDoILPYCEoLgrhMms3nm3Ghi1juevPZ7kp6DSw0VVisZZC6Nfyk9AYES', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 17:44:31', 'f', '2026-04-21 15:16:57', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('45', '5', 'xesTpPezZQ78B7HkuICNybrZO8Aa0AEpvH9u8if0hR2DKQcSlxpzJ5Bdy8uIhWNykxcVTsmStmebcJui', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 21:01:57', 'f', '2026-03-26 21:01:57', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('65', '1', 'lmA3DDHgsZ9OeVJphNc149Rke3kYRlCKP0wKMrz0SgcCHmdu62SJUUAEtPqruJQOXCDFFlewxTzkWGez', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-09 20:53:29', 'f', '2026-04-02 20:12:41', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('55', '4', '9KI32lP8JtIxwnulW77j11ZeV1o77WEvqfvWI68nBUrwJoJLPD8mSutaZysjUayAn9ITRvbbhLXn0oiK', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 21:06:45', 'f', '2026-03-30 21:06:45', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('70', '3', 'ruKQZKP7AyMrLjq9zRcZsdJl3XzWlbEcxFGMxXXO720ipoFvFMtmMvStR5ZyGg7cNmHo4tNFiW4kSUEl', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-10 22:06:40', 'f', '2026-04-03 22:05:58', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('79', '3', 'qwUDzRLa8ZZR27QYHdT6SCQPGjU6HhEOfAU3gy3EQ2dB0Ng4jAccwjSI8rS8wefACB0cMklrpmk4MZKh', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 01:09:58', 'f', '2026-04-06 01:09:58', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('41', '3', 'XJVLxNzkSrLlELBOAthka9XhxG80txSvBot96VjSswJSSriXmXY6CBLTC3D0bnlhLkM90wQxYj2qiBu3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 20:59:03', 'f', '2026-03-26 20:59:03', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('180', '1', 'OKPUKh33MdP3sfLMGqHcxLIAa75vplJwtj3zNkMMi9n3Nu02CMWc8MzpjY7DPeLkDU4LpyI3EjLphrZY', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-01 15:52:50', 'f', '2026-04-24 12:42:14', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('162', '4', 'niKwUGpe139xd6tUeHsaKWVqlXscWCOW3QDNy0xeoQJVQFT3AXU1kCQMllg1xheVOQftGumUFak1kBkN', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-27 23:14:29', 'f', '2026-04-20 23:14:07', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('133', '4', 'H8eCeoz2kswHjKNzOJuGs4aSQuJC3GI1uZsdqRvrj5tA8glzBFb9yt3c2oBHI9AAQlaY4glWQZCwC2R6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 23:39:20', 'f', '2026-04-09 13:47:52', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('148', '4', 'vwE36l7oMckjSmGqtbRPNEgeKd5BWLE9zEkFnUMYhrnrlwgiU3K1vyATiWXWctxBz5F65SsJkCpEQ6zR', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-18 14:13:58', 'f', '2026-04-11 14:09:42', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('181', '1', '2aAR03tUuvBUYI5b7rbZh5GjmiwjvcpcRsRZXOQaD9CC2XP2bGCtFK6mZfBdJkpG6qektme9xeOCVjXB', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-01 15:55:24', 'f', '2026-04-24 15:55:16', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('49', '3', 'A9bwOSYxKZtw2vIsgxNYIeOxbKudc0k6veK6Uut5HfE8EdHTfykiTU6ZpfeqPhsitRTwhRQJhPRQGuFw', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 21:15:42', 'f', '2026-03-26 21:15:42', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('82', '1', '9LsVe9BXpk0VB3hA3Mur5MIYXr5atn2BedLTwhFP5zKWSWnZQjAkUrQT6CUOrKw6ygqnCEfyHEIeUFRI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 17:42:11', 'f', '2026-04-06 17:42:11', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('134', '1', 'QO6wjGNs0RnWtdMAthTlGndLTN2BuTW2rXnuj5876tJslmfC0tzM7vULJNRaVvLsNFckemxsxqf8REst', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 23:12:47', 'f', '2026-04-09 23:07:28', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('16', '2', 'Lu9AlQIpc3Iq2nV4BAc5sSkXhI3mMVgXGPTiGrNXzkfSlKYo1FSwLMWliKdEioz9M63tFIKunXYZUQKm', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 21:38:46', 'f', '2026-03-19 21:38:46', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('25', '2', 'GEjyafR4cqu9unrU5uGGSxDdYVqAj3wChgTxJJYqwk5nrLiXHsY8XPLenpxyL8mrN1a2gSFmbmeUNffD', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 20:21:55', 'f', '2026-03-24 20:21:55', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('28', '2', 'VxK44iIKXmzWM4RygbtQWQz2l7u5IsXLNV6IIWi8TtrSYy19ZxfKaYuxtiqbQfBsQDLHEBt9iEaONbfA', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 20:40:34', 'f', '2026-03-24 20:40:34', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('143', '2', 'I6pN8vCbBEHUOtqoiyEYXFNj4Mudcz8jlRXxSI4AvUgSzmRsMu002QOYUAE0PbvtcdrBkzSQduk2DfLV', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-17 00:33:05', 'f', '2026-04-10 00:05:09', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('104', '1', 'DhJKRPWU9CcPJKHR0s8anwRpdq7EbaNB4mN8oRG82r6yDSSBd1F7JRByLXATjOvGk5tzTG4SJurzQ2c2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:26:42', 'f', '2026-04-08 04:26:41', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('117', '2', 'fc67tAS4pOi81M1J1n4nQ2DmP3r9Vrw9Dlm64FO7TD0bvDsPwXmNSaFl3FPBElMI6SUaPmmvssSYiTMz', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 19:37:10', 'f', '2026-04-08 19:25:26', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('154', '6', 'n7pW2mduTy0uWgQ01VUQnSrXgQNPk1W1k5tz9ZpzbUhSlKJlBcQbwxyVpIzvKwE6ZySSGctYFw2UxTIx', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-24 14:16:36', 'f', '2026-04-17 11:39:50', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('150', '6', 'QwaAZpFGc5KCVTAcYxhbApVUFMehEo2kIFWkotpWe1LOl7j692xz55sgsV15dIJdHcJvtYczetXfGU4H', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-19 19:09:32', 'f', '2026-04-12 18:12:49', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('128', '2', 'gXdYbXPK20faxDT21SfGXjC7c0ZwdxJFu95NZVpZJxLvgddX0R4YCT62rMjVL9IfnvXqIt5MGIZcgsdk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 22:52:36', 'f', '2026-04-09 12:21:06', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('81', '2', 'GJXhRFPOf1FiT3J1VXAas8CNJMiPo7PjJSYxstQVrVMvG8BqIWz1lhHdGpkxJANWaVVa7ugmykezaaxq', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 17:40:56', 'f', '2026-04-06 17:40:56', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('145', '1', 'H7bVylZtxJW623FZWP1GMbC9PPvACv8AnYttpVbJjzVBB1B2C4pci1Tbd5U1gU7veZ7eyOWNttR0gf0Z', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-18 14:33:34', 'f', '2026-04-10 12:26:12', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('52', '3', 'MqPqSl8kpuXTvarA1ll9yuhlg7aG3LIYG46MXHWJisqrV8qBjpCtkurM6f35GB33iUUNsY8FGZTmcVLT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 20:43:57', 'f', '2026-03-30 20:43:57', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('86', '3', 'ZVWuLSTBZYJ8RUlvIcHYmJmNlOKsdldIp5E7JNKNZx30PP4v85LbvVK1qX3CkNCvoAkMrHxlIr1oTSQs', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 17:55:32', 'f', '2026-04-06 17:55:32', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('31', '3', '4obztHFRrmK7dxSrSv8tB1KdcQfKNx8ixVBhNDfmgUNDhylqiqygb4bmz93qoHgXHNG1FT15jpjfQNgF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 04:31:32', 'f', '2026-03-26 04:31:32', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('118', '4', 'GW6bKI2cDVxtBwu7hJx2Iotmrj9zXmMsDBnbFhG8ANsuhtgCY9gLgbJxKQvvq6wlgO0Gd3654sGj5pew', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 21:33:27', 'f', '2026-04-08 19:37:16', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('60', '3', 'T5PRCadrQ7vXhgCZQI3PRiSAOLNnTKdwMBGvwGWwvVPH14s0jjEqAgGCi7JfT8LnvvtrNAOHcNg7huN8', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-08 01:43:52', 'f', '2026-04-01 01:43:52', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('62', '3', 'DIxhOh0Tf1EYB5eNldQ5F4kGwFY3Klu5c5FawyUb7vXRZesXCRzOw5jBAtpLgbk6Su4tp4zIYmQNp5I9', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-08 01:53:41', 'f', '2026-04-01 01:53:41', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('115', '4', 'fJvKpVHV1IMcqG86bSdvUWDylC1N4jg9ya77ybu5IiffIDNu3gvH4lvURs3rvkxsXI59VcU5KQTFpQfw', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 19:25:10', 'f', '2026-04-08 19:25:04', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('69', '1', 'Wx3iVqmJn6WxycJtIuNgi9Dt4w8y59dK3SWfsV4BKPCDkqE3z0G7NMZWdCGP1d2FMiolOkD8Z8rk48qj', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-10 04:00:59', 'f', '2026-04-03 02:51:50', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('15', '1', 'X6umDhpVOJYXNJHoTSWLWJczuZIVbqtaOQUdavgACgCClKL94v1g86OE3y02EqekIeYmxiaXZB2elkQX', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-26 02:41:30', 'f', '2026-03-19 02:41:30', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('98', '1', 'o40TyJvZj1ONP0CbSzXDey4zqFjZtefSAxFtyyx5mNfgGtnMuwcL1p5zKA6yESyykP35lInU8dN1TQw8', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:24:09', 'f', '2026-04-08 04:24:09', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('67', '1', '7yT1YnuLUaoFMdB2wtHFNtKkJGbhpCZGWFcKnXzYoV6ocx47ROx99kvRnPZNHomR5Dbcgmgp9TzaKttX', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-10 02:47:51', 'f', '2026-04-03 02:39:45', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('123', '1', 'DqTIxc9kTWwLOfUNAegJbdyTEfnzLszkU9yDEhF0kWRW23o8pSIYJqlrp4hNKxXWnPqglNJz1IaVPDzb', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 22:43:34', 'f', '2026-04-08 22:24:29', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('66', '3', 'zRh54ptiACeKABGS1uYlBK98b6zIttF7NztXbyTDZpAuoKzFJBiqT1S7uLLkkxYWDlbsrwQAsZoO7YMZ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-09 20:44:38', 'f', '2026-04-02 20:18:14', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('74', '3', '4EkHExWhCXpvdN1yxKSJWB13dTe7EhGUnVJP0vLqxCbHN11FgBq95d3ySaDONhxRFEqJ85ATrI8SK4Hy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-12 01:22:05', 'f', '2026-04-05 01:16:25', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('100', '4', 'Grp0u5zVt8TdcgPX6e5djWNEIIEglBLsctsjb5Gl0zDSqnuo6VKBRRTPdCa8DWAGCfLD5PF8Uc3jhhFt', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:25:31', 'f', '2026-04-08 04:25:30', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('120', '4', 'euoHh7LKKZPBLPCM8CEDjZsyHhW0Tjob8axhmNI7AzQu1AxRUbtvvqOs6OrYr7T5R8Ke8vhHQJJkVTHI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 21:41:52', 'f', '2026-04-08 21:37:19', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('155', '2', 'UfYpNx42lTRtAapH5pujHpKbTvHF2cbbQgVdfRd6UN3t2i4UzoUo90oA1aFBBaC8uX47qERXWhU2Yx8U', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-24 14:47:37', 'f', '2026-04-17 14:16:45', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('119', '6', 'jBhygeHUFi9kzcuGbsNhiW4E21qurhcM5JPkRCKzJ9joaAUjmzQvsJntjg0m11ziqZsw81lmwBMzwQDY', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 21:37:11', 'f', '2026-04-08 20:33:57', '2026-04-21 15:20:06');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('32', '5', 'jBL6OKBpA650v8JD12O9f6W4DTIILsAmyIVicx3PVu8GyN4xyFMoHKwCXxVcvmBpyWWUyzdkXS8BpiBi', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 04:32:08', 'f', '2026-03-26 04:32:08', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('147', '3', 'jgyJCWi2Svo9oCrAvFQvAv4SF5M1uYnoSSxDSelhKax9i1L57WBVoD65smfVx2xuKkZPEGhTidB0V4LV', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-18 14:01:11', 'f', '2026-04-10 12:42:09', '2026-04-11 14:01:11');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('43', '5', '0OuDzppFKZim9628gHX7kPxgYV6IUOGanS1e4VhBkAibyqLWmOP2AFTSOnqEQCreDYqM2ShqK8EZyvoM', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 21:00:12', 'f', '2026-03-26 21:00:12', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('77', '5', '4j9mJxas89pudO5b4zYyu8oHy8zvFN6WWWl3DwubhmVmzrunzJNlErWxTF4mO42h79T3UkWzBgY9fJOI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 01:03:16', 'f', '2026-04-06 01:03:16', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('110', '5', '10SUgNbgXOURPdqVMH73LhRkpxsw2tWZhbakqVS9SQ7Gmm9qah2oH4ImqJUKyozRyK0tdxndWgx4EmtZ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 06:07:25', 'f', '2026-04-08 05:15:48', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('87', '2', 'dvvxlTQT8X4ZiO5LZRB0kSsvCnB98a5kqW68VqoaNZP2A8JK47fq5baERxdhVxjhXJ79rK53In2Z11YL', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 17:42:02', 'f', '2026-04-07 17:42:02', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('146', '2', 'gfOW5eV7nCm2tJm2CyoE3vW00JDfGlToHzdax2cHZg3IyH4f8lD4xkrGUNdCl1zJDfp6XsKRs8W2s0jU', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-18 14:09:35', 'f', '2026-04-10 12:41:49', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('140', '4', '7d0BSjMt5pKz8TwozUR1EgdpRfJJkpB42pCYreQR1yfgykoYcpAC61VqyGuMwMSq2d0jJZxirMDbjq0N', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-17 00:04:12', 'f', '2026-04-09 23:47:19', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('144', '4', 'pzgU4uKBxP2V4jtohYBPfEzCoQoexXs22BuLFC0hq0xhdaRhcZUPmMERMxbOX2Tn4AnYuvmZ79tEZhI3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-17 00:31:53', 'f', '2026-04-10 00:24:10', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('61', '1', 'Jt2kjEPrYLVhLLdvUVXjRQEQ0VsVwuL3lSO024qTnbepMBVnoTXrVfODPYW0MKmk5PZymuF0kH8S1Yar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-08 01:51:54', 'f', '2026-04-01 01:51:54', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('135', '2', 'ucdDcvedwBlOlmOka7y59QOPRkyu9kUFQ5LtXBVrn1IGN9UhfFw01DIYkjRU00h25bO02Cbol69YEP6i', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 23:12:24', 'f', '2026-04-09 23:11:53', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('93', '1', 'Jhs5G2Fgd3v7kt9nSOrvIhhkaRDs2h7c2bZZPt0Xe8U8kqunj0BSWERe68C9WIfrGzXBShV9iBEPY6RS', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 21:14:54', 'f', '2026-04-07 21:14:54', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('22', '1', '2AukATxLey5kKZP6JSefYj0YNX3yUq89M6YR3v1IksMQ48vldZY6FIYpNOMbNhA7pbXioWezuHTUQljR', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 18:21:34', 'f', '2026-03-24 18:21:34', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('173', '1', 'CvDzrZHp8c8tDboHArYwBI8OQQrHsV6H3ma02JCiDD18HZJpdwhaNicav8lWYePJTNHY3aHYd5kSijEa', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 20:01:09', 'f', '2026-04-21 20:00:58', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('136', '2', 'mpj6oNhpwMrFFsbIk4afknI7iLhKbhFxK3pRvBFWopyRnUL2l9jLV8NHXmtucMbvfopu0R2Rh6lQRBsm', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 23:37:33', 'f', '2026-04-09 23:35:22', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('111', '5', 'u4e98SOZmmKoQ6dCQoUnqY0seyAZoy7As4NyWQyNSPDvacx3iAOt9j7V1nncEae6sxG885dcN5U2O6aF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 18:44:23', 'f', '2026-04-08 18:43:51', '2026-04-21 15:19:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('182', '1', 'volES8ntQyCrOMit4m0amw4yIN2br4F6MseYZPQt9XNk5Q0hBrfH3LrHyoF7qINtg0u5rQr7NsmEJgFT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-02 16:49:37', 'f', '2026-04-25 16:49:36', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('29', '1', 'g0rGQGxccQOwwG9dSuBSJuHZuXNXfJlhYSj7nSLvaXakxsSpdvbj9uj47ZfzKGTx0D182H2RgXdCJbsy', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-01 18:37:41', 'f', '2026-03-25 18:37:41', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('44', '2', '92e5Fmtsw6ESGc6WHU50eVmYc7t5wOJLGnndTpdThWctE70BzM5NHNsh9ZfcpMLfIWpzvfoj3bGYCvEo', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 21:01:07', 'f', '2026-03-26 21:01:07', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('172', '1', 'gqPyqTQxHtmyiPPdQM1Hg7v7eWixhNBqSacYO7VklPoE55zWWw4A02FvKRZLI0RE8jF5ucLRZU28AsGc', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 19:41:12', 'f', '2026-04-21 19:39:47', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('159', '1', 'XiIxSyXLbkuvsQD3vZecQYYBz3yIqWGwbSvYuS1AWB8SYv2yaW5zLjSQIFrTlUji3NqApKpx9RcxsnRI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-24 23:17:24', 'f', '2026-04-17 17:35:59', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('160', '1', 'DlGo1W3kmpHqfHT59RefgWDuWxxErPY81xllNTFeE8Ed8hdpE1PtDfDK99Q89ePq8jGYQ0ZJ3Jwe3WB8', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-27 23:14:37', 'f', '2026-04-20 23:12:19', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('97', '4', '3cXFt1UmJEdkwpaUg9EPx5ZDeZmjaHYV7w9b337r4CWsbV7MrPoI2eH2jee6w1yNH7GETlLpR5j9438L', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 02:31:25', 'f', '2026-04-08 02:30:57', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('102', '4', 'IqxVR1l6W4C2A324WjuP17YVcjngB6S9kotfLtYHT2juJz6sfAyV1ja6QsBWY60voxXKMlRLVLs6opwB', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:25:58', 'f', '2026-04-08 04:25:57', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('99', '4', 'McDL02cAw2upSiStt8RjS57G3T7PPv9Oz3JAmfQWmdY9n6BREwlpPzcL8xfpwixa2b8NI230P1XTvDjG', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:24:19', 'f', '2026-04-08 04:24:19', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('6', '1', 'FyChdb76Jobwe1BgNsVUanhJrbDPYB4S5HWQytBr5FgXi8x0IKTjklSCUipJOtqRjg4LLmUNp8rwnAU3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-20 17:12:01', 'f', '2026-03-13 17:12:01', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('103', '3', 'wuyn7FEjv0bpM8Tr8FdPA1f7G8TiL2bpFsGtlImct0p30DCsgWqwjXRPo15oA1oc5Bd0kGJ8vMtW7uNn', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:26:28', 'f', '2026-04-08 04:26:10', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('149', '1', 'kGWO279cAvmqVYqsB1kfhhGUWr0xCgOgnJ1vfzBHLM6dWbd23dLCgbQ7RC3t1ydpyX0HqZsIKBtK9ZqM', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-19 18:59:38', 'f', '2026-04-12 16:04:52', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('185', '1', 'J699gXZeMvfvu7DKq7xPi8fJXK5heUGwRrQhLMbBs7G05oLuZ8UOJ3JLFk2ZxO7giqHxzarQutVKIVqI', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-03 14:09:47', 'f', '2026-04-26 11:56:00', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('174', '1', 'kJINI1fXUz3PZ6uyz4uDwNG9IAfg9L1OqxG67iP3rgqCX6oD2FlM9YjvDKuHaYvD7DUWL4pHDeq4Oknw', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 16:51:39', 'f', '2026-04-22 16:48:30', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('179', '1', 'CKHC9do1Izgckw3zo3DVYr7g73gRsbgukw3uI5jPDw6Bu2NhYbGI7S9pNCCJQIjsPmyFcPhDoAF35q1j', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-30 13:50:29', 'f', '2026-04-23 13:50:23', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('33', '4', 'O6xtqUD6yLqs5oiEkssvF7isM3kTVxW7JS5gVzQqu8Ym7YQ7nmJY7xYs69rfzAFb4zR5AV8sIHJsVds5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 04:32:30', 'f', '2026-03-26 04:32:30', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('36', '1', 'd5VppHhTkZws5wJwMFNZGxnpMBn0HybzvEJWWGzeNtz2FIdWYeV17jRbhpiy5OtXb5BSb5xuQKrTYQV6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 17:48:30', 'f', '2026-03-26 17:48:30', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('109', '3', '9ndB5ubutIcicvKXOldE8PbDuvx5iXXSRgVpmfLiDFrK4OuyDtELh2SMeltUSzas92nIXUaOBb7Mf8mL', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 06:07:39', 'f', '2026-04-08 05:07:44', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('157', '4', 'yLljLvpU6tJxJQ9sf3JAaxdHyWKmlT46TEFt9a4de2l0y4f1viXuPY5GxyIFeePLg1Pw67CzcfYAieE4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-24 15:56:09', 'f', '2026-04-17 14:47:45', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('112', '4', 'xB36WC8kmLKYmq0IFE7us8BnTfxjNUwO9SqRnfhrWiquDg9WfVIv0WsbyEVmCEqTppNKF4FxF99CLIdv', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 18:44:20', 'f', '2026-04-08 18:44:01', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('132', '3', 'ogkzsR8OU6ewxLAZ2BoMIgyDNK0BfmvXiNlcH1XzDZVu7fxkFvqxmV4fkfGsDYUqvHwD6TO8fQ5kWe8b', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 20:49:50', 'f', '2026-04-09 13:29:49', '2026-04-10 12:42:09');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('121', '4', '5sINcAv79pj6Ofwjw0MMjwwPM4biasUj5p3nvwmcSBIIxqmHMzSqMbojlr1SHJatsDHxGAq0UegoWxzh', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 22:31:18', 'f', '2026-04-08 21:42:18', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('164', '4', 'kZDFzfBgYYzxqZV2xBa0GCgAg9WSc9ocBAav3xJssO1Zhu39dBTfotAVThSy9sH8Kd3maW5LiscfBlzh', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 12:32:50', 'f', '2026-04-21 12:32:03', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('63', '1', 'xIcQt0bBLoLDZMreoyCroUYeEmAehxbrk3SAEo4F45Rg92NnSS2IOPLf1WLYSvUy9bNXXOtoBNSv5SXN', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-08 01:58:36', 'f', '2026-04-01 01:58:36', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('101', '1', 'AhkIsQIzdZv26d3a9YXXbxiAgFyzbaqM9acgxsFh5lZTBhQzOH5VIa5U6XzSbVuwDFmv6tUb7iF9ZtYz', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 04:25:43', 'f', '2026-04-08 04:25:41', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('21', '1', 'OkYnUHi97A7pjemxKR9Hi80EvdnKcXpzPr2eV5GTAI5Pgfhlx44jkG4p6LYD5LRMuCEWvfQFgV4oY47j', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-30 03:00:49', 'f', '2026-03-23 03:00:49', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('88', '1', 'Anq5cgb3OYIOkgyT2npLEk9PRi4FbQBx46uYD5F7E0GrxbcVpYRLQVJ4RUpzgxou2wkKG1NZOB0jUKpl', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 19:39:32', 'f', '2026-04-07 19:39:32', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('50', '1', 'cmAuMZM64EYjRuGCOGyzIlLL03J2y5o9zukuo9LQ2YiCGdO160PnHlzdjXTOPq3paLK4v1ep4OMNPHSM', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-05 19:52:50', 'f', '2026-03-29 19:52:50', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('142', '1', '0cW8aLMNaJNobGPcgFxtxHiD7VqC0cvvfvjCkObjou1B1jqYf5494pW0yblswdo9DcAFPt5DyiKiU9R8', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-17 00:40:03', 'f', '2026-04-09 23:50:34', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('94', '1', '9LnIVZIree7cBQ0vudYm37M8AJJUjyHa7qPtidv3KReRDfvE1KNjNugMUkw8xGQjZ75fzgjowbNliCGF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 00:35:32', 'f', '2026-04-08 00:35:32', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('138', '2', '4r2FmBAC3MTnCWLwOvJzB6LT4Rs1FnWrUZOtp0ga3qbQRbGgXBhc2vveF5lQ8GYhTksykpos7NPxePY0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 23:43:38', 'f', '2026-04-09 23:40:06', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('177', '1', '7aiXedsDjsKHV3aHK06VHXA6tKP2s6PN2ir99ZbYxPwgoLHtVhO1KrmTk273AotxTBaCr2i7EiB4DEAz', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-30 13:40:58', 'f', '2026-04-23 13:40:31', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('91', '2', 'PTCv0BeHTmLSg8bNS28DVfuJfl2C2lQJweQkrDdUouAirVLKO2X6ubXqJj7lIHA2lnahJk1hlKguXphx', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-14 20:02:41', 'f', '2026-04-07 20:02:41', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('78', '1', 'Kv9XIFgMoHzyt8XVyCxm14nIytimrXy4fidxwfmwOHrgkcNwEuHAY1ahXLYrc8Jy7UA8Dpv10avJ4fOe', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 01:03:35', 'f', '2026-04-06 01:03:35', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('59', '1', 'fMC1dJgl4luSvAkKdw5nTGAvt3yrZJQToUW39pMkHN3Ax65rWMlJ8yMDkiqUxn2NI2K5COPFdJBfFzF5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-08 01:41:00', 'f', '2026-04-01 01:41:00', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('84', '4', 'v1h2dofmouIxHtCfodnLxM03tT2MTOT07YUHFHEL7Obe7a3rUyum7moL4sZgkzC3Oj6CV5B03g1JZGDv', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 17:52:04', 'f', '2026-04-06 17:52:04', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('137', '4', 'oER5Pli9DynuyRBfTjVSGguYx8dOsa6tJkA0KD54jgqhDzTq2ear3DHERdZFPGajLS3IlH7aIaHeJczG', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 23:43:39', 'f', '2026-04-09 23:39:22', '2026-04-23 13:31:52');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('107', '2', 'pHcysGNa1x9zUP8cgGgzV5tXrFbV0YvndIeq1nFDShXYoxeiTVgLgGE1e8SGUMoJ5TntiMhWlx5noCBr', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 06:07:29', 'f', '2026-04-08 04:30:47', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('76', '2', 'zAUJiVgo7GjEUwVI49MaNV1MPNuZr6QDWTvVZU7MtROqw2iA7RDIbtTY4FIdKm78iR5HLCzWyyDxldNF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-12 23:04:34', 'f', '2026-04-05 23:04:34', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('53', '2', 'xQ6eR8VnJAua6psuwo9oIX7gU6aPgzqh1kxeavbyEI1xRlapLwk4oZqUOBnxp5RbFMj0lWoeiVygsyZ1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 20:48:53', 'f', '2026-03-30 20:48:53', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('80', '1', 'Zvi1TNVZirqiObVEOdi9GK38NfZz55CBIV7EI7Fsnq3Mlla3LssGTj6o0oxyA2yrdvC9s0W3aBYlNOPv', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-13 01:12:56', 'f', '2026-04-06 01:12:56', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('56', '2', '5BGqlY8T7D8O4G7eicMGw9HtRPnhjhsSNabcr8UDd1Ye8Gr8JjFqW3wKf1Q5iuAAQI2wLWK1L0Lm3aU3', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 21:06:56', 'f', '2026-03-30 21:06:56', '2026-04-21 15:16:29');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('19', '1', 'yoLULIsjbsfr8HHqItWClxiXm8MY9K2dqSzwHdbDMQpSbssirVfjJTql7qVs0dcT187RyPOBOh5NlNvb', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-27 18:47:46', 'f', '2026-03-20 18:47:46', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('48', '1', 'TSQrZ6SG96HeqRnLX813XJGma0LVCRkPdfJh8lCFk4DzmfDBqK0NGWqbd5I8oWDjt0fXK6nwNEeJrRDs', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 21:11:24', 'f', '2026-03-26 21:11:24', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('34', '1', 'DXcZl63IyEaAM3lAAaUBF1YTZGjgTXqRX2liTJrRXPaQoKaKOYtbxYYmNhsaLVl3lkHn8dfi0ukLUYDw', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 04:33:50', 'f', '2026-03-26 04:33:50', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('129', '1', 'QWaVMkSZdNjdWO6oOmRRGPQCtWo17Um6hdeNuXQxfZiKaVHiiFw8T5hz601ZN8wZ4wIIDIlblcfrGSAO', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 22:54:40', 'f', '2026-04-09 12:22:02', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('95', '1', 'TCBneA0pZoZxuNH0360nqUUcXAV2LuGUt47DOztY9pFwRvJHviaWhU98UlBZArJRHjEZ7AYgRRB0nXGC', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 02:31:31', 'f', '2026-04-08 02:28:23', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('51', '1', 'trLbpH2JkV6GJSgY20qmp8OOhPFob4P2OZJxQOh6ViIokZOIAMIFZReiKuuxm8VxQyBnYqjWLTc7y5DQ', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-06 20:43:19', 'f', '2026-03-30 20:43:19', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('14', '1', '9X2HgfPjRCQ7Jb7tGqfeDdiLVwakvbtLkGOiGUmclU3JKQimmstssRyQVPsMOyZI230OvxCVf8zgJRfC', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-24 19:58:12', 'f', '2026-03-17 19:58:12', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('183', '1', 'f0rZwNbDGVb1XuD17RrAZobupLTrZqNeO5J75n4JpkqYQE0FHjKCeSokVt59qiKhSpHiu6nO4tv7SStW', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-02 19:20:38', 'f', '2026-04-25 16:52:15', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('75', '1', 'rugbM6IKobp1lVMzh2OjsQrmVTSvdCrCfalqxJYFTUecU5BU2ENktxUbqRj2gvS5j1u3LVnbEOiyiHWM', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-12 23:03:58', 'f', '2026-04-05 23:03:57', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('38', '1', 'VbEe3UfABwzj0fib5qa1MP3gLXUD2sBvOwFqVoLLhXsvBIJ5B2dOCap0i8xHHI1E3pTDuoPKBlDSMl0y', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 19:02:13', 'f', '2026-03-26 19:02:13', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('39', '1', '4GTTGsLlGHgIMUhvpMaSPmCGTuF95GT66B6aU8Bcu5Gf8tjrwkhndAmZPo4j6oY5MmWJ4NMEYI3WrjO4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 20:07:13', 'f', '2026-03-26 20:07:13', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('64', '1', 'CNbG5zkbvFv4w2UTue0jo23HRYSYT0wp9WCw9bFWB5Fx9EM8ES0kgixH5TapmZW5utGE0pNCNZhYmQFc', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-08 01:59:45', 'f', '2026-04-01 01:59:45', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('20', '1', 'JSGxFPBFVedAl3av8IYV8rZCmB6nUddAO6dhEsT3CA0pY7ZzGymY5OxorPCFwyEe7fL75dbHHsCjnFb7', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-30 01:53:19', 'f', '2026-03-23 01:53:19', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('165', '1', 'q9I2bYKgpPlcd6tV8sxZ74qc1IGHpxQpKY6GjMEH9rB2UUPJlVFQqAFwNU6Bgs04LuqGVJsmUuk3UtOE', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 15:16:22', 'f', '2026-04-21 15:14:20', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('23', '1', 'pCuLMrAjrDdwB86NuxOPaoPs3mwgC5XOyVHlWatJ79vvlNRvAv5nrT3SvM7MoGM7uQzgMd08nxRvTSOB', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-03-31 20:20:42', 'f', '2026-03-24 20:20:42', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('153', '1', 'hmtwIvHnYbcyo8gILYeHXceadP4FjTGUOGrFMKauO53eKkNmIucE6pywbeIQlQaFRFwZ1fWT4uxJpKBz', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-23 23:17:39', 'f', '2026-04-15 20:15:28', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('40', '1', 'vF5oTYfvuAH7XsZ5Okh0ZlOMPuo9EpJrXxnJRs5ZrAU3jMHyXMdHUtTzuHnQYrASK38nH693IWxDX4nq', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-02 20:56:36', 'f', '2026-03-26 20:56:36', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('108', '1', 'G6vO5griZkvEhQYQt6Bl8hc7YTixGCdI7HG8NvsgseRwnhLxC15ehJpJhut4JkB4JdAq9ETW6fp1rIb5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-15 06:07:45', 'f', '2026-04-08 04:31:15', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('171', '1', '0W16chzIHR9Vdwj5qM4IqN1niLK5eQW9nTv0bkiQgDnjLqYZgA0ECYX77wLAmNV1o3BVutUexZtWM13C', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-28 19:36:56', 'f', '2026-04-21 19:33:45', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('186', '1', 'HCzWrFp7Em1iHKZM81YH8iU63InPnQLr1z4HNUqQIeeMmzX94QL8vC1OiwCuX6AlB1niFHobcsTfrIj7', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-03 23:48:59', 'f', '2026-04-26 20:36:17', '2026-04-27 12:05:44');
INSERT INTO public.sesiones (id, usuario_id, token, ip, user_agent, fecha_expiracion, activa, created_at, updated_at) VALUES ('151', '1', 'hatBn4uPUynvG7VEI6IikUXCCmIZxLD7FSJsNg9eXB40Pbb9fKNcOIqSxqXZH5UVktXS0Xva5mzj0Rlx', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-21 00:35:06', 'f', '2026-04-14 00:12:45', '2026-04-27 12:05:44');
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('6', '1', 'CERRADA', '2026-04-09 21:06:36', '2026-04-09 21:38:54', '2026-04-09 21:06:36', '2026-04-09 21:38:54', 'FIADQ7', 'INDIVIDUAL', 'manual', '0', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('3', '1', 'CERRADA', '2026-04-09 20:26:22', '2026-04-09 20:51:59', '2026-04-09 20:26:22', '2026-04-09 20:51:59', 'P6LWXR', 'INDIVIDUAL', 'manual', '1', 'ugJ6ffBFSb3UeCWUEzGOTxjU3XBj7FPJncUZjyrvDp39iD8r1oSGHos0jGZCtcdz');
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('4', '13', 'CERRADA', '2026-04-09 20:45:46', '2026-04-09 20:47:14', '2026-04-09 20:45:46', '2026-04-09 20:47:14', '6CLDLP', 'INDIVIDUAL', 'manual', '0', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('17', '13', 'CERRADA', '2026-04-09 22:48:31', '2026-04-09 22:52:33', '2026-04-09 22:48:31', '2026-04-09 22:52:33', 'ZYBQLC', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('5', '1', 'CERRADA', '2026-04-09 21:06:00', '2026-04-09 21:06:18', '2026-04-09 21:06:00', '2026-04-09 21:06:18', 'V7APWH', 'INDIVIDUAL', 'manual', '0', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('1', '1', 'CERRADA', '2026-04-09 16:24:02', '2026-04-09 16:29:20', '2026-04-09 16:24:02', '2026-04-09 16:29:20', '0E2XN0', 'INDIVIDUAL', 'manual', '1', 'z9KIF6jYn1319Auobkuqb7z1uEiamE4eNa7zY7fOsklvvOHQvZgUO7LJheiTyi4u');
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('7', '10', 'CERRADA', '2026-04-09 21:43:12', '2026-04-09 21:44:07', '2026-04-09 21:43:12', '2026-04-09 21:44:07', 'HSRGKR', 'INDIVIDUAL', 'manual', '0', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('15', '11', 'CERRADA', '2026-04-09 22:11:49', '2026-04-09 22:16:55', '2026-04-09 22:11:49', '2026-04-09 22:16:55', 'AI1RKS', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('14', '12', 'CERRADA', '2026-04-09 22:11:12', '2026-04-09 22:16:59', '2026-04-09 22:11:12', '2026-04-09 22:16:59', 'UNF9UO', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('8', '10', 'CERRADA', '2026-04-09 21:44:20', '2026-04-09 21:46:24', '2026-04-09 21:44:20', '2026-04-09 21:46:24', 'YAE9I0', 'INDIVIDUAL', 'manual', '0', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('2', '1', 'CERRADA', '2026-04-09 19:53:44', '2026-04-09 20:11:06', '2026-04-09 19:53:44', '2026-04-09 20:11:06', 'IY6XEA', 'INDIVIDUAL', 'manual', '0', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('13', '1', 'CERRADA', '2026-04-09 22:10:04', '2026-04-09 22:17:03', '2026-04-09 22:10:04', '2026-04-09 22:17:03', 'CQAU8H', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('39', '2', 'CERRADA', '2026-04-23 13:40:48', '2026-04-23 13:40:55', '2026-04-23 13:40:48', '2026-04-23 13:40:55', 'LHNB2A', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('9', '1', 'CERRADA', '2026-04-09 22:06:38', '2026-04-09 22:07:53', '2026-04-09 22:06:38', '2026-04-09 22:07:53', 'RWD19A', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('34', '2', 'CERRADA', '2026-04-17 17:36:39', '2026-04-17 17:36:54', '2026-04-17 17:36:39', '2026-04-17 17:36:54', 'SSWZ93', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('10', '2', 'CERRADA', '2026-04-09 22:07:09', '2026-04-09 22:07:59', '2026-04-09 22:07:09', '2026-04-09 22:07:59', 'UCS9NB', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('28', '1', 'CERRADA', '2026-04-11 14:01:28', '2026-04-11 14:13:27', '2026-04-11 14:01:28', '2026-04-11 14:13:27', 'LQY949', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('11', '2', 'CERRADA', '2026-04-09 22:07:18', '2026-04-09 22:08:06', '2026-04-09 22:07:18', '2026-04-09 22:08:06', 'IM30JK', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('12', '2', 'CERRADA', '2026-04-09 22:07:36', '2026-04-09 22:08:20', '2026-04-09 22:07:36', '2026-04-09 22:08:20', 'GHBVNP', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('32', '1', 'CERRADA', '2026-04-17 14:46:09', '2026-04-17 15:45:08', '2026-04-17 14:46:09', '2026-04-17 15:45:08', 'WKLTEG', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('26', '1', 'CERRADA', '2026-04-10 12:26:37', '2026-04-10 12:43:10', '2026-04-10 12:26:37', '2026-04-10 12:43:10', '8EYYKJ', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('40', '11', 'ACTIVA', '2026-04-23 13:46:05', NULL, '2026-04-23 13:46:05', '2026-04-23 13:46:06', 'O78HQQ', 'INDIVIDUAL', NULL, '1', 'K1qKeKavnXSW1V69OkegK7VkXmA8fiZwkNVdWmucpqu2FCGe7Mdy5gRr1Ynuy1kS');
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('29', '1', 'CERRADA', '2026-04-11 14:22:36', '2026-04-11 14:32:45', '2026-04-11 14:22:36', '2026-04-11 14:32:45', 'HLHZVF', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('20', '1', 'CERRADA', '2026-04-10 00:03:33', '2026-04-10 00:09:33', '2026-04-10 00:03:33', '2026-04-10 00:09:33', '802APL', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('35', '1', 'CERRADA', '2026-04-17 20:48:36', '2026-04-17 21:23:59', '2026-04-17 20:48:36', '2026-04-17 21:23:59', 'HXRFH3', 'INDIVIDUAL', 'inactividad', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('27', '1', 'CERRADA', '2026-04-10 13:44:06', '2026-04-10 13:52:50', '2026-04-10 13:44:06', '2026-04-10 13:52:50', '2IYRKQ', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('25', '2', 'CERRADA', '2026-04-10 00:26:55', '2026-04-10 00:32:15', '2026-04-10 00:26:55', '2026-04-10 00:32:15', 'KOLOGR', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('16', '13', 'CERRADA', '2026-04-09 22:32:09', '2026-04-09 22:36:32', '2026-04-09 22:32:09', '2026-04-09 22:36:32', '3GB2J0', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('19', '1', 'CERRADA', '2026-04-09 23:40:49', '2026-04-09 23:50:47', '2026-04-09 23:40:49', '2026-04-09 23:50:47', 'S7VYVQ', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('21', '2', 'CERRADA', '2026-04-10 00:10:59', '2026-04-10 00:24:50', '2026-04-10 00:10:59', '2026-04-10 00:24:50', 'UJKAFV', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('22', '2', 'CERRADA', '2026-04-10 00:11:05', '2026-04-10 00:25:01', '2026-04-10 00:11:05', '2026-04-10 00:25:01', '8QX08D', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('18', '1', 'CERRADA', '2026-04-09 23:09:10', '2026-04-09 23:12:23', '2026-04-09 23:09:10', '2026-04-09 23:12:23', 'W45SVB', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('24', '2', 'CERRADA', '2026-04-10 00:25:55', '2026-04-10 00:32:25', '2026-04-10 00:25:55', '2026-04-10 00:32:25', 'WBPZBK', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('23', '2', 'CERRADA', '2026-04-10 00:25:31', '2026-04-10 00:32:35', '2026-04-10 00:25:31', '2026-04-10 00:32:35', 'WZFIPU', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('30', '1', 'CERRADA', '2026-04-12 16:05:09', '2026-04-12 16:05:31', '2026-04-12 16:05:09', '2026-04-12 16:05:31', 'T5TX8O', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('43', '2', 'CERRADA', '2026-04-26 21:32:43', '2026-04-26 21:35:09', '2026-04-26 21:32:43', '2026-04-26 21:35:09', 'GSXHT9', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('31', '1', 'CERRADA', '2026-04-12 18:58:44', '2026-04-12 18:59:23', '2026-04-12 18:58:44', '2026-04-12 18:59:23', 'WJGDMZ', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('41', '2', 'CERRADA', '2026-04-24 15:52:55', '2026-04-24 15:53:17', '2026-04-24 15:52:55', '2026-04-24 15:53:17', 'UVR635', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('37', '1', 'CERRADA', '2026-04-21 15:17:14', '2026-04-21 15:22:15', '2026-04-21 15:17:14', '2026-04-21 15:22:15', 'DAHAH4', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('38', '1', 'ACTIVA', '2026-04-21 19:41:44', NULL, '2026-04-21 19:41:44', '2026-04-21 19:41:46', 'PSKNZW', 'INDIVIDUAL', NULL, '1', 'nrZj61cdkyr8BH9pqW0A5naqMQWomZ7DZHKwdoijDS2I2c0h7lcu5h8udVD5v145');
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('33', '1', 'CERRADA', '2026-04-17 15:52:25', '2026-04-17 16:24:38', '2026-04-17 15:52:25', '2026-04-17 16:24:38', 'QQCHNE', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('36', '1', 'CERRADA', '2026-04-17 21:39:38', '2026-04-20 23:14:21', '2026-04-17 21:39:38', '2026-04-20 23:14:21', 'NYE2WY', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('42', '2', 'CERRADA', '2026-04-24 15:55:29', '2026-04-24 15:55:37', '2026-04-24 15:55:29', '2026-04-24 15:55:37', 'XGZ3OG', 'INDIVIDUAL', 'manual', '1', NULL);
INSERT INTO public.sesiones_mesa (id, mesa_id, estado, fecha_inicio, fecha_cierre, created_at, updated_at, codigo_grupo, tipo_sesion, motivo_cierre, participantes_activos, token) VALUES ('44', '2', 'ACTIVA', '2026-04-27 12:40:06', NULL, '2026-04-27 12:40:06', '2026-04-27 12:40:30', 'RAM1LA', 'INDIVIDUAL', NULL, '1', 'eQI1NqWlyM5WTperfQPyw6SaLu4lJiwxhVGU21jeMFq3YqhEnRmWrliSi7lqpuqt');
INSERT INTO public.usuarios (id, nombre, email, password, rol_id, estado, ultimo_login, created_at, updated_at, remember_token) VALUES ('2', 'Jhonatan Castro Calderon', 'Shonano247@gmail.com', '$2y$12$4hwboMhwTxQy0VHJij0ri.BdD1jhEwG6TSqBmu/jf4jYNNSJ4XfUu', '3', 't', '2026-04-21 15:16:29', '2026-03-12 18:28:19', '2026-04-21 15:16:29', NULL);
INSERT INTO public.usuarios (id, nombre, email, password, rol_id, estado, ultimo_login, created_at, updated_at, remember_token) VALUES ('5', 'Jorge Motta', 'gorgo@gmail.com', '$2y$12$gT84M.yxcMiPogtP5WNF0.tFsVGlbsso.c4dHz8ok1ZtInxXsWDLi', '3', 't', '2026-04-21 15:19:09', '2026-03-24 20:38:04', '2026-04-21 15:19:09', NULL);
INSERT INTO public.usuarios (id, nombre, email, password, rol_id, estado, ultimo_login, created_at, updated_at, remember_token) VALUES ('6', 'Juan Trujillo', 'juanito@gmail.com', '$2y$12$q7KZX3xKO3zJOcp80iWqF.9oBrvboYsXPFJbayNsnqQ1Ke.AAjOaa', '2', 't', '2026-04-21 15:20:06', '2026-04-08 21:12:49', '2026-04-21 15:20:06', NULL);
INSERT INTO public.usuarios (id, nombre, email, password, rol_id, estado, ultimo_login, created_at, updated_at, remember_token) VALUES ('4', 'Emerson Corredor', 'parolin@gmail.com', '$2y$12$HnKDDqGVcGcyTGA8z44wC.4AP5lVec61deKUnPWJ3xOvCJXV35bXm', '3', 't', '2026-04-23 13:31:52', '2026-03-24 20:37:34', '2026-04-23 13:31:52', NULL);
INSERT INTO public.usuarios (id, nombre, email, password, rol_id, estado, ultimo_login, created_at, updated_at, remember_token) VALUES ('3', 'Julián Rivera', 'Julian123@gmail.com', '$2y$12$n3CUrjp8WHM/PGKAxopNj.dJvSoIjVMUlIJR3gSeubR7Pc7VjM/k.', '2', 't', '2026-04-10 12:42:09', '2026-03-12 18:28:52', '2026-04-10 12:42:09', NULL);
INSERT INTO public.usuarios (id, nombre, email, password, rol_id, estado, ultimo_login, created_at, updated_at, remember_token) VALUES ('1', 'Leider Fabian Ramos Cano', 'leiderfabianramoscano99@gmail.com', '$2y$12$/cgsH.ijlClINrlthO0l3OgjEd.qW2p9Nked9378YEegb9XSMj0oy', '1', 't', '2026-04-27 12:05:44', '2026-03-12 04:00:21', '2026-04-27 12:05:44', NULL);
INSERT INTO public.zona_coberturas (id, nombre, descripcion, costo_envio, tiempo_estimado, activo, created_at, updated_at) VALUES ('3', 'Zona Sur', 'Sector Timanco y Canaima', '4500.00', '35', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.zona_coberturas (id, nombre, descripcion, costo_envio, tiempo_estimado, activo, created_at, updated_at) VALUES ('4', 'Zona Centro', 'Casco urbano central y comercial', '2500.00', '15', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.zona_coberturas (id, nombre, descripcion, costo_envio, tiempo_estimado, activo, created_at, updated_at) VALUES ('5', 'Zona Occidente', 'Sector Galindo y Chicalá', '3500.00', '25', 't', '2026-04-26 22:56:27', '2026-04-26 22:56:27');
INSERT INTO public.zona_coberturas (id, nombre, descripcion, costo_envio, tiempo_estimado, activo, created_at, updated_at) VALUES ('1', 'Zona Norte', 'Sector Cándido y alrededores', '3000.00', '25', 't', '2026-04-26 22:56:27', '2026-04-26 23:16:01');
INSERT INTO public.zona_coberturas (id, nombre, descripcion, costo_envio, tiempo_estimado, activo, created_at, updated_at) VALUES ('2', 'Zona Oriente', 'Sector Las Palmas e Ipanema', '4000.00', '30', 'f', '2026-04-26 22:56:27', '2026-04-27 00:12:41');
SELECT pg_catalog.setval('public.barrio_domiciliario_id_seq', 2, true);
SELECT pg_catalog.setval('public.barrios_id_seq', 41, true);
SELECT pg_catalog.setval('public.carrito_items_id_seq', 32, true);
SELECT pg_catalog.setval('public.categorias_id_seq', 10, true);
SELECT pg_catalog.setval('public.detalle_pedidos_id_seq', 29, true);
SELECT pg_catalog.setval('public.domiciliarios_id_seq', 7, true);
SELECT pg_catalog.setval('public.historial_estado_pedidos_id_seq', 120, true);
SELECT pg_catalog.setval('public.inventarios_id_seq', 1, false);
SELECT pg_catalog.setval('public.mesas_id_seq', 13, true);
SELECT pg_catalog.setval('public.migrations_id_seq', 23, true);
SELECT pg_catalog.setval('public.movimientos_inventario_id_seq', 1, false);
SELECT pg_catalog.setval('public.notificaciones_id_seq', 1, false);
SELECT pg_catalog.setval('public.pagos_id_seq', 25, true);
SELECT pg_catalog.setval('public.pedidos_id_seq', 23, true);
SELECT pg_catalog.setval('public.productos_id_seq', 30, true);
SELECT pg_catalog.setval('public.report_schedules_id_seq', 1, true);
SELECT pg_catalog.setval('public.roles_id_seq', 1, false);
SELECT pg_catalog.setval('public.sesiones_id_seq', 188, true);
SELECT pg_catalog.setval('public.sesiones_mesa_id_seq', 44, true);
SELECT pg_catalog.setval('public.usuarios_id_seq', 6, true);
SELECT pg_catalog.setval('public.zona_coberturas_id_seq', 5, true);
ALTER TABLE ONLY public.barrio_domiciliario
    ADD CONSTRAINT barrio_domiciliario_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.barrios
    ADD CONSTRAINT barrios_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_sesion_mesa_id_producto_id_unique UNIQUE (sesion_mesa_id, producto_id);
ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.detalle_pedidos
    ADD CONSTRAINT detalle_pedidos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.domiciliarios
    ADD CONSTRAINT domiciliarios_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.historial_estado_pedidos
    ADD CONSTRAINT historial_estado_pedidos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.inventarios
    ADD CONSTRAINT inventarios_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.inventarios
    ADD CONSTRAINT inventarios_producto_id_unique UNIQUE (producto_id);
ALTER TABLE ONLY public.mesas
    ADD CONSTRAINT mesas_numero_unique UNIQUE (numero);
ALTER TABLE ONLY public.mesas
    ADD CONSTRAINT mesas_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.notificaciones
    ADD CONSTRAINT notificaciones_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.report_schedules
    ADD CONSTRAINT report_schedules_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_nombre_unique UNIQUE (nombre);
ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.sesiones_mesa
    ADD CONSTRAINT sesiones_mesa_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.sesiones_mesa
    ADD CONSTRAINT sesiones_mesa_token_unique UNIQUE (token);
ALTER TABLE ONLY public.sesiones
    ADD CONSTRAINT sesiones_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.sesiones
    ADD CONSTRAINT sesiones_token_unique UNIQUE (token);
ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_unique UNIQUE (email);
ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.zona_coberturas
    ADD CONSTRAINT zona_coberturas_pkey PRIMARY KEY (id);
CREATE INDEX report_schedules_next_run_at_index ON public.report_schedules USING btree (next_run_at);
CREATE INDEX sesiones_mesa_token_index ON public.sesiones_mesa USING btree (token);
ALTER TABLE ONLY public.barrio_domiciliario
    ADD CONSTRAINT barrio_domiciliario_barrio_id_foreign FOREIGN KEY (barrio_id) REFERENCES public.barrios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.barrio_domiciliario
    ADD CONSTRAINT barrio_domiciliario_domiciliario_id_foreign FOREIGN KEY (domiciliario_id) REFERENCES public.domiciliarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.barrios
    ADD CONSTRAINT barrios_zona_id_foreign FOREIGN KEY (zona_id) REFERENCES public.zona_coberturas(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.carrito_items
    ADD CONSTRAINT carrito_items_sesion_mesa_id_foreign FOREIGN KEY (sesion_mesa_id) REFERENCES public.sesiones_mesa(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.detalle_pedidos
    ADD CONSTRAINT detalle_pedidos_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.detalle_pedidos
    ADD CONSTRAINT detalle_pedidos_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id);
ALTER TABLE ONLY public.domiciliarios
    ADD CONSTRAINT domiciliarios_zona_id_foreign FOREIGN KEY (zona_id) REFERENCES public.zona_coberturas(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.historial_estado_pedidos
    ADD CONSTRAINT historial_estado_pedidos_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.historial_estado_pedidos
    ADD CONSTRAINT historial_estado_pedidos_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);
ALTER TABLE ONLY public.inventarios
    ADD CONSTRAINT inventarios_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id);
ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_inventario_id_foreign FOREIGN KEY (inventario_id) REFERENCES public.inventarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.notificaciones
    ADD CONSTRAINT notificaciones_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_mesero_id_foreign FOREIGN KEY (mesero_id) REFERENCES public.usuarios(id);
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_sesion_mesa_id_foreign FOREIGN KEY (sesion_mesa_id) REFERENCES public.sesiones_mesa(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES public.categorias(id);
ALTER TABLE ONLY public.sesiones_mesa
    ADD CONSTRAINT sesiones_mesa_mesa_id_foreign FOREIGN KEY (mesa_id) REFERENCES public.mesas(id);
ALTER TABLE ONLY public.sesiones
    ADD CONSTRAINT sesiones_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.roles(id);