# Guía de Despliegue en Railway

¡He preparado tu proyecto para ser desplegado en Railway! El sistema reconocerá automáticamente que es un proyecto Laravel y usará Nixpacks para instalar las dependencias y configurarlo.

## 1. Archivos Creados
- `railway.toml`: Le indica a Railway cómo debe construir la aplicación.
- `Procfile`: Archivo de respaldo por si en el futuro decides usar buildpacks.
- `schema.sql`: Tu base de datos local exportada (estructura y datos).

## 2. Pasos en el panel de Railway
1. Sube este proyecto a tu repositorio de GitHub.
2. En Railway, dale a **New Project** -> **Deploy from GitHub repo**.
3. Añade también un nuevo servicio de base de datos: **New** -> **Database** -> **Add PostgreSQL**.
4. Importa el archivo `schema.sql` a tu nueva base de datos PostgreSQL de Railway usando la pestaña **Data** (puedes ejecutar consultas ahí o usar un cliente como pgAdmin/DBeaver conectado a Railway).

## 3. Variables de Entorno (Environment Variables)
Ve a tu proyecto web en Railway, entra en **Variables**, y añade las siguientes (copiadas de tu `.env` local, adaptadas para producción):

```env
APP_NAME="Cafeteria"
APP_ENV=production
APP_KEY=base64:UpYBPJG7gR2LftmCJs4kwCy5r1Q87KowyuXdsihLlR0=
APP_DEBUG=false
APP_URL=https://TU-URL-DE-RAILWAY.up.railway.app

# La base de datos (Railway te proporciona estas variables en la sección "Connect" de tu PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

# Configuración de sesión y caché
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Wompi (si aplica)
WOMPI_BASE_URL=https://production.wompi.co/v1
WOMPI_PUBLIC_KEY=tu_llave_publica
WOMPI_PRIVATE_KEY=tu_llave_privada

# Correo electrónico
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=leiderfabianramoscano99@gmail.com
MAIL_PASSWORD="pjct mxnp gbbx kibl"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=leiderfabianramoscano99@gmail.com
MAIL_FROM_NAME="Restaurante"
```

## 4. Archivos Subidos (Almacenamiento)
Como Railway es un sistema "efímero", los archivos subidos (como las imágenes de los productos) en `storage/app/public` se borrarán con cada nuevo despliegue. 
**Recomendación:** Más adelante, deberás configurar AWS S3 o Cloudinary en el sistema para almacenar las imágenes de manera persistente. Por ahora, las imágenes que importaste funcionarán si las subes directamente.
