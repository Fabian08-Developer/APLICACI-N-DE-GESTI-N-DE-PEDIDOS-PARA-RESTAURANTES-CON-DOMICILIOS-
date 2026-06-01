# 📁 views/admin/ — Propósito de esta carpeta

Esta carpeta contiene **vistas Blade de renderizado interno** utilizadas para generar documentos
como PDFs y códigos QR. **NO son vistas del panel de administración**.

---

## ⚠️ Distinción importante

| Carpeta | Propósito |
|---|---|
| `resources/views/admin/` | Plantillas para **generación de PDF/QR** (exportación de datos) |
| `resources/views/livewire/admin/` | **Vistas del panel de administración** (componentes Livewire interactivos) |

---

## Contenido actual

| Archivo / Carpeta | Descripción |
|---|---|
| `mesas/pdf-qr.blade.php` | Plantilla PDF para el código QR de una mesa |
| `reportes/excel.blade.php` | Plantilla de exportación de reportes a Excel |
| `reportes/pdf.blade.php` | Plantilla de exportación de reportes a PDF |

---

## Uso

Estas vistas son utilizadas por controladores o jobs que usan librerías como
`barryvdh/laravel-dompdf` o `maatwebsite/excel` para generar y descargar archivos.
No están vinculadas a rutas web directas ni a componentes Livewire.
