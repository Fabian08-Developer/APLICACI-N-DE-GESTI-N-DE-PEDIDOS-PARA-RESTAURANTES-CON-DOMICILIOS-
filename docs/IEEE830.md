# Documento de Especificación de Requisitos de Software (SRS)

**Estandar:** IEEE 830-1998  
**Proyecto:** Cafe Bambu (Sistema Integrado de Gestión y Pedidos de Cafetería/Restaurante)  

---

## 1. Introducción

### 1.1 Propósito
El propósito de este documento es definir de forma clara y sin ambigüedades la especificación comprensiva de los requisitos de software del sistema web interactivo conocido como **"Cafe Bambu"**. Este SRS describe la funcionalidad base, diseño estructurado y el comportamiento externo requerido para la construcción, el mantenimiento y control del ciclo de vida de la plataforma digital. Va dirigido tanto a desarrolladores y mantenedores técnicos, como a usuarios corporativos involucrados en el proyecto.

### 1.2 Alcance
"Cafe Bambu" entra a operar como una aplicación integral web moderna fundamentada en las tecnologías (Laravel + Vite + PostgreSQL) diseñada para automatizar y optimizar los flujos integrados de un restaurante. El sistema abarca:
* Toma de pedidos autogestionados mediante un escaneo dinámico de códigos QR en las mesas del establecimiento.
* Solución "checkout" de pagos en línea que facilita y automatiza de forma escalable transacciones, interactuando con la pasarela financiera externa **Wompi** (Soporte Nequi y métodos variados).
* Centralización visual (Visualización en tiempo real) de las cuentas separadas de mesas.
* Entornos de gestión basados en roles para control y restricción de dependencias (Interfaces particulares para administradores, personal general de atención como meseros, e interfaces productivas dinámicas para el área de la cocina).

### 1.3 Definiciones, Acrónimos y Abreviaturas
* **SRS:** Especificación de Requisitos de Software (Software Requirements Specification).
* **POS:** Sistema de Punto de Venta (Point of Sale).
* **QR:** Código de Respuesta Rápida para accesos ultra rápidos (Quick Response).
* **AJAX (fetch):** Solicitudes en segundo plano para no interrumpir al usuario.
* **MVC:** Parádigma Modelo-Vista-Controlador.
* **Wompi webhook:** Rutas automatizadas o endpoints que aguardan la comunicación asíncrona de terceros sobre eventos financieros.

### 1.4 Referencias
* Estándar IEEE-830-1998 para Especificación de Requisitos de Software.
* Documentación Técnica y Arquitecturas de *Laravel 11.x*.
* Documentación Oficial de la pasarela y API RESTful corporativo de *Wompi Developers*.

### 1.5 Visión General
El documento subyacente expone primeramente un panorama genérico explicativo para enmarcar las limitantes y roles del sistema. Subsecuentemente, los Requisitos Específicos categorizan técnica y desgranadamente la lógica del mismo; divididos exhaustivamente en funcionales (Qué hace el software transaccionalmente), requerimientos de interfaces (Cómo interactúa hacia el marco exterior) y atributos no funcionales.

---

## 2. Descripción General

### 2.1 Perspectiva del Producto
El sistema "Cafe Bambu" es introducido como una solución autónoma, no está diseñado para empotrarse dentro de software existente, excepto en la capa de pagos, funcionando por su lado como un motor e-commerce en entorno de consumo local. El software es de perfil **Mobile First** para el consumidor (clientes y meseros operando en piso) y está diseñado bajo una arquitectura MVC mediante la cual la comunicación a la base de datos de PostgreSQL fluye estrictamente mapeado relacionalmente (ORM). 

### 2.2 Funciones Principales del Producto
* **Gestión Activa en la Mesa:** A los clientes se les arrojan identidades temporales de la base y pueden visualizar subcategorías amigables en tiempo real, añadiendo ítems a un carrito vivo de compras.
* **Integración Activa Wompi:** Procesamiento de pagos y cronómetros de expiración asíncronos en estado *"pendiente"*. Un Webhook a la escucha en el backend recibe las conformidades de pago, mutando automáticamente el estado de las órdenes en base de datos de forma silenciosa.
* **Plataforma de Administración y Estructura:** Generación de categorías, productos, manejo de perfiles personales y control robusto por autenticaciones de staff.
* **Estación de Cocina:** Pantallas amplias (tableros) con un flujo visual directo de estados ("Pendiente", "En Preparación", "Listo para Entregar").
* **Servicio y Soporte en Vivo (Meseros):** Herramientas para meseros que se especializan en controlar órdenes manuales, y liberar las mesas limpias y deshabitadas para nuevos visitantes en iteración continua.

