<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportSchedule;
use App\Services\Reports\ReportDataService;
use App\Notifications\SalesSummaryNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class ReportScheduleController extends Controller
{
    protected $reportService;

    public function __construct(ReportDataService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Listar programaciones activas
     */
    public function index()
    {
        $schedules = ReportSchedule::orderBy('created_at', 'desc')->get();
        return response()->json($schedules);
    }

    /**
     * Guardar nueva programación
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'active' => 'boolean',
            'frequency' => 'required|string',
            'time' => 'required',
            'days' => 'nullable|array',
            'month_days' => 'nullable|array',
            'custom_config' => 'nullable|array',
            'method' => 'required|string',
            'recipients' => 'nullable|array',
            'whatsapp_number' => 'nullable|string',
            'sections' => 'nullable|array',
        ]);

        $schedule = ReportSchedule::create($validated);
        $schedule->calculateNextRun();

        return response()->json([
            'message' => 'Programación guardada exitosamente',
            'schedule' => $schedule
        ]);
    }

    /**
     * Eliminar programación
     */
    public function destroy($id)
    {
        $schedule = ReportSchedule::findOrFail($id);
        $schedule->delete();

        return response()->json(['message' => 'Programación eliminada']);
    }

    /**
     * Enviar reporte de prueba inmediato
     */
    public function sendTest(Request $request)
    {
        $method = $request->input('method');
        $sections = $request->input('sections', ['kpis', 'chart']);
        
        // Datos de prueba (últimos 30 días)
        $start = Carbon::now()->subDays(30)->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');
        $data = $this->reportService->getSalesSummary($start, $end);

        $notification = new SalesSummaryNotification($data, $sections);

        if ($method === 'email') {
            $recipients = $request->input('recipients', []);
            foreach ($recipients as $email) {
                Notification::route('mail', $email)->notify($notification);
            }
        } elseif ($method === 'whatsapp') {
            $number = $request->input('whatsapp_number');
            Notification::route('WhatsApp', $number)->notify($notification);
        }

        return response()->json(['message' => 'Reporte de prueba enviado']);
    }
}
