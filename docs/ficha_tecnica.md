# Ficha Técnica - Sistema de Gestión Café Bambú

## 1. Identificación del Producto
- **Nombre Comercial:** Sistema de Gestión y Pedidos Café Bambú
- **Tipo de Software:** Aplicación Web de Gestión de Restaurantes (SaaS / On-Premise)
- **Versión Actual:** 1.0.0
- **Público Objetivo:** Restaurantes, cafeterías y negocios gastronómicos con atención presencial de alto fluído.

## 2. Descripción General
Es un ecosistema integral de pedidos *contactless* diseñado para agilizar el flujo de operaciones en un restaurante físico. Permite que los clientes realicen pedidos desde su propio teléfono celular mediante códigos QR dinámicos, eliminando tiempos muertos. La información fluye en tiempo real hacia las áreas de preparación (cocina) y se integra nativamente con roles de atención (meseros) y de pago, bajo un esquema en vivo.

## 3. Arquitectura Tecnológica
El proyecto está desarrollado bajo el patrón **MVC (Modelo-Vista-Controlador)**.
- **Backend Framework:** Laravel 11.x (Escrito en PHP 8.1+)
- **Frontend Engine:** Blade Templating (Renderizado del lado del Servidor) 
- **Estilos y Scripts:** CSS y JavaScript Vanilla puro (Zero-dependency architecture en cliente para máxima velocidad).
- **Gestor de Paquetes Backend:** Composer
- **Gestor de Paquetes Frontend:** Node Package Manager (NPM) + Vite.js (Asset Bundling)
- **Modelo de Concurrencia:** Polling asíncrono optimizado y bloqueos de fila nativos en Base de Datos.

## 4. Requerimientos de Entorno (Servidor)
Para el despliegue funcional en producción, se requiere un servidor VPS, Cloud o compartido con:
- **Sistema Operativo:** Ubuntu 22.04 LTS o superior (Recomendado) / Windows Server
- **Motor Web:** Nginx o Apache 2.4+
- **Intérprete Core:** PHP 8.1 o superior
- **Extensiones PHP:** `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `xml`, `curl`
- **Base de Datos:** MySQL 8.0+ o MariaDB 10.6+

## 5. Integraciones de Terceros
- **Pasarela de Pagos:** Wompi (Bancolombia)
- **Protocolo de Validación:** Webhook mediante validación transaccional por firmas `HMAC sha256` asíncronas para garantizar invulnerabilidad contra redirecciones manipuladas.

## 6. Módulos y Roles del Sistema

### 6.1 Módulo Cliente (No requiere descarga / PWA ready)
- Autenticación transparente mediante URLs tokenizadas.
- Manejo de Sesiones Concurrentes (Múltiples clientes pueden compartir la cuenta global de una misma mesa visualizando pedidos separados).
- Catálogo virtual interactivo, visualizador del estado de preparación de comida en tiempo real e Integración de carrito de compras con Checkout Wompi.

### 6.2 Módulo de Operaciones (Mesero)
- **Balanceo Inteligente de Carga:** El algoritmo *Session Affinity Router* asigna todos los movimientos de una misma mesa física a un único empleado para evitar duplicación de esfuerzos y centralizar entregas.
- Finalización explícita granular (permite despedir clientes individuales en una mesa grande) liberando órdenes pendientes asociadas.

### 6.3 Módulo de Preparación (Cocina)
- Tableros tipo *Kanban* sin distracciones por temas de pago o administración. Exclusivo para gestión de estados *Preparando* y *Listo para entrega*.
- Actualización dinámica.

### 6.4 Módulo de Administración Integral (Dashboard)
- Entorno CRUD completo y tableros estadísticos para gestión de mesas, impresión de matrices QR, categorización dinámica del menú, catálogo fotográfico de productos.
- Panel de reclutamiento y asignación de contraseñas/cargos de Staff.
- Matriz completa de auditoría detallando los históricos de cada iteración, fechas, mesero asignado y estado financiero de los tickets del restaurante.

## 7. Modelos Críticos de Seguridad
- **Cierres Atómicos Transaccionales (Locks):** Uso directo de `lockForUpdate()` relacional al momento del escaneo del código QR para prevenir *Race-Conditions* e incursión masiva superando el límite de sillas de la mesa en tiempo de alta congestión.
- **Limpiador Lógico de Datos:** Política de Auto-limpieza de *Zombie-Orders* a nivel de backend: cualquier cierre de sesión ejecutado por un mesero aborta, a nivel base de datos, cualquier preparación colgada para frenar mermas y desperdicios físicos.
