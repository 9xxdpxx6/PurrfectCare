<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Медицинская карта - {{ $petInfo['Имя'] }}</title>
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
            page-break-inside: avoid; /* Предотвращаем разрыв секций */
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
            page-break-inside: avoid; /* Предотвращаем разрыв таблиц */
        }
        .data-table th, .data-table td { 
            padding: 8px; 
            border: 1px solid #ddd; 
            text-align: left; 
            page-break-inside: avoid; /* Предотвращаем разрыв ячеек */
        }
        .data-table th { 
            background-color: #e9ecef; 
            font-weight: bold; 
        }
        .data-table tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        .data-table tr { 
            page-break-inside: avoid; /* Предотвращаем разрыв строк */
        }
        .summary-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
            page-break-inside: avoid; /* Предотвращаем разрыв сетки */
        }
        .summary-item { 
            padding: 10px; 
            border: 1px solid #ddd; 
            background-color: #f8f9fa; 
            page-break-inside: avoid; /* Предотвращаем разрыв элементов */
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
        /* Дополнительные стили для предотвращения разрывов */
        .table-container {
            page-break-inside: avoid;
            overflow: visible;
        }
        .section-content {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Медицинская карта</h1>
        <h2>{{ $petInfo['Имя'] }} ({{ $petInfo['Владелец'] }})</h2>
        <p>Дата создания: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Информация о питомце</div>
        <div class="section-content">
            <table class="info-table">
                <tr><td>ID</td><td>{{ $petInfo['ID'] }}</td></tr>
                <tr><td>Имя</td><td>{{ $petInfo['Имя'] }}</td></tr>
                <tr><td>Владелец</td><td>{{ $petInfo['Владелец'] }}</td></tr>
                <tr><td>Email владельца</td><td>{{ $petInfo['Email владельца'] }}</td></tr>
                <tr><td>Телефон владельца</td><td>{{ $petInfo['Телефон владельца'] }}</td></tr>
                <tr><td>Адрес владельца</td><td>{{ $petInfo['Адрес владельца'] }}</td></tr>
                <tr><td>Порода</td><td>{{ $petInfo['Порода'] }}</td></tr>
                <tr><td>Вид</td><td>{{ $petInfo['Вид'] }}</td></tr>
                <tr><td>Пол</td><td>{{ $petInfo['Пол'] }}</td></tr>
                <tr><td>Дата рождения</td><td>{{ $petInfo['Дата рождения'] }}</td></tr>
                <tr><td>Возраст</td><td>{{ $petInfo['Возраст'] }}</td></tr>
                <tr><td>Температура</td><td>{{ $petInfo['Температура'] }}</td></tr>
                <tr><td>Вес</td><td>{{ $petInfo['Вес'] }}</td></tr>
                <tr><td>Дата регистрации</td><td>{{ $petInfo['Дата регистрации'] }}</td></tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Сводка</div>
        <div class="section-content">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Общее количество приемов</div>
                    <div class="summary-value">{{ $summary['Общее количество приемов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Общее количество вакцинаций</div>
                    <div class="summary-value">{{ $summary['Общее количество вакцинаций'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Общее количество анализов</div>
                    <div class="summary-value">{{ $summary['Общее количество анализов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Общее количество заказов</div>
                    <div class="summary-value">{{ $summary['Общее количество заказов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Общая сумма заказов</div>
                    <div class="summary-value">{{ $summary['Общая сумма заказов'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Последний прием</div>
                    <div class="summary-value">{{ $summary['Последний прием'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Последняя вакцинация</div>
                    <div class="summary-value">{{ $summary['Последняя вакцинация'] }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Последний анализ</div>
                    <div class="summary-value">{{ $summary['Последний анализ'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Приемы ({{ count($visits) }})</div>
        <div class="section-content">
            @if(count($visits) > 0)
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Дата и время</th>
                                <th>Ветеринар</th>
                                <th>Филиал</th>
                                <th>Статус</th>
                                <th>Жалобы</th>
                                <th>Симптомы</th>
                                <th>Диагнозы</th>
                                <th>Завершен</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visits as $visit)
                                <tr>
                                    <td>{{ $visit['Дата и время'] }}</td>
                                    <td>{{ $visit['Ветеринар'] }}</td>
                                    <td>{{ $visit['Филиал'] }}</td>
                                    <td>{{ $visit['Статус'] }}</td>
                                    <td>{{ $visit['Жалобы'] }}</td>
                                    <td>{{ $visit['Симптомы'] }}</td>
                                    <td>{{ $visit['Диагнозы'] }}</td>
                                    <td>{{ $visit['Завершен'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">Нет данных о приемах</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Вакцинации ({{ count($vaccinations) }})</div>
        <div class="section-content">
            @if(count($vaccinations) > 0)
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Тип вакцины</th>
                                <th>Ветеринар</th>
                                <th>Дата вакцинации</th>
                                <th>Следующая вакцинация</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vaccinations as $vaccination)
                                <tr>
                                    <td>{{ $vaccination['Тип вакцины'] }}</td>
                                    <td>{{ $vaccination['Ветеринар'] }}</td>
                                    <td>{{ $vaccination['Дата вакцинации'] }}</td>
                                    <td>{{ $vaccination['Следующая вакцинация'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">Нет данных о вакцинациях</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Анализы ({{ count($labTests) }})</div>
        <div class="section-content">
            @if(count($labTests) > 0)
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Тип анализа</th>
                                <th>Ветеринар</th>
                                <th>Дата получения</th>
                                <th>Дата завершения</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labTests as $labTest)
                                <tr>
                                    <td>{{ $labTest['Тип анализа'] }}</td>
                                    <td>{{ $labTest['Ветеринар'] }}</td>
                                    <td>{{ $labTest['Дата получения'] }}</td>
                                    <td>{{ $labTest['Дата завершения'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">Нет данных об анализах</div>
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
</body>
</html>