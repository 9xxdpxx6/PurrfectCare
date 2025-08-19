<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Код подтверждения</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
        .code { background: #fff; border: 2px solid #4CAF50; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; color: #4CAF50; margin: 20px 0; border-radius: 5px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PurrfectCare</h1>
            <p>Код подтверждения</p>
        </div>
        
        <div class="content">
            <p>Здравствуйте, {{ $userName }}!</p>
            
            <p>Вы запросили код подтверждения для входа в систему PurrfectCare.</p>
            
            <p>Для завершения регистрации используйте следующий код:</p>
            
            <div class="code">{{ $code }}</div>
            
            <p><strong>Важная информация:</strong></p>
            <ul>
                <li>Код действителен 10 минут</li>
                <li>Никогда не передавайте этот код третьим лицам</li>
                <li>Если вы не запрашивали код, просто проигнорируйте это письмо</li>
            </ul>
            
            <p>С уважением,<br>команда PurrfectCare</p>
        </div>
        
        <div class="footer">
            <p>С уважением, команда PurrfectCare</p>
            <p>Это письмо сгенерировано автоматически, не отвечайте на него</p>
        </div>
    </div>
</body>
</html>
