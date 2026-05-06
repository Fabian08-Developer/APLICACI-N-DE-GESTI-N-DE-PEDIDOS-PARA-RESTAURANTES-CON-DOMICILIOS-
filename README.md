# fix/security: Implementación completa de seguridad, RBAC y control de acceso

## Rama
`fix/security-rbac-access-control`

## Tipo de cambio
`fix` + `feat` — Corrección de vulnerabilidades críticas e implementación de arquitectura
de seguridad por capas con control de acceso basado en roles (RBAC).

---

## Resumen ejecutivo

Se implementó un modelo de defensa en profundidad de 5 capas independientes que cubre
autenticación, autorización por rol, control de ownership de recursos, protección de
sesiones y hardening de la superficie de ataque. Antes de estos cambios, todas las rutas
internas eran accesibles sin autenticación y cualquier usuario autenticado podía acceder
a recursos de otros usuarios cambiando el ID en la URL.

---

## Vulnerabilidades corregidas

| Severidad | Vulnerabilidad | Estado |
|-----------|---------------|--------|
| CRÍTICA | Rutas `/admin`, `/mesero`, `/cocina` sin ningún middleware | ✅ Corregido |
| CRÍTICA | `session()` directo en lugar de `Auth::login()` — mezcla de sesiones | ✅ Corregido |
| CRÍTICA | Sin `session()->regenerate()` post-login — session fixation abierta | ✅ Corregido |
| CRÍTICA | `config/auth.php` apuntaba a `App\Models\User` (inexistente) | ✅ Corregido |
| ALTA | Sin control de roles — mesero podía acceder a `/admin/*` por URL directa | ✅ Corregido |
| ALTA | Modelo `SubSesion` importado en middleware pero eliminado del proyecto | ✅ Corregido |
| ALTA | `logout()` solo hacía `flush()` — session ID reutilizable post-logout | ✅ Corregido |
| ALTA | Sin ownership de recursos — cliente A podía ver pedidos del cliente B | ✅ Corregido |
| MEDIA | Race condition en `limpiarSesionCliente()` por UPDATE masivo con LIKE | ✅ Corregido |
| MEDIA | `remember_token` no estaba en `$hidden` del modelo Usuario | ✅ Corregido |
| MEDIA | `GET /register` apuntaba al método `mostrarLogin()` incorrecto | ✅ Corregido |
| MEDIA | Usuarios autenticados podían volver a `/login` sin redireccionamiento | ✅ Corregido |

---

## Arquitectura implementada — modelo de defensa en profundidad

Cada request pasa por 5 capas independientes antes de llegar a la lógica de negocio.
Si cualquier capa falla, el request es rechazado sin llegar a las capas siguientes.

```
Request HTTP
    │
    ▼
[Capa 1] CSRF Middleware (nativo Laravel)
    │  Bloquea POST/PUT/DELETE sin token válido → 419
    ▼
[Capa 2] VerificarAutenticacion
    │  Auth::check() + token activo en BD + consistencia usuario_id → /login
    ▼
[Capa 3] VerificarRol
    │  Rol del usuario coincide con la ruta solicitada → 403
    ▼
[Capa 4] VerificarOwnership  (rutas de cliente con ID de recurso)
    │  El recurso pertenece a la sesion_mesa activa → 404
    ▼
[Capa 5] Lógica de negocio (controladores)
    │  Ownership adicional en queries (mesero_id = Auth::id())
    ▼
  Respuesta
```

**Flujo QR paralelo:**
```
GET /mesa/{codigo}
    │
    ▼
[ClienteSessionMiddleware] → SesionMesa activa en BD
    │
    ▼
[VerificarOwnership] → recurso pertenece a sesion_mesa_id en sesión
    │
    ▼
Lógica de negocio del cliente
```

---

## Archivos nuevos

