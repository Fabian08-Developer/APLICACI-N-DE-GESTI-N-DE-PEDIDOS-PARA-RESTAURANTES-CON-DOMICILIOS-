<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Código QR - Mesa {{ $mesa->numero }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            text-align: center;
            color: #1e293b;
            margin: 0;
            padding: 40px;
            background-color: #ffffff;
        }
        .container {
            border: 3px solid #c9a84c;
            border-radius: 20px;
            padding: 40px 30px;
            max-width: 420px;
            margin: 0 auto;
            background: #fafaf9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .logo {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .branch {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .title-box {
            background-color: #0f172a;
            color: #c9a84c;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            letter-spacing: 1px;
            display: inline-block;
        }
        .qr-code {
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .qr-code svg {
            display: block;
        }
        .instruction {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
            margin-top: 30px;
            padding: 0 10px;
        }
        .instruction strong {
            color: #0f172a;
            font-size: 16px;
        }
        .footer {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">MENÚ DIGITAL</div>
        <div class="branch">{{ $mesa->sucursal->nombre }}</div>
        
        <div class="title-box">MESA {{ $mesa->numero }}</div>
        
        <div style="display: block; width: 100%; margin: 10px 0;">
            <div class="qr-code">
                <img src="data:image/svg+xml;base64,{{ base64_encode($qrCodeSvg) }}" width="250" height="250" style="display: block; border: none;">
            </div>
        </div>
        
        <div class="instruction">
            <strong>¿Listo para ordenar?</strong><br>
            Escanea este código QR con tu celular para ver nuestra carta digital, armar tu pedido y pagar en línea de forma rápida y segura.
        </div>
        
        <div class="footer">
            Powered by Espresso &bull; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
