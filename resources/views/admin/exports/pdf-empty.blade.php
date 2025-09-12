<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Отчет</title>
    <style>
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px; 
            text-align: center; 
        }
        .empty { 
            margin-top: 50px; 
            color: #666; 
        }
    </style>
</head>
<body>
    <div class="empty">
        <h3>Нет данных для экспорта</h3>
        <p>Дата создания: {{ now()->format('d.m.Y H:i') }}</p>
    </div>
</body>
</html>
