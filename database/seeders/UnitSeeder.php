<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Миллиграмм', 'symbol' => 'мг'],
            ['name' => 'Грамм', 'symbol' => 'г'],
            ['name' => 'Килограмм', 'symbol' => 'кг'],
            ['name' => 'Миллилитр', 'symbol' => 'мл'],
            ['name' => 'Литр', 'symbol' => 'л'],
            ['name' => 'Штука', 'symbol' => 'шт'],
            ['name' => 'Ампула', 'symbol' => 'амп'],
            ['name' => 'Таблетка', 'symbol' => 'таб'],
            ['name' => 'Капсула', 'symbol' => 'капс'],
            ['name' => 'Упаковка', 'symbol' => 'уп'],
            ['name' => 'Грамм на литр', 'symbol' => 'г/л'],
            ['name' => 'Миллимоль на литр', 'symbol' => 'ммоль/л'],
            ['name' => 'Микромоль на литр', 'symbol' => 'мкмоль/л'],
            ['name' => 'Пикомоль на литр', 'symbol' => 'пмоль/л'],
            ['name' => 'Единица на литр', 'symbol' => 'Ед/л'],
            ['name' => 'Миллиединица на миллилитр', 'symbol' => 'мЕд/мл'],
            ['name' => 'Микроединица на миллилитр', 'symbol' => 'мкЕд/мл'],
            ['name' => 'Нанограмм на миллилитр', 'symbol' => 'нг/мл'],
            ['name' => 'Микроединица на литр', 'symbol' => 'мкЕд/л'],
            ['name' => 'Наномоль на литр', 'symbol' => 'нмоль/л'],
            ['name' => 'Процент', 'symbol' => '%'],
            ['name' => 'Миллиметр в час', 'symbol' => 'мм/ч'],
            ['name' => '10 в 12 степени на литр', 'symbol' => '10^12/л'],
            ['name' => '10 в 9 степени на литр', 'symbol' => '10^9/л'],
            ['name' => 'В поле зрения', 'symbol' => 'в п/з'],
            ['name' => 'КОЕ на миллилитр', 'symbol' => 'КОЕ/мл'],
            ['name' => 'МЕ на миллилитр', 'symbol' => 'МЕ/мл'],
        ];

        Unit::insert($units);
    }
}
