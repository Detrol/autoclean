<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbjudan till AutoClean</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            display: inline-block;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        h1 {
            color: #1f2937;
            margin: 0;
            font-size: 28px;
        }
        .subtitle {
            color: #6b7280;
            margin-top: 5px;
            font-size: 16px;
        }
        .content {
            margin-top: 30px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 30px 0;
            text-align: center;
        }
        .button:hover {
            opacity: 0.9;
        }
        .info-box {
            background-color: #f3f4f6;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .expires-warning {
            color: #ef4444;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"></div>
            <h1>AutoClean</h1>
            <p class="subtitle">Stationshanteringssystem för biltvätt</p>
        </div>

        <div class="content">
            <p>Hej {{ $recipientName }}!</p>

            <p>{{ $inviterName }} har bjudit in dig att ansluta till AutoClean - vårt stationshanteringssystem för biltvättar.</p>

            <p>För att komma igång behöver du sätta ett lösenord för ditt konto. Klicka på knappen nedan för att slutföra registreringen:</p>

            <div style="text-align: center;">
                <a href="{{ $invitationUrl }}" class="button">Sätt ditt lösenord</a>
            </div>

            <div class="info-box">
                <strong>Vad är AutoClean?</strong><br>
                AutoClean är ett komplett system för att hantera:
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
            <p style="margin-top: 20px;">© {{ date('Y') }} AutoClean. Alla rättigheter förbehållna.</p>
        </div>
    </div>
</body>
</html>