<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля - PurrfectCare</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .logo {
            position: relative;
            z-index: 1;
        }
        
        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .logo p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        
        .main-text {
            font-size: 16px;
            color: #4a4a4a;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            border: none;
            cursor: pointer;
            letter-spacing: 0.5px;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.4);
            color: #ffffff !important;
        }
        
        .info-box {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin: 30px 0;
            position: relative;
        }
        
        .info-box::before {
            content: '⚠️';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 20px;
        }
        
        .info-box h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 16px;
            margin-left: 30px;
        }
        
        .info-box ul {
            margin-left: 30px;
            list-style: none;
        }
        
        .info-box li {
            font-size: 14px;
            color: #4a4a4a;
            margin-bottom: 8px;
            position: relative;
            padding-left: 20px;
        }
        
        .info-box li::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }
        
        .url-section {
            margin: 30px 0;
        }
        
        .url-section p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
            font-weight: 500;
        }
        
        .url-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            word-break: break-all;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.4;
        }
        
        .signature {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }
        
        .signature p {
            font-size: 16px;
            color: #4a4a4a;
            margin-bottom: 8px;
        }
        
        .footer {
            background: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 30px 0;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
            
            .greeting {
                font-size: 20px;
            }
            
            .button {
                padding: 14px 28px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <h1>PurrfectCare</h1>
                <p>Сброс пароля</p>
            </div>
        </div>
        
        <div class="content">
            <div class="greeting">Здравствуйте, {{ $user->name }}!</div>
            
            <div class="main-text">
                <p>Вы запросили сброс пароля для вашего аккаунта в системе PurrfectCare.</p>
                <p>Для сброса пароля нажмите на кнопку ниже:</p>
            </div>
            
            <div class="button-container">
                <a href="{{ route('client.password.reset', ['token' => $token, 'email' => $user->email]) }}" class="button">Сбросить пароль</a>
            </div>
            
            <div class="info-box">
                <h3>Важная информация</h3>
                <ul>
                    <li>Эта ссылка действительна в течение 24 часов</li>
                    <li>Ссылка может быть использована только один раз</li>
                    <li>Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо</li>
                    <li>Никогда не передавайте эту ссылку третьим лицам</li>
                </ul>
            </div>
            
            <div class="divider"></div>
            
            <div class="url-section">
                <p>Если у вас возникли проблемы с нажатием кнопки "Сбросить пароль", скопируйте и вставьте ссылку ниже в адресную строку браузера:</p>
                <div class="url-box">{{ route('client.password.reset', ['token' => $token, 'email' => $user->email]) }}</div>
            </div>
            
            <div class="signature">
                <p>С уважением,</p>
                <p><strong>команда PurrfectCare</strong></p>
            </div>
        </div>
        
        <div class="footer">
            <p>Это письмо отправлено автоматически, не отвечайте на него</p>
            <p>Если у вас возникли вопросы, обратитесь к администратору системы</p>
        </div>
    </div>
</body>
</html>