### `app/Http/Middleware/VerificarAutenticacion.php`
Middleware de autenticación con triple verificación:
1. `Auth::check()` — Laravel reconoce al usuario en la sesión actual.
2. Token personalizado activo en tabla `sesiones` — permite revocar sesiones
   de forma centralizada desde el panel admin sin esperar expiración.
3. `sesion->usuario_id === Auth::id()` — el token pertenece al usuario
   autenticado, no a otro (previene reuso de tokens robados).

Adicionalmente renueva automáticamente la fecha de expiración del token
cuando quedan menos de 60 minutos, evitando cortes inesperados durante
el turno de trabajo.

Soporta respuestas JSON para requests AJAX (código 401) además de
redirección HTML.

---

### `app/Http/Middleware/VerificarRol.php`
Middleware de autorización por rol con jerarquía RBAC:
- Recibe uno o más roles como parámetro: `->middleware('rol:mesero,cocina')`.
- El rol `administrador` siempre tiene acceso sin necesidad de listarlo
  explícitamente (jerarquía RBAC: el superior hereda todos los permisos).
- Soporta respuestas JSON para AJAX (código 403).
- Usa `abort(403)` con mensaje genérico — no revela la estructura de rutas.

---

### `app/Http/Middleware/VerificarOwnership.php` *(capa de seguridad nueva)*
Previene que un cliente acceda a recursos de otro cliente cambiando el ID
en la URL. Verifica que el `pedido_id` o `pago_id` solicitado pertenezca
a la `sesion_mesa_id` guardada en la sesión PHP activa.

- Parámetro de uso: `->middleware('cliente.ownership:pedido')` o `:pago`.
- Lee el ID del recurso desde los parámetros de ruta (`{pedidoId}`, `{pagoId}`).
- Si el recurso no existe o no pertenece a la sesión: responde **404**, no 403.
  Justificación: 403 confirmaría que el recurso existe. 404 no revela información.
- Rutas sin ID específico (como `/pedido/confirmar`) pasan sin verificación.

---

### `app/Http/Middleware/GuestOnly.php`
Previene que un usuario ya autenticado acceda a `/login` o `/register`.
Sin este middleware, un usuario con sesión activa podía iniciar un segundo
login y generar estado de sesión ambiguo.

Redirige al dashboard correspondiente según el rol del usuario autenticado.

---

## Archivos modificados

### `config/auth.php`
- **Antes:** Provider `users` apuntaba a `App\Models\User::class` (modelo inexistente).
  `Auth::login()` fallaba silenciosamente porque el guard no podía rehidratar al usuario.
- **Después:** Provider renombrado a `usuarios`, modelo cambiado a
  `App\Models\Usuario::class`. Password broker actualizado en consecuencia.

```php
// Antes
'model' => env('AUTH_MODEL', App\Models\User::class)

// Después
'model' => App\Models\Usuario::class
```

---

### `app/Models/Usuario.php`
- Añadido `remember_token` a `$hidden` — estaba expuesto en serialización JSON.
- Añadido `use Notifiable` — requerido por `Authenticatable` para notificaciones.
- Añadido `$casts` para `estado` (boolean) y `ultimo_login` (datetime).

> **Acción requerida:** Si la tabla `usuarios` no tiene la columna `remember_token`,
> crear y ejecutar la siguiente migración:
> ```php
> // database/migrations/xxxx_add_remember_token_to_usuarios_table.php
> $table->rememberToken(); // varchar(100) nullable
> ```
> ```bash
> php artisan migrate
> ```

---

### `app/Http/Controllers/AuthController.php`

**Método `login()`:**
- Reemplazado `session(['usuario_id' => ..., 'usuario_nombre' => ...])` por
  `Auth::login($usuario)`. El guard de Laravel gestiona el aislamiento de sesión
  por cookie — cada browser tiene su propio session ID independiente.
- Añadido `$request->session()->regenerate()` inmediatamente después del login.
  Previene session fixation: si alguien capturó el session ID antes del login,
  ese ID queda invalidado.
