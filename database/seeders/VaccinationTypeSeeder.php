<?php

namespace Database\Seeders;

use App\Models\VaccinationType;
use App\Models\Drug;
use Illuminate\Database\Seeder;

class VaccinationTypeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Реальные типы вакцинаций с правильными составами и дозировками
        $vaccinationTypes = $this->getVaccinationTypes();

        foreach ($vaccinationTypes as $typeData) {
            $drugs = $typeData['drugs'];
            unset($typeData['drugs']);
            
            $vaccinationType = VaccinationType::create($typeData);
            
            // Создаем препараты если их нет и привязываем к типу вакцинации
            foreach ($drugs as $drugData) {
                // Ищем существующий препарат или создаем новый
                $drug = Drug::firstOrCreate(
                    ['name' => $drugData['name']],
                    [
                        'price' => rand(200, 1500),
                        'quantity' => rand(50, 200),
                        'prescription_required' => true,
                        'unit_id' => 1, // Предполагаем, что единица измерения с ID 1 это "мл"
                    ]
                );
                
                // Привязываем препарат к типу вакцинации
                $vaccinationType->drugs()->attach($drug->id, [
                    'dosage' => $drugData['dosage'],
                    // Batch template отключен по требованию клиники
                    // 'batch_template' => 'LOT-' . strtoupper(substr(str_replace([' ', '-'], '', $drug->name), 0, 4)) . '-{DATE}',
                    'batch_template' => null,
                ]);
            }
        }
    }

    private function getVaccinationTypes(): array
    {
        return [
            // === СОБАКИ - ОСНОВНЫЕ КОМПЛЕКСНЫЕ ВАКЦИНЫ ===
            [
                'name' => 'Нобивак DHPPi + L',
                'price' => 1800,
                'description' => 'Комплексная вакцинация собак против чумы, аденовироза, парвовироза, парагриппа и лептоспироза',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Lepto', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Нобивак DHPPi + RL',
                'price' => 2200,
                'description' => 'Комплексная вакцинация собак с защитой от бешенства и лептоспироза',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Эурикан DHPPI2-L',
                'price' => 1650,
                'description' => 'Комплексная вакцинация собак французской вакциной против основных инфекций',
                'drugs' => [
                    ['name' => 'Eurican DHPPI2-L', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вангард Плюс 5 L4',
                'price' => 1750,
                'description' => 'Американская комплексная вакцина против 5 основных болезней собак',
                'drugs' => [
                    ['name' => 'Vanguard Plus 5 L4', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Астерион DHPPiL',
                'price' => 1450,
                'description' => 'Российская комплексная вакцина для собак',
                'drugs' => [
                    ['name' => 'Астерион DHPPiL', 'dosage' => 1.0],
                ]
            ],

            // === БЕШЕНСТВО ===
            [
                'name' => 'Нобивак Rabies',
                'price' => 950,
                'description' => 'Моновакцина против бешенства для собак и кошек',
                'drugs' => [
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Рабикан',
                'price' => 650,
                'description' => 'Российская инактивированная вакцина против бешенства',
                'drugs' => [
                    ['name' => 'Рабикан', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Дефенсор 3',
                'price' => 1100,
                'description' => 'Американская вакцина против бешенства с защитой на 3 года',
                'drugs' => [
                    ['name' => 'Defensor 3', 'dosage' => 1.0],
                ]
            ],

            // === ЩЕНКИ - СПЕЦИАЛЬНЫЕ СХЕМЫ ===
            [
                'name' => 'Нобивак Puppy DP',
                'price' => 1200,
                'description' => 'Живая вакцина для щенков с 4-6 недель против чумы и парвовироза',
                'drugs' => [
                    ['name' => 'Nobivac Puppy DP', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Первичная вакцинация щенков (6-8 недель)',
                'price' => 2500,
                'description' => 'Комплексная первичная вакцинация щенков по стандартной схеме',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Lepto', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вторая вакцинация щенков (10-12 недель)',
                'price' => 2800,
                'description' => 'Повторная вакцинация щенков с добавлением защиты от бешенства',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Эурикан Примо',
                'price' => 1350,
                'description' => 'Первичная вакцинация щенков с раннего возраста',
                'drugs' => [
                    ['name' => 'Eurican Primo', 'dosage' => 1.0],
                ]
            ],

            // === КОШКИ - КОМПЛЕКСНЫЕ ВАКЦИНЫ ===
            [
                'name' => 'Нобивак Tricat Trio',
                'price' => 1400,
                'description' => 'Комплексная вакцинация кошек против панлейкопении, ринотрахеита и калицивироза',
                'drugs' => [
                    ['name' => 'Nobivac Tricat Trio', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Нобивак Forcat',
                'price' => 1650,
                'description' => 'Четырехвалентная вакцина для кошек с защитой от хламидиоза',
                'drugs' => [
                    ['name' => 'Nobivac Forcat', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Феловакс 4',
                'price' => 1550,
                'description' => 'Американская четырехкомпонентная вакцина для кошек',
                'drugs' => [
                    ['name' => 'Fel-O-Vax 4', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Квадрикат',
                'price' => 1750,
                'description' => 'Французская вакцина против 4 основных инфекций кошек с защитой от бешенства',
                'drugs' => [
                    ['name' => 'Quadricat', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Мультифел-4',
                'price' => 950,
                'description' => 'Российская инактивированная вакцина для кошек',
                'drugs' => [
                    ['name' => 'Мультифел-4', 'dosage' => 1.0],
                ]
            ],

            // === КОТЯТА ===
            [
                'name' => 'Первичная вакцинация котят (8-9 недель)',
                'price' => 1800,
                'description' => 'Первая вакцинация котят без бешенства',
                'drugs' => [
                    ['name' => 'Nobivac Tricat Trio', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Повторная вакцинация котят (12 недель)',
                'price' => 2400,
                'description' => 'Вторая вакцинация котят с добавлением бешенства',
                'drugs' => [
                    ['name' => 'Nobivac Tricat Trio', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],

            // === СПЕЦИАЛЬНЫЕ ВАКЦИНЫ ===
            [
                'name' => 'Вакцинация против лептоспироза',
                'price' => 800,
                'description' => 'Моновакцина против лептоспироза собак',
                'drugs' => [
                    ['name' => 'Nobivac Lepto', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация против лейкемии кошек',
                'price' => 1200,
                'description' => 'Специальная вакцина против вирусной лейкемии кошек',
                'drugs' => [
                    ['name' => 'Nobivac FeLV', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация против болезни Лайма',
                'price' => 1350,
                'description' => 'Профилактика боррелиоза у собак в эндемичных районах',
                'drugs' => [
                    ['name' => 'LymeVax', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация против питомникового кашля',
                'price' => 1100,
                'description' => 'Интраназальная вакцина против бордетеллеза и парагриппа',
                'drugs' => [
                    ['name' => 'Nobivac Bb', 'dosage' => 0.5],
                ]
            ],

            // === ХОРЬКИ ===
            [
                'name' => 'Вакцинация хорьков CDV',
                'price' => 1450,
                'description' => 'Специальная вакцинация хорьков против чумы плотоядных',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 0.5],
                ]
            ],
            [
                'name' => 'Комплексная вакцинация хорьков',
                'price' => 2100,
                'description' => 'Полная вакцинация хорьков против чумы и бешенства',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 0.5],
                    ['name' => 'Nobivac Rabies', 'dosage' => 0.5],
                ]
            ],

            // === КРОЛИКИ ===
            [
                'name' => 'Вакцинация кроликов от ВГБК',
                'price' => 650,
                'description' => 'Вакцинация против вирусной геморрагической болезни кроликов',
                'drugs' => [
                    ['name' => 'Лапинизированная вакцина ВГБК', 'dosage' => 0.5],
                ]
            ],
            [
                'name' => 'Вакцинация кроликов от миксоматоза',
                'price' => 750,
                'description' => 'Живая вакцина против миксоматоза кроликов',
                'drugs' => [
                    ['name' => 'Вакцина против миксоматоза', 'dosage' => 0.5],
                ]
            ],
            [
                'name' => 'Комплексная вакцинация кроликов',
                'price' => 1200,
                'description' => 'Ассоциированная вакцина против ВГБК и миксоматоза',
                'drugs' => [
                    ['name' => 'Раббивак V', 'dosage' => 0.5],
                ]
            ],

            // === ПТИЦЫ ===
            [
                'name' => 'Вакцинация птиц от болезни Ньюкасла',
                'price' => 850,
                'description' => 'Живая вакцина для декоративных птиц против псевдочумы',
                'drugs' => [
                    ['name' => 'Ла-Сота', 'dosage' => 0.2],
                ]
            ],

            // === ПРЕМИУМ КОМПЛЕКСЫ ===
            [
                'name' => 'Премиум-вакцинация собак (Нобивак)',
                'price' => 3200,
                'description' => 'Полная защита собак препаратами премиум-класса',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                    ['name' => 'Nobivac Bb', 'dosage' => 0.5],
                ]
            ],
            [
                'name' => 'Премиум-вакцинация кошек',
                'price' => 2850,
                'description' => 'Максимальная защита кошек от всех основных инфекций',
                'drugs' => [
                    ['name' => 'Nobivac Forcat', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                    ['name' => 'Nobivac FeLV', 'dosage' => 1.0],
                ]
            ],

            // === РЕВАКЦИНАЦИИ ===
            [
                'name' => 'Ежегодная ревакцинация собак базовая',
                'price' => 1950,
                'description' => 'Стандартная ежегодная ревакцинация взрослых собак',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Ежегодная ревакцинация собак расширенная',
                'price' => 2450,
                'description' => 'Расширенная ревакцинация с дополнительной защитой',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                    ['name' => 'Nobivac Lepto', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Ежегодная ревакцинация кошек',
                'price' => 1850,
                'description' => 'Стандартная ревакцинация домашних кошек',
                'drugs' => [
                    ['name' => 'Nobivac Tricat Trio', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],

            // === ЭКСТРЕННАЯ ВАКЦИНАЦИЯ ===
            [
                'name' => 'Экстренная вакцинация против бешенства',
                'price' => 1500,
                'description' => 'Срочная вакцинация после контакта с подозрительным животным',
                'drugs' => [
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                    ['name' => 'Иммуноглобулин антирабический', 'dosage' => 2.0],
                ]
            ],

            // === ПЛЕМЕННОЕ РАЗВЕДЕНИЕ ===
            [
                'name' => 'Предродовая вакцинация сук',
                'price' => 2200,
                'description' => 'Специальная схема вакцинации беременных сук для защиты потомства',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],

            // === ДОПОЛНИТЕЛЬНЫЕ СХЕМЫ ===
            [
                'name' => 'Вакцинация перед поездкой за границу',
                'price' => 2750,
                'description' => 'Полная вакцинация согласно международным требованиям',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                    ['name' => 'Nobivac Lepto', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация для выставочных животных',
                'price' => 3100,
                'description' => 'Расширенная защита для животных, участвующих в выставках',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                    ['name' => 'Nobivac Bb', 'dosage' => 0.5],
                ]
            ],

            // === ЭКЗОТИЧЕСКИЕ ЖИВОТНЫЕ ===
            [
                'name' => 'Вакцинация енотов',
                'price' => 1950,
                'description' => 'Специальная схема для енотов-полоскунов',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация лисиц',
                'price' => 1750,
                'description' => 'Вакцинация домашних лисиц против основных инфекций',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],

            // === СПЕЦИАЛЬНЫЕ СЛУЧАИ ===
            [
                'name' => 'Вакцинация ослабленных животных',
                'price' => 2400,
                'description' => 'Щадящая схема для животных с ослабленным иммунитетом',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 0.5],
                    ['name' => 'Иммуномодулятор Иммунофан', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация пожилых животных',
                'price' => 2100,
                'description' => 'Адаптированная схема для животных старше 8 лет',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],

            // === РЕГИОНАЛЬНЫЕ ОСОБЕННОСТИ ===
            [
                'name' => 'Вакцинация от клещевых инфекций',
                'price' => 1650,
                'description' => 'Специальная защита в регионах с высоким риском клещей',
                'drugs' => [
                    ['name' => 'Пиродог', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Южная схема вакцинации',
                'price' => 2350,
                'description' => 'Расширенная защита для южных регионов с дополнительными рисками',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                    ['name' => 'Пиродог', 'dosage' => 1.0],
                ]
            ],

            // === КОМБИНИРОВАННЫЕ СХЕМЫ ===
            [
                'name' => 'Мини-пород схема',
                'price' => 1400,
                'description' => 'Специальная дозировка для собак весом менее 5 кг',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 0.5],
                    ['name' => 'Nobivac Rabies', 'dosage' => 0.5],
                ]
            ],
            [
                'name' => 'Гигантских пород схема',
                'price' => 2650,
                'description' => 'Усиленная защита для собак весом более 40 кг',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.5],
                    ['name' => 'Nobivac RL', 'dosage' => 1.5],
                ]
            ],

            // === ПРОФИЛАКТИЧЕСКИЕ КОМПЛЕКСЫ ===
            [
                'name' => 'Профилактика питомникового кашля + основная',
                'price' => 2750,
                'description' => 'Комплексная защита с акцентом на респираторные инфекции',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Bb', 'dosage' => 0.5],
                    ['name' => 'Nobivac Rabies', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Максимальная защита собак',
                'price' => 3950,
                'description' => 'Самый полный комплекс защиты от всех возможных инфекций',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                    ['name' => 'Nobivac Bb', 'dosage' => 0.5],
                    ['name' => 'LymeVax', 'dosage' => 1.0],
                    ['name' => 'Пиродог', 'dosage' => 1.0],
                ]
            ],

            // === РЕАБИЛИТАЦИОННЫЕ СХЕМЫ ===
            [
                'name' => 'Постболезненная вакцинация',
                'price' => 2200,
                'description' => 'Щадящая схема после перенесенных заболеваний',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 0.7],
                    ['name' => 'Иммуномодулятор Риботан', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация после операций',
                'price' => 1950,
                'description' => 'Восстановление иммунитета после хирургических вмешательств',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 0.8],
                    ['name' => 'Nobivac Rabies', 'dosage' => 0.8],
                ]
            ],

            // === СЕЗОННЫЕ СХЕМЫ ===
            [
                'name' => 'Предзимняя вакцинация',
                'price' => 2100,
                'description' => 'Усиленная защита перед холодным сезоном',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac Bb', 'dosage' => 0.5],
                ]
            ],
            [
                'name' => 'Весенняя ревакцинация',
                'price' => 2400,
                'description' => 'Обновление защиты перед активным сезоном',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Nobivac RL', 'dosage' => 1.0],
                ]
            ],

            // === СПЕЦИАЛИЗИРОВАННЫЕ ВАКЦИНЫ ===
            [
                'name' => 'Вакцинация от дерматофитозов',
                'price' => 1350,
                'description' => 'Лечебно-профилактическая вакцинация против грибковых инфекций',
                'drugs' => [
                    ['name' => 'Микродерм', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Противоопухолевая вакцинация',
                'price' => 4500,
                'description' => 'Экспериментальная иммунотерапия онкологических заболеваний',
                'drugs' => [
                    ['name' => 'Онковакцина-ВК2', 'dosage' => 0.5],
                    ['name' => 'Иммуномодулятор Полиоксидоний', 'dosage' => 1.0],
                ]
            ],

            // === ИМПОРТНЫЕ ПРЕМИУМ ВАКЦИНЫ ===
            [
                'name' => 'Версикан DHPPi/L4R',
                'price' => 2650,
                'description' => 'Французская премиум-вакцина нового поколения',
                'drugs' => [
                    ['name' => 'Versican DHPPi/L4R', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Канген DHP-LR',
                'price' => 2450,
                'description' => 'Американская высокоэффективная вакцина',
                'drugs' => [
                    ['name' => 'Canigen DHP-LR', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Биовета DHPPi+L+R',
                'price' => 2150,
                'description' => 'Чешская комплексная вакцина проверенного качества',
                'drugs' => [
                    ['name' => 'Bioveta DHPPi+L+R', 'dosage' => 1.0],
                ]
            ],

            // === РЕДКИЕ СХЕМЫ ===
            [
                'name' => 'Аллергикам адаптированная',
                'price' => 2750,
                'description' => 'Специальная гипоаллергенная схема вакцинации',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 0.3],
                    ['name' => 'Антигистаминный препарат', 'dosage' => 1.0],
                    ['name' => 'Дексаметазон', 'dosage' => 0.1],
                ]
            ],
            [
                'name' => 'Экспресс-иммунизация',
                'price' => 3200,
                'description' => 'Ускоренная схема для срочных случаев',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.2],
                    ['name' => 'Иммуностимулятор Максидин', 'dosage' => 1.0],
                ]
            ],

            // === КОМБИНИРОВАННЫЕ ТЕРАПИИ ===
            [
                'name' => 'Вакцинация + дегельминтизация',
                'price' => 1850,
                'description' => 'Комплексная защита от инфекций и паразитов',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Мильбемакс', 'dosage' => 1.0],
                ]
            ],
            [
                'name' => 'Вакцинация + витаминотерапия',
                'price' => 2050,
                'description' => 'Вакцинация с поддержкой витаминами',
                'drugs' => [
                    ['name' => 'Nobivac DHPPi', 'dosage' => 1.0],
                    ['name' => 'Гамавит', 'dosage' => 2.0],
                ]
            ],
        ];
    }
}