<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Детали приёма - {{ $visitInfo['ID приёма'] }}</title>
    <style>
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px; 
            line-height: 1.4; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 20px; 
        }
        .section { 
            margin-bottom: 25px; 
            page-break-inside: avoid;
        }
        .section-title { 
            background-color: #f0f0f0; 
            padding: 8px; 
            font-weight: bold; 
            margin-bottom: 10px; 
            border-left: 4px solid #007bff; 
        }
        .info-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        .info-table td { 
            padding: 5px 10px; 
            border: 1px solid #ddd; 
        }
        .info-table td:first-child { 
            background-color: #f8f9fa; 
            font-weight: bold; 
            width: 30%; 
        }
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            page-break-inside: avoid;
        }
        .data-table th, .data-table td { 
            padding: 8px; 
            border: 1px solid #ddd; 
            text-align: left; 
            page-break-inside: avoid;
        }
        .data-table th { 
            background-color: #e9ecef; 
            font-weight: bold; 
        }
        .data-table tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        .data-table tr { 
            page-break-inside: avoid;
        }
        .summary-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
            page-break-inside: avoid;
        }
        .summary-item { 
            padding: 10px; 
            border: 1px solid #ddd; 
            background-color: #f8f9fa; 
            page-break-inside: avoid;
        }
        .summary-label { 
            font-weight: bold; 
            color: #495057; 
        }
        .summary-value { 
            color: #007bff; 
            font-size: 14px; 
        }
        .no-data { 
            text-align: center; 
            color: #6c757d; 
            font-style: italic; 
            padding: 20px; 
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Детали приёма</h1>
        <h2>ID: {{ $visitInfo['ID приёма'] }}</h2>
        <p>Дата создания отчета: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Информация о приёме</div>
        <div class="section-content">
            <table class="info-table">
                <tr><td>ID приёма</td><td>{{ $visitInfo['ID приёма'] }}</td></tr>
                <tr><td>Дата и время</td><td>{{ $visitInfo['Дата и время'] }}</td></tr>
                <tr><td>Статус</td><td>{{ $visitInfo['Статус'] }}</td></tr>
                <tr><td>Жалобы</td><td>{{ $visitInfo['Жалобы'] }}</td></tr>
                <tr><td>Дата создания</td><td>{{ $visitInfo['Дата создания'] }}</td></tr>
                <tr><td>Последнее обновление</td><td>{{ $visitInfo['Последнее обновление'] }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Информация о клиенте</div>
        <div class="section-content">
            <table class="info-table">
                <tr><td>ID клиента</td><td>{{ $clientInfo['ID клиента'] }}</td></tr>
                <tr><td>Имя</td><td>{{ $clientInfo['Имя'] }}</td></tr>
                <tr><td>Email</td><td>{{ $clientInfo['Email'] }}</td></tr>
                <tr><td>Телефон</td><td>{{ $clientInfo['Телефон'] }}</td></tr>
                <tr><td>Адрес</td><td>{{ $clientInfo['Адрес'] }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Информация о питомце</div>
        <div class="section-content">
            <table class="info-table">
                <tr><td>ID питомца</td><td>{{ $petInfo['ID питомца'] }}</td></tr>
                <tr><td>Кличка</td><td>{{ $petInfo['Имя'] }}</td></tr>
                <tr><td>Порода</td><td>{{ $petInfo['Порода'] }}</td></tr>
                <tr><td>Вид</td><td>{{ $petInfo['Вид'] }}</td></tr>
                <tr><td>Пол</td><td>{{ $petInfo['Пол'] }}</td></tr>
                <tr><td>Дата рождения</td><td>{{ $petInfo['Дата рождения'] }}</td></tr>
                <tr><td>Возраст</td><td>{{ $petInfo['Возраст'] }}</td></tr>
                <tr><td>Температура</td><td>{{ $petInfo['Температура'] }}</td></tr>
                <tr><td>Вес</td><td>{{ $petInfo['Вес'] }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Информация о ветеринаре</div>
        <div class="section-content">
            <table class="info-table">
                <tr><td>ID ветеринара</td><td>{{ $veterinarianInfo['ID ветеринара'] }}</td></tr>
                <tr><td>Имя</td><td>{{ $veterinarianInfo['Имя'] }}</td></tr>
                <tr><td>Email</td><td>{{ $veterinarianInfo['Email'] }}</td></tr>
                <tr><td>Телефон</td><td>{{ $veterinarianInfo['Телефон'] }}</td></tr>
                <tr><td>Специализации</td><td>{{ $veterinarianInfo['Специализации'] }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Информация о филиале</div>
        <div class="section-content">
            <table class="info-table">
                <tr><td>ID филиала</td><td>{{ $branchInfo['ID филиала'] }}</td></tr>
                <tr><td>Название</td><td>{{ $branchInfo['Название'] }}</td></tr>
                <tr><td>Адрес</td><td>{{ $branchInfo['Адрес'] }}</td></tr>
                <tr><td>Телефон</td><td>{{ $branchInfo['Телефон'] }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Симптомы ({{ count($symptoms) }})</div>
        <div class="section-content">
            @if(count($symptoms) > 0)
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Тип</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($symptoms as $symptom)
                                <tr>
                                    <td>{{ $symptom['ID симптома'] }}</td>
                                    <td>{{ $symptom['Название'] }}</td>
                                    <td>{{ $symptom['Тип'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">Нет данных о симптомах</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Диагнозы ({{ count($diagnoses) }})</div>
        <div class="section-content">
            @if(count($diagnoses) > 0)
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>План лечения</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($diagnoses as $diagnosis)
                                <tr>
                                    <td>{{ $diagnosis['ID диагноза'] }}</td>
                                    <td>{{ $diagnosis['Название'] }}</td>
                                    <td>{{ $diagnosis['План лечения'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">Нет данных о диагнозах</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Заказы ({{ count($orders) }})</div>
        <div class="section-content">
            @if(count($orders) > 0)
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID заказа</th>
                                <th>Филиал</th>
                                <th>Статус</th>
                                <th>Общая сумма</th>
                                <th>Оплачен</th>
                                <th>Дата создания</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order['ID заказа'] }}</td>
                                    <td>{{ $order['Филиал'] }}</td>
                                    <td>{{ $order['Статус'] }}</td>
                                    <td>{{ $order['Общая сумма'] }}</td>
                                    <td>{{ $order['Оплачен'] }}</td>
                                    <td>{{ $order['Дата создания'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">Нет данных о заказах</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Сводка</div>
        <div class="section-content">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Количество симптомов</div>
                    <div class="summary-value">{{ $summary['Количество симптомов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Количество диагнозов</div>
                    <div class="summary-value">{{ $summary['Количество диагнозов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Количество заказов</div>
                    <div class="summary-value">{{ $summary['Количество заказов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Общая сумма заказов</div>
                    <div class="summary-value">{{ $summary['Общая сумма заказов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Количество позиций в заказах</div>
                    <div class="summary-value">{{ $summary['Количество позиций в заказах'] }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