- La sesión PHP ahora solo guarda `token_usuario`. Nombre, rol e id del usuario
  se leen desde `Auth::user()` en cada request, siempre frescos desde la BD.
- Añadida redirección automática si el usuario ya está autenticado al visitar `/login`.

**Método `logout()`:**
- Reemplazado `$request->session()->flush()` por la secuencia correcta:
  ```php
  Auth::logout();                         // 1. Desautentica del guard
  $request->session()->invalidate();      // 2. Destruye datos de sesión
  $request->session()->regenerateToken(); // 3. Nuevo token CSRF
  ```
  `flush()` solo borraba los datos pero mantenía el session ID activo,
  permitiendo su reutilización.

**Método `register()`:**
- Añadida validación para impedir registrar usuarios con rol `cliente` desde
  el formulario de staff.

**General:**
- Extraída lógica de redirección por rol al método privado `redirigirSegunRol()`.
- Corrección de `GET /register` que apuntaba a `mostrarLogin()` en lugar de
  `registerForm()`.

---

### `app/Http/Middleware/ClienteSessionMiddleware.php`
- **Eliminadas** todas las referencias a `App\Models\SubSesion` (modelo inexistente).
- **Corregida** la restauración de sesión por token: ahora busca directamente en
  `sesiones_mesa` donde el campo `token` existe según el ERD, en lugar de buscar
  en `SubSesion` con una FK circular inexistente (`sesion_mesa_id` sobre sí misma).
- **Eliminado** el bloque de `limpiarSesionCliente()` que hacía `UPDATE` masivo
  sobre `usuarios` con condición `email LIKE 'temp_%'` — race condition que podía
  afectar registros de sesiones concurrentes.
- Datos guardados en sesión PHP reducidos a IDs (`sesion_mesa_id`, `mesa_id`).
  Nunca datos denormalizados que puedan quedar desactualizados entre requests.
- Lógica de inactividad y verificación de estado en BD extraídas a métodos
  privados (`verificarInactividad`, `intentarRestaurarSesion`).
- Verificación activa de que la `SesionMesa` sigue en estado `ACTIVA` en BD
  en cada request — detecta cierres remotos por el mesero o el admin.

---

### `routes/web.php`
Reestructuración completa con middlewares aplicados correctamente.

**Antes:** Ningún grupo de rutas tenía middleware.
**Después:**

| Grupo | Middlewares |
|-------|-------------|
| `GET /login`, `GET /register` | `guest.only` |
| `POST /logout` | `auth.custom` |
| `cliente/*` (sesión requerida) | `ClienteSessionMiddleware` |
| `cliente/pedido/{id}`, `cliente/pedido/{id}/cancelar` | `ClienteSessionMiddleware` + `cliente.ownership:pedido` |
| `cliente/pago/{id}/*` | `ClienteSessionMiddleware` + `cliente.ownership:pago` |
| `admin/*` | `auth.custom` + `rol:administrador` |
| `mesero/*` | `auth.custom` + `rol:mesero` |
| `cocina/*` | `auth.custom` + `rol:cocina` |

Rutas de inicio de sesión QR (`/sesion/individual`, `/sesion/compartida/*`)
quedan fuera del middleware de sesión — son el punto de entrada, no requieren
sesión previa.

---

### `bootstrap/app.php`
Registrados los aliases de todos los middlewares nuevos:

```php
$middleware->alias([
    'auth.custom'       => VerificarAutenticacion::class,
    'rol'               => VerificarRol::class,
    'cliente.sesion'    => ClienteSessionMiddleware::class,
    'cliente.ownership' => VerificarOwnership::class,
    'guest.only'        => GuestOnly::class,
]);
```

Registradas respuestas personalizadas para errores 403 y 404 que soportan
tanto HTML como JSON según el tipo de request.

---

## Patrón de ownership en controladores

Además de los middlewares, se aplicó ownership a nivel de query en controladores:

