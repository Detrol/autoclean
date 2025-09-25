<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbjudan till WashNode</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 12px;
            margin: 0 auto 16px auto;
            color: white;
            font-size: 28px;
        }
        h1 {
            color: #111827;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .subtitle {
            color: #6b7280;
            margin-top: 4px;
            font-size: 16px;
        }
        .content {
            margin-top: 24px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white !important;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 24px 0;
            text-align: center;
            border: none;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.25);
        }
        .button:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }
        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
        }
        .expires-warning {
            color: #dc2626;
            font-weight: 600;
        }
        .content p {
            margin-bottom: 16px;
        }
        .content ul {
            padding-left: 20px;
        }
        .content li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏪</div>
            <h1>WashNode</h1>
            <p class="subtitle">Stationshanteringssystem för biltvätt</p>
        </div>

        <div class="content">
            <p>Hej {{ $recipientName }}!</p>

            <p>{{ $inviterName }} har bjudit in dig att ansluta till WashNode - vårt stationshanteringssystem för biltvättar.</p>

            <p>För att komma igång behöver du sätta ett lösenord för ditt konto. Klicka på knappen nedan för att slutföra registreringen:</p>

            <div style="text-align: center;">
                <a href="{{ $invitationUrl }}" class="button">Sätt ditt lösenord</a>
            </div>

            <div class="info-box">
                <strong>Vad är WashNode?</strong><br>
                WashNode är ett komplett system för att hantera:
                <ul style="margin: 10px 0;">
                    <li>Schemaläggning av städuppgifter</li>
                    <li>Inventariehantering</li>
                    <li>Tidsrapportering</li>
                    <li>Stationsöversikt och underhåll</li>
                </ul>
            </div>

            <p><span class="expires-warning">OBS!</span> Denna inbjudan är giltig till {{ $expiresAt->format('d M Y, H:i') }}. Efter detta datum behöver du be om en ny inbjudan.</p>

            <p>Om du har några frågor, kontakta {{ $inviterName }} eller din närmaste chef.</p>

            <p>Välkommen till teamet!</p>
        </div>

        <div class="footer">
            <p>Om du inte kan klicka på knappen, kopiera och klistra in följande länk i din webbläsare:</p>
            <p style="word-break: break-all; color: #667eea;">{{ $invitationUrl }}</p>
            <p style="margin-top: 20px;">© {{ date('Y') }} WashNode. Alla rättigheter förbehållna.</p>
        </div>
    </div>
</body>
</html>