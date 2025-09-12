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
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
        }
        tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .footer { 
            margin-top: 20px; 
            text-align: center; 
            font-size: 10px; 
            color: #666; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Отчет</h2>
        <p>Дата создания: {{ now()->format('d.m.Y H:i') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                @foreach($tableHeaders as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Страница 1 из 1</p>
    </div>
</body>
</html>
