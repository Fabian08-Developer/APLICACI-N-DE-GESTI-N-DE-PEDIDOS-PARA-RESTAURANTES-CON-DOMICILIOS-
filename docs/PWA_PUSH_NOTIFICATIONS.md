# Documentación Técnica Completa: PWA y Notificaciones Web Push

Este documento describe a profundidad la arquitectura, configuración, conexión con terceros y el flujo funcional de los módulos de PWA (Progressive Web App) y Notificaciones Web Push implementados en el sistema de gestión de pedidos. 

---

## 1. Módulo PWA (Progressive Web App)

El sistema cuenta con soporte completo de PWA para permitir la instalación de la aplicación web como una aplicación nativa de escritorio o móvil, e incluye capacidades de funcionamiento *offline-first*.

### Archivos Principales
*   **`public/manifest.json`**: Define el comportamiento, colores, íconos de distintos tamaños y el nombre de la aplicación cuando se instala en un dispositivo o se añade a la pantalla de inicio.
*   **`public/sw.js` (Service Worker Principal):** Actúa como un proxy de red en el navegador interceptando peticiones, controlando la caché y escuchando los eventos de notificaciones push en segundo plano.
*   **`resources/views/layouts/admin.blade.php`**: Inyecta la lógica de registro del Service Worker en las vistas de administración (a través de `resources/js/echo-setup.js`), asegurando que la PWA se inicialice al cargar el panel de administración.

### Estrategias de Caché (Soporte Offline)
El Service Worker (`sw.js`) emplea dos estrategias de caché principales para asegurar la resiliencia del sistema frente a cortes de conexión a internet o fallas de red:

1.  **Cache-First (Para Assets Estáticos):**
    Los archivos que raramente cambian (Imágenes, íconos, CSS, JS y fuentes tipográficas) se intentan cargar primero desde la caché local del dispositivo. Si no existen localmente, se descargan del servidor y se guardan en la caché para el futuro.
2.  **Network-First con Fallback Offline (Para HTML y Rutas API):**
    Para la navegación normal, se intenta obtener siempre la última versión desde el servidor. Si el dispositivo pierde la conexión a internet y la petición falla, el Service Worker intercepta el error y retorna la última versión guardada en caché (si existe) o una página genérica de offline, permitiendo que la interfaz siga viva.

---

## 2. Módulo de Notificaciones Web Push

Las Notificaciones Web Push permiten que el administrador y los meseros reciban alertas pop-up a nivel de sistema operativo de forma instantánea, incluso si la pestaña de la aplicación está cerrada o minimizada.

### Conexión con Terceros y Criptografía (VAPID)
A diferencia de los sistemas tradicionales que requieren servicios costosos (como Pusher o Firebase de paga), este sistema utiliza el protocolo libre **VAPID** (Voluntary Application Server Identification) junto con las APIs nativas de los navegadores.

*   **Librería Backend de Terceros:** Se integró el paquete de Composer `minishlink/web-push` (v10.1.0). Este paquete se encarga de crear el payload seguro y encriptar los mensajes antes de enviarlos a los servidores nativos de notificaciones.
*   **Servidores Push (Terceros Nativos):** El sistema se comunica directamente con los servidores de notificaciones del fabricante del navegador web del usuario (ej. Google FCM para Chrome, Mozilla Push Service para Firefox, Windows Push Notification Service para Edge, y Apple Push Notification service para Safari).

### ¿Cómo se generan las Claves VAPID?
Para que el navegador (y los servidores de Google/Apple) confíen en las notificaciones de nuestro backend, requerimos un par de claves criptográficas asimétricas. 
*   **Problema de Compatibilidad en Windows:** La generación nativa vía `openssl_pkey_new` en PHP falla en entornos Windows (Laragon/XAMPP) al tratar de utilizar curvas elípticas (EC `P-256`).
*   **La Solución Implementada:** Se programó un script secundario en NodeJS (`generate-vapid.mjs`) que hace uso de la librería criptográfica nativa de V8 (`crypto`). Este script genera el par de claves seguras y automáticamente inyecta los siguientes valores en el archivo `.env`:
    *   `VAPID_PUBLIC_KEY` (Clave pública que se expone al cliente)
    *   `VAPID_PRIVATE_KEY` (Clave privada ultra-secreta usada por Laravel para firmar el payload)
    *   `VITE_VAPID_PUBLIC_KEY` (Alias inyectado a Vite para que el frontend pueda leer la llave pública al momento de suscribir el navegador).