### 2.3 Características de Usuarios
El sistema divide rígidamente responsabilidades mediante roles inalterables:
- **Administrador:** Posee el nivel de privilegios general, manipulación estructural de inventarios y datos pormenorizados.
- **Cliente (Transitorio):** Usuario final anónimo sin necesidad de registro ni descargas externas: accediendo únicamente escaneando la url única por código QR que valida en background pertenencia a la mesa operativa y procesa una orden mediante un dispositivo propio (Smartphone).
- **Mesero:** Personal dependiente, capacitado para tomar o modificar pedidos solicitados físicamente y gestionar el estado útil / cierre de sesiones en las mesas.
- **Cocinero:** Personal restringido sin acceso financiero, que requiere visualmente notificaciones (o monitores estáticos) grandes con los alimentos de alta prioridad para cambiar a estado "finalizado".

### 2.4 Entorno de Operación
El backend ejecuta y procesa la información valiéndose de `PHP 8.x` sobre el framework de Laravel, interconectado con un gestor PostgreSQL. Las interfaces o *Front-End* requieren exclusivamente navegadores de última era complacientes a las normativas de HTML5, CSS3, e interprete para ES6 JavaScript.

### 2.5 Restricciones y Limitaciones de Diseño
* Es obligatoria y absolutamente ineludible una conexión concurrente y funcional a la red (Internet y Wifi Local) debido a los puentes obligados hacia Wompi y al almacenamiento persistente.
* Los tiempos muertos y respuestas dependen en gran porción en la fluidez de interacción con bancos y sistemas (v.g. *Nequi*).
* El renderizado base y hojas de cascada usan Vanilla CSS apoyados en el flujo y empaquetador del módulo `Vite.js`.

---

## 3. Requisitos Específicos

En el apartado siguiente se enumera de manera concisa aquellos eventos computacionales imperativos.

### 3.1 Requisitos Funcionales (RF)

* **RF01 - Autenticación Selectiva (Staff):** Los usuarios tipo staff se enfrentarán a procesos `POST /login` verificables, creando llaves de sesiones transaccionales por ventana en lugar de globales para facilitar flujos cruzados de operaciones. 
* **RF02 - Sesión de Cliente Ininterrumpida en Mesas:** El backend debe inicializar con el request GET inicial hacia el QR con el `ID de la Mesa` un "token" seguro que el cliente conserva transparentemente a fin de mitigar "pedidos falsos remotos". 
* **RF03 - CRUD e Inventario:** Administradores autorizados deben ser capaces de manipular y guardar persistencia tanto de perfiles de roles, como artículos culinarios y sub-categorías asociadas vía dashboards de formularios.
* **RF04 - Proceso y Expirado de CheckOut en Cliente:** Cuando un pedido alcanza flujo final, el sistema requiere mostrar pantallas temporizadas persistentes ("Pago Pendiente"); este conteo atrás del tiempo muerto de pago no puede perderse o reiniciarse ante recargas accidentales, interactuando con el almacenamiento local / session.
* **RF05 - Recepción Externa de Autoridad (Webhook):** El controlador debe responder firmemente, interpretar firmas o sellos SHA e integridades de evento enviadas independientemente por Wompi y afectar la tabla `Pedidos / Historial` sin inmutar el front hasta recargar de manera asíncrona la vista objetivo de éxito.
* **RF06 - Notificaciones hacia la Operación de Cocina:** Se emitirán a través de las plantillas (Blade y JS) solicitudes regulares consultando la base de datos central que dicten de forma asíncrona los alimentos en espera.
* **RF07 - Acción de Desplazamiento Integral de Meseros:** Un mesero podrá invocar rutas especializadas o botones directos que declaren la "liberación y cierre local del pedido" que vuelve la mesa o sesión nuevamente inmaculada.

### 3.2 Requisitos No Funcionales y Atributos Generales (RNF)

* **RNF01 (Rendimiento | Polling Dinámico):** Los cronómetros en progreso, "pagos pendientes", deben consultar su mutación de estados y validación de cobro bancario de manera reiterativa con demoras de `(1 a 5 ms)`, expandiéndose gradualmente (`interval polling backs`) hacia retardos más altos si se detectan pérdidas de la conectividad de red con el objeto de no sobre saturar o colapsar el servidor PHP general.
* **RNF02 (Seguridad & Encriptado):** Uso forzado de un `APP_KEY` seguro y validaciones `Bcrypt` obligatorias para proteger contraseñas. Implementación y revalidación de las protecciones o directivas `CSRF (Cross-Site Request Forgery)` garantizando transacciones y accesos completamente sanos. Inactivación de cuentas deshabilitadas forzada a lo largo de los middleware de entrada del sistema.
* **RNF03 (Escalabilidad de los Controladores):** El uso nativo del marco arquitectónico MVC de Laravel permite que nuevos métodos o puertas de enlace de pago pasarelas alternativas transaccionen, puedan migrar o fusionarse sin comprometer la limpieza y fluencia funcional de módulos primarios (cocina-mesero).
* **RNF04 (Experiencia Visual, UI/UX):** El marco conceptual del portal se adherirá al principio visual minimalista y altamente receptivo (Vistas Glassmorphism, sombreados limpios, Darkmodes relativos) impulsados mediante el set particular en CSS3 y transiciones nativas y orgánicas.
