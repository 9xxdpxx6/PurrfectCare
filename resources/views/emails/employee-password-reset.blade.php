<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Сброс пароля</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
        .password { background: #fff; border: 2px solid #007bff; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; color: #007bff; margin: 20px 0; border-radius: 5px; font-family: monospace; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PurrfectCare</h1>
            <p>Сброс пароля для сотрудника</p>
        </div>
        
        <div class="content">
            <p>Здравствуйте, {{ $employeeName }}!</p>
            
            <p>Администратор системы PurrfectCare сбросил ваш пароль для входа в систему.</p>
            
            <p>Ваш новый временный пароль:</p>
            
            <div class="password">{{ $newPassword }}</div>
            
            <div class="warning">
                <p><strong>⚠️ Важная информация:</strong></p>
                <ul>
                    <li>Этот пароль является временным</li>
                    <li>Рекомендуется сменить пароль при первом входе в систему</li>
                    <li>Никогда не передавайте этот пароль третьим лицам</li>
                    <li>Если вы не запрашивали сброс пароля, немедленно свяжитесь с администратором</li>
                </ul>
            </div>
            
            <p><strong>Для входа в систему:</strong></p>
            <ol>
                <li>Перейдите на страницу входа для сотрудников</li>
                <li>Введите ваш email: <strong>{{ $employeeEmail }}</strong></li>
                <li>Введите новый пароль: <strong>{{ $newPassword }}</strong></li>
                <li>После входа смените пароль на новый</li>
            </ol>
            
            <p>С уважением,<br>команда PurrfectCare</p>
        </div>
        
        <div class="footer">
            <p>Это письмо сгенерировано автоматически, не отвечайте на него</p>
            <p>Если у вас возникли вопросы, обратитесь к администратору системы</p>
        </div>
    </div>
</body>
</html>
