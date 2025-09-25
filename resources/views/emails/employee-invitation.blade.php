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
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" style="width: 28px; height: 28px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                </svg>
            </div>
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