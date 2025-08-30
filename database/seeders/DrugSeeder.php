<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очищаем таблицу branch_drug перед заполнением
        \Illuminate\Support\Facades\DB::table('branch_drug')->truncate();

        $drugs = [
            // Антибиотики
            ['name' => 'Амоксициллин 15%', 'price' => 850.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Цефтриаксон 5%', 'price' => 1200.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Энрофлоксацин 10%', 'price' => 950.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Тилозин 200', 'price' => 1800.00, 'quantity' => 30, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Линкомицин 30%', 'price' => 750.00, 'quantity' => 60, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Гентамицин 4%', 'price' => 650.00, 'quantity' => 40, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Окситетрациклин 20%', 'price' => 1100.00, 'quantity' => 70, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Доксициклин 10%', 'price' => 1400.00, 'quantity' => 45, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Спирамицин 50%', 'price' => 2200.00, 'quantity' => 25, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Кларитромицин 10%', 'price' => 1600.00, 'quantity' => 35, 'prescription_required' => true, 'unit' => 'мл'],

            // Противовоспалительные и обезболивающие
            ['name' => 'Кетопрофен 1%', 'price' => 450.00, 'quantity' => 120, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Мелоксикам 0.5%', 'price' => 380.00, 'quantity' => 90, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Флуниксин 5%', 'price' => 520.00, 'quantity' => 75, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Карпрофен 5%', 'price' => 680.00, 'quantity' => 60, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Толфенамовая кислота 4%', 'price' => 890.00, 'quantity' => 40, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Аспирин 10%', 'price' => 250.00, 'quantity' => 150, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Парацетамол 15%', 'price' => 320.00, 'quantity' => 130, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Ибупрофен 5%', 'price' => 410.00, 'quantity' => 100, 'prescription_required' => false, 'unit' => 'мл'],

            // Антигельминтные препараты
            ['name' => 'Ивермектин 1%', 'price' => 280.00, 'quantity' => 200, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Альбендазол 10%', 'price' => 420.00, 'quantity' => 180, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Фенбендазол 10%', 'price' => 380.00, 'quantity' => 160, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Празиквантел 10%', 'price' => 550.00, 'quantity' => 120, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Пирантел 15%', 'price' => 320.00, 'quantity' => 140, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Левамизол 7.5%', 'price' => 290.00, 'quantity' => 170, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Милбемицин 0.1%', 'price' => 680.00, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Моксидектин 1%', 'price' => 720.00, 'quantity' => 70, 'prescription_required' => false, 'unit' => 'мл'],

            // Антипротозойные препараты
            ['name' => 'Метронидазол 5%', 'price' => 380.00, 'quantity' => 110, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Тинидазол 10%', 'price' => 450.00, 'quantity' => 90, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Ронидазол 10%', 'price' => 520.00, 'quantity' => 75, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Толтразурил 5%', 'price' => 680.00, 'quantity' => 60, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Диклазурил 2.5%', 'price' => 890.00, 'quantity' => 45, 'prescription_required' => true, 'unit' => 'мл'],

            // Противогрибковые препараты
            ['name' => 'Кетоконазол 2%', 'price' => 580.00, 'quantity' => 85, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Итраконазол 1%', 'price' => 720.00, 'quantity' => 65, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Флуконазол 2%', 'price' => 650.00, 'quantity' => 70, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Гризеофульвин 5%', 'price' => 420.00, 'quantity' => 95, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Амфотерицин B 0.5%', 'price' => 1200.00, 'quantity' => 30, 'prescription_required' => true, 'unit' => 'мл'],

            // Антигистаминные препараты
            ['name' => 'Димедрол 1%', 'price' => 180.00, 'quantity' => 250, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Супрастин 2%', 'price' => 220.00, 'quantity' => 200, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Тавегил 0.1%', 'price' => 280.00, 'quantity' => 180, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Лоратадин 0.1%', 'price' => 320.00, 'quantity' => 160, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Цетиризин 0.1%', 'price' => 380.00, 'quantity' => 140, 'prescription_required' => false, 'unit' => 'мл'],

            // Витамины и минералы
            ['name' => 'Витамин B12 0.1%', 'price' => 150.00, 'quantity' => 300, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Витамин D3 0.05%', 'price' => 180.00, 'quantity' => 280, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Витамин E 5%', 'price' => 220.00, 'quantity' => 250, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Витамин A 0.1%', 'price' => 160.00, 'quantity' => 290, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Кальций глюконат 10%', 'price' => 120.00, 'quantity' => 350, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Магний сульфат 25%', 'price' => 90.00, 'quantity' => 400, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Железо декстран 10%', 'price' => 280.00, 'quantity' => 180, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Селен 0.1%', 'price' => 420.00, 'quantity' => 120, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Цинк 5%', 'price' => 180.00, 'quantity' => 260, 'prescription_required' => false, 'unit' => 'мл'],

            // Препараты для сердечно-сосудистой системы
            ['name' => 'Дигоксин 0.025%', 'price' => 450.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Верапамил 0.25%', 'price' => 380.00, 'quantity' => 95, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Амиодарон 5%', 'price' => 680.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Нитроглицерин 1%', 'price' => 320.00, 'quantity' => 120, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Эналаприл 0.1%', 'price' => 580.00, 'quantity' => 70, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для ЖКТ
            ['name' => 'Омепразол 2%', 'price' => 280.00, 'quantity' => 150, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Ранитидин 1%', 'price' => 220.00, 'quantity' => 180, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Фамотидин 0.5%', 'price' => 250.00, 'quantity' => 160, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Метоклопрамид 0.5%', 'price' => 180.00, 'quantity' => 200, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Домперидон 0.1%', 'price' => 320.00, 'quantity' => 130, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Лоперамид 0.1%', 'price' => 150.00, 'quantity' => 250, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Бисакодил 0.1%', 'price' => 120.00, 'quantity' => 280, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Лактулоза 66%', 'price' => 180.00, 'quantity' => 220, 'prescription_required' => false, 'unit' => 'мл'],

            // Мочегонные препараты
            ['name' => 'Фуросемид 5%', 'price' => 220.00, 'quantity' => 170, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Спиронолактон 2%', 'price' => 380.00, 'quantity' => 100, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Гидрохлоротиазид 2.5%', 'price' => 280.00, 'quantity' => 140, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Маннитол 20%', 'price' => 450.00, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для нервной системы
            ['name' => 'Фенобарбитал 5%', 'price' => 320.00, 'quantity' => 120, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Диазепам 0.5%', 'price' => 450.00, 'quantity' => 90, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Мидазолам 1%', 'price' => 580.00, 'quantity' => 70, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Кетамин 10%', 'price' => 680.00, 'quantity' => 60, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Ксилазин 2%', 'price' => 420.00, 'quantity' => 110, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Медетомидин 0.1%', 'price' => 720.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Ацепромазин 1%', 'price' => 380.00, 'quantity' => 100, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для эндокринной системы
            ['name' => 'Инсулин 40 ЕД/мл', 'price' => 580.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Левтироксин 0.1%', 'price' => 420.00, 'quantity' => 110, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Преднизолон 1%', 'price' => 280.00, 'quantity' => 150, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Дексаметазон 0.4%', 'price' => 320.00, 'quantity' => 130, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Триамцинолон 0.1%', 'price' => 380.00, 'quantity' => 100, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для глаз
            ['name' => 'Атропин 1%', 'price' => 220.00, 'quantity' => 180, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Тропикамид 1%', 'price' => 280.00, 'quantity' => 150, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Пилокарпин 1%', 'price' => 180.00, 'quantity' => 200, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Тимолол 0.5%', 'price' => 320.00, 'quantity' => 130, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Дорзоламид 2%', 'price' => 450.00, 'quantity' => 90, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для кожи
            ['name' => 'Бетаметазон 0.1%', 'price' => 280.00, 'quantity' => 160, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Гидрокортизон 1%', 'price' => 220.00, 'quantity' => 190, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Клотримазол 1%', 'price' => 180.00, 'quantity' => 220, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Миконазол 2%', 'price' => 250.00, 'quantity' => 170, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Салициловая кислота 2%', 'price' => 120.00, 'quantity' => 280, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Бензоил пероксид 5%', 'price' => 200.00, 'quantity' => 200, 'prescription_required' => false, 'unit' => 'мл'],

            // Растворы для инфузий
            ['name' => 'Натрия хлорид 0.9%', 'price' => 80.00, 'quantity' => 500, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Глюкоза 5%', 'price' => 90.00, 'quantity' => 450, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Рингер-лактат', 'price' => 120.00, 'quantity' => 400, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Гемодез 6%', 'price' => 180.00, 'quantity' => 300, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Реополиглюкин 10%', 'price' => 220.00, 'quantity' => 250, 'prescription_required' => false, 'unit' => 'мл'],

            // Препараты для репродуктивной системы
            ['name' => 'Окситоцин 10 ЕД/мл', 'price' => 280.00, 'quantity' => 150, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Простагландин F2α 0.1%', 'price' => 580.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Гонадотропин 1000 ЕД/мл', 'price' => 1200.00, 'quantity' => 40, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Прогестерон 2.5%', 'price' => 450.00, 'quantity' => 100, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Эстрадиол 0.1%', 'price' => 380.00, 'quantity' => 120, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для онкологии
            ['name' => 'Циклофосфамид 2%', 'price' => 1200.00, 'quantity' => 30, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Винкристин 0.1%', 'price' => 1800.00, 'quantity' => 20, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Доксорубицин 2%', 'price' => 2200.00, 'quantity' => 15, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Карбоплатин 10 мг/мл', 'price' => 2800.00, 'quantity' => 10, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Цисплатин 1 мг/мл', 'price' => 3200.00, 'quantity' => 8, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для экстренной помощи
            ['name' => 'Адреналин 0.1%', 'price' => 450.00, 'quantity' => 100, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Норадреналин 0.1%', 'price' => 520.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Допамин 4%', 'price' => 680.00, 'quantity' => 60, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Добутамин 12.5 мг/мл', 'price' => 720.00, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Атропин 0.1%', 'price' => 280.00, 'quantity' => 150, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Лидокаин 2%', 'price' => 180.00, 'quantity' => 200, 'prescription_required' => true, 'unit' => 'мл'],
            ['name' => 'Бупивакаин 0.5%', 'price' => 320.00, 'quantity' => 130, 'prescription_required' => true, 'unit' => 'мл'],

            // Препараты для диагностики
            ['name' => 'Флуоресцеин 1%', 'price' => 280.00, 'quantity' => 120, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Роза бенгал 1%', 'price' => 220.00, 'quantity' => 150, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Индоцианин зеленый 0.25%', 'price' => 580.00, 'quantity' => 70, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Метиленовый синий 1%', 'price' => 180.00, 'quantity' => 180, 'prescription_required' => false, 'unit' => 'мл'],
            ['name' => 'Эозин 1%', 'price' => 150.00, 'quantity' => 200, 'prescription_required' => false, 'unit' => 'мл']
        ];

        // Получаем все филиалы
        $branches = \App\Models\Branch::all();

        foreach ($drugs as $drug) {
            $unit = Unit::where('symbol', $drug['unit'])->first();
            
            if ($unit) {
                $createdDrug = Drug::firstOrCreate(
                    [
                        'name' => $drug['name'],
                        'unit_id' => $unit->id
                    ],
                    [
                        'price' => $drug['price'],
                        'prescription_required' => $drug['prescription_required']
                    ]
                );

                // Attach препарат ко всем филиалам с рандомным количеством
                foreach ($branches as $branch) {
                    // Проверяем, не прикреплен ли уже препарат к филиалу
                    if (!$createdDrug->branches()->where('branch_id', $branch->id)->exists()) {
                        $createdDrug->branches()->attach($branch->id, [
                            'quantity' => rand(50, 200), // Рандомное количество от 50 до 200
                        ]);
                    }
                }
            }
        }
    }
} 