### Modelos y Base de Datos (Estructura Multi-Tenant)
*   **Tabla en BD:** `push_subscriptions`
    *   Dado que el sistema tiene una arquitectura multi-tenant y la tabla de usuarios se llama `usuarios` utilizando claves primarias tipo **UUID** (no enteros), la migración fue adaptada específicamente para referenciar `char(36)` o `uuid`.
    *   **Esquema de la tabla:**
        *   `id`: Clave primaria autoincremental (BigInt).
        *   `user_id`: Clave foránea tipo `uuid` que referencia a `usuarios.id` en cascada (Cascade On Delete).
        *   `endpoint`: Texto único (URL del servidor de Google/Mozilla).
        *   `public_key`: Varchar(512) para guardar la clave pública ECDH del navegador.
        *   `auth_token`: Varchar(512) para guardar el secreto compartido generado por el navegador.
        *   `content_encoding`: Varchar(32) por defecto `aesgcm`.
        *   `timestamps`: Creado y actualizado por defecto.
*   **Modelo Eloquent:** `App\Models\PushSubscription`.
    *   Guarda tres valores cruciales proporcionados por el navegador: el `endpoint` (URL del servidor de Google/Apple), la `public_key` del dispositivo y el `auth_token`.

### Rutas API y Controladores
*   **`POST /push/subscribe`:** (Gestionado por `PushSubscriptionController@store`). Recibe los datos criptográficos desde Javascript y los almacena en la tabla de la base de datos vinculados al usuario que inició sesión.
*   **`POST /push/unsubscribe`:** Elimina el endpoint de la base de datos si el usuario revoca los permisos, su suscripción caduca, o si el usuario cierra la sesión.

---

## 3. Flujo Funcional y Ciclo de Vida de una Notificación

1.  **Registro y Solicitud de Permisos (Frontend):**
    *   Cuando un admin o mesero ingresa al panel, se ejecuta el script `resources/js/echo-setup.js`.
    *   El navegador registra el Service Worker (`sw.js`).
    *   Utilizando la `Push API` del navegador, se lanza un pop-up nativo pidiendo al usuario permiso para enviarle notificaciones.
2.  **Generación de la Suscripción:**
    *   Si el usuario hace clic en "Permitir", Javascript utiliza la `VITE_VAPID_PUBLIC_KEY` para solicitar una suscripción segura al servidor de Google/Mozilla.
    *   El servidor de Google/Mozilla responde con un `endpoint` temporal único y llaves de encriptación.
    *   Estos datos se envían de forma asíncrona mediante Axios al endpoint de nuestro servidor (`POST /push/subscribe`).
3.  **Disparador de Eventos (Backend - Ej: Nuevas Reservas):**
    *   Ocurre un evento de negocio. Ejemplo: El cliente hace una reserva online.
    *   La clase `ReservaService` guarda la reserva y luego invoca la función para notificar al restaurante.
    *   El `WebPushService` busca en la BD todas las suscripciones activas del personal de esa sucursal, toma el mensaje, lo encripta firmándolo con la `VAPID_PRIVATE_KEY` y envía el request HTTP directo a la URL (`endpoint`) del servidor de notificaciones (ej. FCM).
4.  **Recepción y Renderizado en el Dispositivo:**
    *   El servidor de Google/Apple recibe el mensaje cifrado y hace "Push" silencioso hacia el navegador del empleado por una conexión de sockets nativa.
    *   El Service Worker (`sw.js`) en el computador del empleado despierta (incluso si la web está cerrada), desencripta el *payload* y lanza un aviso usando `self.registration.showNotification`.

---

## 4. Dependencias y Consideraciones Críticas para Producción

> [!WARNING]
> **Requisito Crítico: Certificado SSL (HTTPS)**
> Por motivos de seguridad web estandarizados mundialmente, las APIs de Service Workers, PWA y Push Notifications **NO FUNCIONAN en entornos HTTP**. La única excepción permitida por los navegadores es `http://localhost` para desarrollo. Cuando el sistema sea desplegado a un servidor en internet (Producción/Staging), es estrictamente obligatorio que el dominio tenga instalado un certificado SSL (HTTPS válido), de lo contrario la PWA nunca se instalará y las notificaciones serán bloqueadas.

> [!IMPORTANT]
> **Motor de Tareas Automáticas (Cron Job)**
> Las reservas que expiran y los envíos de notificaciones rezagados requieren que el servidor backend procese tareas en segundo plano. 
> *   En el entorno local de desarrollo, siempre debes ejecutar: `php artisan schedule:work`
> *   En el servidor de producción (Ubuntu/Linux), el administrador del servidor **debe** configurar el *Cron Job* maestro editando `crontab -e` e insertando la siguiente línea (ajustando la ruta al proyecto):
>     `* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1`