```php
// Mesero — solo ve sus propios pedidos asignados
Pedido::where('mesero_id', Auth::id())->get();

// Al obtener un recurso específico — firstOrFail() lanza 404 automáticamente
Pedido::where('id', $id)
      ->where('mesero_id', Auth::id())
      ->firstOrFail();
```

Reemplazado `find($id)` por `findOrFail($id)` en todos los controladores
donde el recurso debe existir obligatoriamente.

---

## Matriz de permisos implementada

```
                                  Admin  Mesero  Cocina  Cliente
Ver todos los pedidos               ✅    ✅*     ✅**     ❌
Ver su propio pedido                ✅     ✅      ✅      ✅
Crear pedido                        ✅     ✅      ❌      ✅
Cambiar estado de pedido            ✅    ✅*     ✅**     ❌
Cancelar pedido                     ✅     ✅      ❌     ✅***
Gestionar usuarios                  ✅     ❌      ❌      ❌
Gestionar productos / categorías    ✅     ❌      ❌      ❌
Gestionar mesas y QR                ✅     ❌      ❌      ❌
Ver menú                            ✅     ✅      ✅      ✅
Ver estado de pago                  ✅     ✅      ❌     ✅***

  *   Solo pedidos donde mesero_id = Auth::id()
 **   Solo visualización, sin edición de datos
***   Solo recursos vinculados a su sesion_mesa_id activa
```

---

## Patrón de uso en vistas — migración de session() a Auth::user()

```php
// ❌ Patrón anterior — eliminar de todas las vistas y controladores
session('usuario_nombre')
session('usuario_rol')
session('usuario_id')

// ✅ Patrón correcto
auth()->user()->nombre
auth()->user()->rol->nombre
auth()->user()->id

// En Blade
{{ auth()->user()->nombre }}
@auth ... @endauth
@guest ... @endguest
```

---

## Comandos a ejecutar después del merge

```bash
# Limpiar caché de configuración, rutas y vistas
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Ejecutar solo si se añadió la columna remember_token
php artisan migrate

# Verificar que los middlewares aparecen correctamente en cada ruta
php artisan route:list --columns=method,uri,middleware
```

---

## Checklist de verificación post-deploy

### Autenticación
- [ ] Dos browsers con usuarios distintos no comparten nombre ni datos de sesión.
- [ ] Visitar `/login` con sesión activa redirige al dashboard del usuario.
- [ ] El logout invalida el session ID — recargar después del logout no mantiene sesión.
- [ ] El token en tabla `sesiones` queda `activa = false` después del logout.

### Autorización por rol
- [ ] Un mesero autenticado recibe 403 al acceder a `/admin/dashboard`.
- [ ] Un usuario de cocina no puede acceder a `/mesero/dashboard`.
- [ ] Un usuario no autenticado es redirigido a `/login` en cualquier ruta protegida.
- [ ] El administrador puede acceder a rutas de mesero y cocina sin 403.

### Ownership de recursos (cliente QR)
- [ ] Cambiar el `pedidoId` en la URL por el de otra mesa retorna 404, no los datos.
- [ ] Cambiar el `pagoId` en la URL por el de otra sesión retorna 404.
- [ ] El cliente puede ver el estado de su propio pedido correctamente.

### Sesión de cliente QR
- [ ] Tras 15 minutos de inactividad el cliente es redirigido y la mesa queda DISPONIBLE.
- [ ] Cerrar la sesión desde el mesero invalida el acceso del cliente inmediatamente.
- [ ] Dos clientes en mesas distintas no comparten carrito ni pedido.

### Verificación técnica
- [ ] `php artisan route:list` muestra middlewares en cada grupo de rutas.
- [ ] Requests AJAX a rutas protegidas reciben JSON con código 401 o 403, no HTML.
- [ ] Webhook de Wompi sigue funcionando sin CSRF pero con verificación de firma.
