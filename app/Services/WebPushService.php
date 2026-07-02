<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Illuminate\Support\Facades\Log;

class WebPushService
{
    private WebPush $webPush;

    public function __construct()
    {
        $vapidPublicKey  = config('webpush.vapid.public_key');
        $vapidPrivateKey = config('webpush.vapid.private_key');
        $vapidSubject    = config('webpush.vapid.subject', config('app.url'));

        $auth = [];

        if ($vapidPublicKey && $vapidPrivateKey) {
            $auth = [
                'VAPID' => [
                    'subject'    => $vapidSubject,
                    'publicKey'  => $vapidPublicKey,
                    'privateKey' => $vapidPrivateKey,
                ],
            ];
        }

        $this->webPush = new WebPush($auth);
        $this->webPush->setReuseVAPIDHeaders(true);
        $this->webPush->setAutomaticPadding(false);
    }

    /**
     * Envía una notificación push a un usuario específico (todas sus suscripciones).
     */
    public function sendToUser(int $userId, string $titulo, string $mensaje, array $extra = []): void
    {
        $suscripciones = PushSubscription::where('user_id', $userId)->get();

        if ($suscripciones->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title'   => $titulo,
            'body'    => $mensaje,
            'icon'    => '/icons/icon-192.png',
            'badge'   => '/icons/icon-72.png',
            'tag'     => 'sgpd-' . ($extra['tipo'] ?? 'general'),
            'data'    => array_merge(['url' => '/dashboard'], $extra),
        ]);

        foreach ($suscripciones as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'publicKey'       => $sub->public_key,
                    'authToken'       => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
                ]);

                $this->webPush->queueNotification($subscription, $payload);
            } catch (\Throwable $e) {
                Log::warning('[WebPush] Error encolando notificación', [
                    'user_id' => $userId,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        // Enviar todas las encoladas
        foreach ($this->webPush->flush() as $report) {
            /** @var MessageSentReport $report */
            if (!$report->isSuccess()) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                Log::warning('[WebPush] Notificación fallida', [
                    'reason'   => $report->getReason(),
                    'endpoint' => substr($endpoint, 0, 60) . '...',
                ]);

                // Si el endpoint ya no es válido, eliminarlo
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                    Log::info('[WebPush] Suscripción expirada eliminada.');
                }
            }
        }
    }
}
