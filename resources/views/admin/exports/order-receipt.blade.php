<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Чек заказа №{{ $order->id }}</title>
    <style>
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 12px; 
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 20px; 
        }
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .info-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .info-section.allow-break {
            page-break-inside: auto;
        }
        .section-title { 
            background-color: #f0f0f0; 
            padding: 8px; 
            font-weight: bold; 
            margin-bottom: 10px; 
            border-left: 4px solid #007bff; 
            font-size: 14px;
        }
        .info-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        .info-table td { 
            padding: 5px 10px; 
            border: 1px solid #ddd; 
            vertical-align: top;
        }
        .info-table td:first-child { 
            background-color: #f8f9fa; 
            font-weight: bold; 
            width: 30%; 
        }
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            page-break-inside: auto;
        }
        .items-table thead {
            page-break-after: avoid; /* Шапка не отрывается от первой строки данных */
        }
        .items-table th, .items-table td { 
            padding: 8px; 
            border: 1px solid #ddd; 
            text-align: left; 
            page-break-inside: avoid;
        }
        .items-table th { 
            background-color: #e9ecef; 
            font-weight: bold; 
            text-align: center;
        }
        .items-table td:first-child { 
            width: 40%; 
        }
        .items-table td:nth-child(2) { 
            width: 15%; 
            text-align: center;
        }
        .items-table td:nth-child(3) { 
            width: 20%; 
            text-align: right;
        }
        .items-table td:last-child { 
            width: 25%; 
            text-align: right;
            font-weight: bold;
        }
        .items-table tbody tr:first-child {
            page-break-before: avoid; /* Первая строка не отрывается от шапки */
        }
        .items-table tbody tr:last-child {
            page-break-after: avoid; /* Последняя строка не отрывается от итоговой суммы */
        }
        .total-section {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 15px;
            page-break-inside: avoid;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .total-row.final {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .no-items {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .page-break-before {
            page-break-before: always;
        }
        .page-break-after {
            page-break-after: always;
        }
        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="clinic-name">PurrfectCare</div>
            <div class="receipt-title">ЧЕК ЗАКАЗА №{{ $receiptNumber }}</div>
            <div class="receipt-info">
                <div>Дата: {{ $currentDate }}</div>
                <div>Заказ №{{ $order->id }}</div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Информация о заказе</div>
            <table class="info-table">
                <tr>
                    <td>Клиент</td>
                    <td>{{ $order->client ? $order->client->name : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Телефон</td>
                    <td>{{ $order->client ? $order->client->phone : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $order->client ? $order->client->email : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Питомец</td>
                    <td>{{ $order->pet ? $order->pet->name : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Порода</td>
                    <td>{{ $order->pet && $order->pet->breed ? $order->pet->breed->name : 'Не указана' }}</td>
                </tr>
                <tr>
                    <td>Вид</td>
                    <td>{{ $order->pet && $order->pet->breed && $order->pet->breed->species ? $order->pet->breed->species->name : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Филиал</td>
                    <td>{{ $order->branch ? $order->branch->name : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Менеджер</td>
                    <td>{{ $order->manager ? $order->manager->name : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Статус</td>
                    <td>{{ $order->status ? $order->status->name : 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Оплачен</td>
                    <td>
                        <span class="status-badge {{ $order->is_paid ? 'status-paid' : 'status-unpaid' }}">
                            {{ $order->is_paid ? 'Да' : 'Нет' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        @if(count($services) > 0)
        <div class="info-section allow-break">
            <div class="section-title">Услуги ({{ count($services) }})</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Наименование</th>
                        <th>Кол-во</th>
                        <th>Цена за ед.</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                    <tr>
                        <td>{{ $service['name'] }}</td>
                        <td>{{ number_format($service['quantity'], 2, ',', ' ') }}</td>
                        <td>{{ number_format($service['unit_price'], 2, ',', ' ') }} руб.</td>
                        <td>{{ number_format($service['total'], 2, ',', ' ') }} руб.</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($drugs) > 0)
        <div class="info-section allow-break">
            <div class="section-title">Лекарства ({{ count($drugs) }})</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Наименование</th>
                        <th>Кол-во</th>
                        <th>Цена за ед.</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drugs as $drug)
                    <tr>
                        <td>{{ $drug['name'] }}</td>
                        <td>{{ number_format($drug['quantity'], 2, ',', ' ') }}</td>
                        <td>{{ number_format($drug['unit_price'], 2, ',', ' ') }} руб.</td>
                        <td>{{ number_format($drug['total'], 2, ',', ' ') }} руб.</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($labTests) > 0)
        <div class="info-section allow-break">
            <div class="section-title">Анализы ({{ count($labTests) }})</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Наименование</th>
                        <th>Кол-во</th>
                        <th>Цена за ед.</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labTests as $labTest)
                    <tr>
                        <td>{{ $labTest['name'] }}</td>
                        <td>{{ number_format($labTest['quantity'], 2, ',', ' ') }}</td>
                        <td>{{ number_format($labTest['unit_price'], 2, ',', ' ') }} руб.</td>
                        <td>{{ number_format($labTest['total'], 2, ',', ' ') }} руб.</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($vaccinations) > 0)
        <div class="info-section allow-break">
            <div class="section-title">Вакцинации ({{ count($vaccinations) }})</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Наименование</th>
                        <th>Кол-во</th>
                        <th>Цена за ед.</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vaccinations as $vaccination)
                    <tr>
                        <td>{{ $vaccination['name'] }}</td>
                        <td>{{ number_format($vaccination['quantity'], 2, ',', ' ') }}</td>
                        <td>{{ number_format($vaccination['unit_price'], 2, ',', ' ') }} руб.</td>
                        <td>{{ number_format($vaccination['total'], 2, ',', ' ') }} руб.</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($services) == 0 && count($drugs) == 0 && count($labTests) == 0 && count($vaccinations) == 0)
        <div class="no-items">
            В заказе нет товаров и услуг
        </div>
        @endif

        <div class="total-section">
            <div class="total-row final">
                <span>ИТОГО К ОПЛАТЕ:</span>
                <span>{{ number_format($order->total, 2, ',', ' ') }} руб.</span>
            </div>
        </div>

        <div class="footer">
            <p>Спасибо за ваш заказ!</p>
            <p>PurrfectCare - забота о ваших питомцах</p>
            <p>Дата печати: {{ $currentDate }}</p>
        </div>
    </div>
</body>
</html>
