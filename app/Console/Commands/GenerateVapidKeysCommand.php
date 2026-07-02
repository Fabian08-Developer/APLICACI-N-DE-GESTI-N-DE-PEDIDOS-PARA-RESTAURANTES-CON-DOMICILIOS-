<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    protected $signature   = 'webpush:vapid';
    protected $description = 'Generate VAPID keys for Web Push Notifications';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $publicKey  = $keys['publicKey'];
        $privateKey = $keys['privateKey'];

        $this->info('');
        $this->info('=== VAPID Keys Generated ===');
        $this->line("VAPID_PUBLIC_KEY={$publicKey}");
        $this->line("VAPID_PRIVATE_KEY={$privateKey}");
        $this->info('============================');
        $this->info('');
        $this->info('Agrega estas líneas a tu archivo .env y reinicia el servidor.');

        // Actualizar el .env automáticamente
        $envPath = base_path('.env');
        $env = file_get_contents($envPath);

        if (str_contains($env, 'VAPID_PUBLIC_KEY=')) {
            $env = preg_replace('/VAPID_PUBLIC_KEY=.*/', "VAPID_PUBLIC_KEY={$publicKey}", $env);
            $env = preg_replace('/VAPID_PRIVATE_KEY=.*/', "VAPID_PRIVATE_KEY={$privateKey}", $env);
        } else {
            $env .= "\n# ─── Web Push VAPID Keys ───────────────────────────────────────\n";
            $env .= "VAPID_PUBLIC_KEY={$publicKey}\n";
            $env .= "VAPID_PRIVATE_KEY={$privateKey}\n";
            $env .= "VITE_VAPID_PUBLIC_KEY=\"\${VAPID_PUBLIC_KEY}\"\n";
        }

        file_put_contents($envPath, $env);

        $this->info('✓ Claves escritas automáticamente en .env');
        $this->warn('Ejecuta "php artisan config:clear" y reinicia el servidor.');

        return self::SUCCESS;
    }
}
