# Aplicación de Gestión de Pedidos para Restaurantes con Domicilios

Este proyecto es un sistema web completo e integrado diseñado para optimizar y automatizar el flujo de pedidos en restaurantes y cafeterías. Permite la gestión eficiente de pedidos locales (en mesa a través de códigos QR) y pedidos a domicilio con asignación inteligente a domiciliarios y control de zonas de cobertura.

---

## 🛠️ Stack Tecnológico

El proyecto está construido sobre un stack moderno y eficiente:
- **Backend:** [Laravel](https://laravel.com/) (PHP)
- **Frontend Interactivo:** [Livewire](https://livewire.laravel.com/) (para una interfaz SPA reactiva sin recargas de página)
- **Estilos:** Vanilla CSS (personalizado para un diseño premium y moderno de tipo Glassmorphism) y componentes de TailwindCSS.
- **Base de Datos:**
  - **Producción / Desarrollo:** PostgreSQL
  - **Pruebas:** SQLite (en memoria para máxima velocidad de ejecución de tests)
- **Compilador de Assets:** [Vite](https://vite.dev/)

---

## 👥 Roles de Usuario y Módulos

El sistema cuenta con un control de acceso basado en roles (RBAC) con interfaces personalizadas y responsivas para cada perfil:

### 👑 1. Gerente
El rol con el nivel de acceso más alto en la organización:
- **Gestión Multi-Sucursal:** Control total para crear, editar y activar/desactivar diferentes sucursales o sedes de la empresa.
- **Reportes Globales:** Visualización de métricas consolidadas de ventas, volumen de pedidos y rendimiento general por sucursal.
- **Accesos Totales:** Administración y supervisión general de la plataforma.

### 🛡️ 2. Administrador
Responsable de la operación y configuración de una sucursal específica:
- **Gestión de Menú:** CRUD de categorías y productos (platos, bebidas) con carga de imágenes y control de precios.
- **Mesas y Códigos QR:** Registro de mesas de la sucursal y generación automática de códigos QR únicos descargables en formato PDF para que los clientes escaneen y ordenen.
- **Gestión de Pedidos:** Monitoreo del flujo de pedidos de la sucursal (locales y a domicilio). Permite filtrar por tipo y estado de orden.
- **Control de Usuarios:** Administración del personal asignado a la sucursal (meseros, cocineros, domiciliarios).
- **Asignación de Domicilios (Manual y Automática):** 
  - *Manual:* Selección directa del domiciliario disponible para una orden.
  - *Automática:* Algoritmo inteligente que asigna el pedido al domiciliario idóneo basándose en su estado de disponibilidad y su **zona preferencial**.
- **Zonas de Cobertura y Barrios:** Administración de las tarifas de envío, estimaciones de tiempo y barrios asociados.
- **Liquidación de Caja:** Arqueo diario, control de ingresos y cierre de caja de la sucursal.

### 🧑‍🍳 3. Cocina
Interfaz adaptada para tabletas o pantallas táctiles en la zona de producción:
- **Cola de Pedidos:** Visualización interactiva de pedidos pendientes en tiempo real ordenados por antigüedad.
- **Flujo de Preparación:** Actualización simple del estado del pedido ("En Preparación" -> "Listo para Servir/Despachar").
- **Disponibilidad de Insumos:** Alerta rápida para marcar productos como agotados temporalmente directamente desde el panel de cocina, impidiendo que los clientes o meseros los ordenen.

### 🤵 4. Mesero
Facilita la atención presencial en el establecimiento:
- **Comanda Digital:** Toma de pedidos interactiva directamente desde su dispositivo móvil o tableta al lado de la mesa del cliente.
- **Estados de Mesa:** Monitoreo y actualización del flujo de atención de sus mesas asignadas.
- **Sincronización Inmediata:** Los pedidos creados se reflejan instantáneamente en la pantalla de la Cocina.

### 🚴 5. Domiciliario
Panel diseñado exclusivamente para el personal de reparto a domicilio:
- **Mis Entregas:** Listado de pedidos asignados activos y completados.
- **Detalle del Despacho:** Acceso a información crítica del cliente (nombre, dirección exacta, barrio, zona de cobertura y teléfono de contacto para llamadas rápidas).
- **Navegación e Indicaciones:** Enlace directo para rutas de entrega.
- **Estado de Entrega:** Control del ciclo del envío ("En Camino" -> "Entregado").
- **Zona Preferencial:** Configuración de su zona geográfica de preferencia, lo cual optimiza y prioriza su perfil en la asignación automática de pedidos.

### 👤 6. Cliente
Experiencia digital fluida y sin fricciones:
- **Auto-Servicio en Mesa:** Escaneo de código QR en la mesa para ver la carta digital en tiempo real, armar el carrito de compras y registrar el pedido sin requerir asistencia física de un mesero.
- **Pedidos a Domicilio:** Acceso a la web de la sucursal, selección de zona/barrio de entrega, cálculo dinámico de tarifa de envío y registro de datos de envío.
- **Seguimiento en Tiempo Real:** Pantalla interactiva que muestra el estado actual de su pedido (Pendiente, En Cocina, En Camino, Entregado, etc.).

---

## 🗺️ Módulo de Zonas de Cobertura (Detalles de RF-141 a RF-145)

El sistema implementa un módulo robusto para gestionar el alcance de los despachos a domicilio:
- **CRUD Completo (RF-141):** Los administradores pueden gestionar las zonas de cobertura y asociar múltiples barrios en formato de texto separado por comas.
- **Tarifas Flexibles (RF-142):** Soporta costos de envío decimales/enteros y permite configurar envíos gratis (`$0`).
- **Control de Duplicados (RF-143):** Restringe la creación de zonas con nombres duplicados dentro de la misma sucursal para evitar confusiones operativas.
- **Activación y Desactivación Rápida (RF-144):** Switch interactivo desde la lista de zonas para desactivar temporalmente una zona de cobertura (por clima, alta demanda u otros factores) sin necesidad de borrar sus datos ni sus barrios.
- **Priorización Preferencial (RF-145):** Vincula las zonas de cobertura con los perfiles de los domiciliarios. El motor de asignación automática de pedidos prioriza al conductor cuya zona preferencial declarada coincida con la zona del pedido a entregar.

---

## 🚀 Instalación y Configuración

### Requisitos Previos
- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL o SQLite

### Pasos de Configuración
1. **Clonar el repositorio:**
   ```bash
   git clone <url-del-repositorio>
   cd cafeteria-app/cafeteria-app
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de JS:**
   ```bash
   npm install
   ```

4. **Configurar el entorno (.env):**
   Duplica el archivo de ejemplo y configura tu base de datos y llaves de correo (Gmail SMTP para alertas de pedidos):
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Correr las migraciones y seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Compilar assets de Frontend:**
   ```bash
   npm run build
   ```

---

## 💻 Ejecución en Desarrollo

Para ejecutar el servidor local y compilar los archivos de estilos/scripts en tiempo real:

1. **Servidor Laravel (PHP):**
   ```bash
   php artisan serve
   ```

2. **Servidor Vite (JS/CSS):**
   ```bash
   npm run dev
   ```

---

## 🧪 Pruebas Automatizadas

El proyecto cuenta con un completo banco de pruebas unitarias y de integración que validan el correcto funcionamiento de las reglas de negocio y los requisitos funcionales de cada rol.

Para correr todas las pruebas del proyecto:
```bash
php artisan test
```

Para correr únicamente el conjunto de pruebas del módulo de Zonas de Cobertura:
```bash
php artisan test tests/Feature/ManageZonasTest.php
```
