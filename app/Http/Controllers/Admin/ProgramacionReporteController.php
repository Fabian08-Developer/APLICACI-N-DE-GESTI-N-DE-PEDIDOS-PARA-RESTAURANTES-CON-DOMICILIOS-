<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramacionReporte;
use App\Livewire\Admin\Reportes\ManageReportes;
use App\Mail\ReporteProgramadoMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ProgramacionReporteController extends Controller
{
    /**
     * GET /admin/reportes/programacion
     * Devuelve las programaciones activas de la sucursal del usuario autenticado.
     */
    public function index(): JsonResponse
    {
        $sucursalId = auth()->user()->sucursal_id;

        $programaciones = ProgramacionReporte::where('sucursal_id', $sucursalId)
            ->orderByDesc('creado_en')
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'frecuencia'      => $p->frecuencia,
                'hora_envio'      => $p->hora_envio,
                'dias'            => $p->dias,
                'dias_mes'        => $p->dias_mes,
                'metodo'          => $p->metodo,
                'destinatarios'   => $p->destinatarios,
                'whatsapp_number' => $p->numero_whatsapp,
                'activo'          => $p->activo,
                'proximo_envio_en'=> $p->proximo_envio_en?->toDateTimeString(),
                'ultimo_envio_en' => $p->ultimo_envio_en?->toDateTimeString(),
            ]);

        return response()->json($programaciones);
    }

    /**
     * POST /admin/reportes/programacion
     * Crea una nueva programación de reporte.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'frequency'        => 'required|in:daily,weekly,monthly',
            'time'             => 'required|date_format:H:i',
            'days'             => 'nullable|array',
            'month_days'       => 'nullable|array',
            'method'           => 'required|in:email,whatsapp',
            'recipients'       => 'nullable|array',
            'recipients.*'     => 'email',
            'whatsapp_number'  => 'nullable|string|max:30',
            'active'           => 'boolean',
        ]);

        $sucursalId = auth()->user()->sucursal_id;

        $prog = new ProgramacionReporte([
            'sucursal_id'     => $sucursalId,
            'activo'          => $data['active'] ?? true,
            'frecuencia'      => $data['frequency'],
            'hora_envio'      => $data['time'] . ':00',
            'dias'            => $data['days'] ?? null,
            'dias_mes'        => $data['month_days'] ?? null,
            'metodo'          => $data['method'],
            'destinatarios'   => $data['recipients'] ?? [],
            'numero_whatsapp' => $data['whatsapp_number'] ?? null,
        ]);

        // Calcular el próximo envío antes de guardar
        $prog->proximo_envio_en = $prog->calcularProximoEnvio();
        $prog->save();

        return response()->json([
            'schedule' => [
                'id'              => $prog->id,
                'frecuencia'      => $prog->frecuencia,
                'hora_envio'      => $prog->hora_envio,
                'dias'            => $prog->dias,
                'dias_mes'        => $prog->dias_mes,
                'metodo'          => $prog->metodo,
                'destinatarios'   => $prog->destinatarios,
                'whatsapp_number' => $prog->numero_whatsapp,
                'activo'          => $prog->activo,
                'proximo_envio_en'=> $prog->proximo_envio_en?->toDateTimeString(),
            ],
        ], 201);
    }

    /**
     * POST /admin/reportes/programacion/{id}/eliminar
     * Elimina una programación.
     */
    public function destroy(string $id): JsonResponse
    {
        $sucursalId = auth()->user()->sucursal_id;

        $prog = ProgramacionReporte::where('id', $id)
            ->where('sucursal_id', $sucursalId)
            ->firstOrFail();

        $prog->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * POST /admin/reportes/programacion/test
     * Genera el PDF del mes actual y lo envía inmediatamente al primer destinatario.
     */
    public function test(Request $request): JsonResponse
    {
        $data = $request->validate([
            'method'       => 'required|in:email,whatsapp',
            'recipients'   => 'nullable|array',
            'recipients.*' => 'email',
        ]);

        if ($data['method'] === 'whatsapp') {
            return response()->json([
                'message' => 'WhatsApp requiere integración externa (Twilio / Meta API). El reporte se guardó correctamente.'
            ]);
        }

        $recipients = $data['recipients'] ?? [];
        if (empty($recipients)) {
            return response()->json(['message' => 'Agrega al menos un destinatario de correo.'], 422);
        }

        try {
            $user = auth()->user();
            $sucursal = $user->sucursal;

            // Generar datos del reporte (mes actual, sin filtros adicionales)
            $pdfData = $this->generarDatosReporte($user, $sucursal->id);
            $pdf = Pdf::loadView('admin.reportes.pdf', $pdfData);
            $pdfContent = $pdf->output();

            $periodoLabel = 'Mes actual (' . now()->format('F Y') . ')';

            foreach ($recipients as $email) {
                Mail::to($email)->send(
                    new ReporteProgramadoMail($sucursal, $periodoLabel, $pdfContent)
                );
            }

            return response()->json(['message' => 'Reporte de prueba enviado correctamente.']);
        } catch (\Exception $e) {
            // Retornamos el error limpio para no romper json_encode con data binaria (pdf)
            return response()->json([
                'message' => 'Error al generar o enviar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera los datos del reporte del mes actual para la sucursal dada.
     * Reutiliza la lógica de ManageReportes sin instanciar el componente Livewire.
     */
    public static function generarDatosReporte($user, string $sucursalId): array
    {
        $component = new ManageReportes();
        $component->period = 'mes';
        $component->start  = '';
        $component->end    = '';
        $component->categorias   = [];
        $component->metodos_pago = [];
        $component->productos_top = [];

        // Usar reflection para llamar al método privado calcularDatosReporte
        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calcularDatosReporte');
        $method->setAccessible(true);

        $data = $method->invoke($component, $user, $sucursalId);

        // El PDF necesita sections para mostrar todas las secciones
        $data['sections'] = ['kpis', 'chart', 'categories'];

        return $data;
    }
}
