<?php

namespace Database\Seeders;

use App\Models\DictionarySymptom;
use Illuminate\Database\Seeder;

class DictionarySymptomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $symptoms = [
            ['name' => 'Рвота', 'description' => 'Рефлекторное извержение содержимого желудка'],
            ['name' => 'Диарея', 'description' => 'Частый жидкий стул'],
            ['name' => 'Запор', 'description' => 'Затрудненное или редкое опорожнение кишечника'],
            ['name' => 'Кашель', 'description' => 'Рефлекторный акт очищения дыхательных путей'],
            ['name' => 'Чихание', 'description' => 'Рефлекторный акт очищения носовых ходов'],
            ['name' => 'Хромота', 'description' => 'Нарушение походки из-за боли или повреждения'],
            ['name' => 'Зуд', 'description' => 'Ощущение раздражения кожи'],
            ['name' => 'Выпадение шерсти', 'description' => 'Потеря волосяного покрова'],
            ['name' => 'Повышенная температура', 'description' => 'Температура тела выше нормы'],
            ['name' => 'Пониженная температура', 'description' => 'Температура тела ниже нормы'],
            ['name' => 'Вялость', 'description' => 'Снижение активности и энергии'],
            ['name' => 'Агрессивность', 'description' => 'Повышенная раздражительность и агрессия'],
            ['name' => 'Отсутствие аппетита', 'description' => 'Отказ от пищи'],
            ['name' => 'Повышенный аппетит', 'description' => 'Увеличенное потребление пищи'],
            ['name' => 'Жажда', 'description' => 'Повышенное потребление воды'],
            ['name' => 'Частое мочеиспускание', 'description' => 'Увеличенная частота мочеиспускания'],
            ['name' => 'Затрудненное мочеиспускание', 'description' => 'Болезненное или затрудненное мочеиспускание'],
            ['name' => 'Кровь в моче', 'description' => 'Наличие крови в моче'],
            ['name' => 'Кровь в кале', 'description' => 'Наличие крови в кале'],
            ['name' => 'Слюнотечение', 'description' => 'Повышенное выделение слюны'],
            ['name' => 'Покраснение глаз', 'description' => 'Покраснение слизистой оболочки глаз'],
            ['name' => 'Выделения из глаз', 'description' => 'Гнойные или слизистые выделения из глаз'],
            ['name' => 'Выделения из носа', 'description' => 'Слизистые или гнойные выделения из носа'],
            ['name' => 'Повреждение кожи', 'description' => 'Раны, царапины или другие повреждения кожи'],
            ['name' => 'Отеки', 'description' => 'Скопление жидкости в тканях'],
            ['name' => 'Судороги', 'description' => 'Непроизвольные мышечные сокращения'],
            ['name' => 'Паралич', 'description' => 'Потеря двигательной функции'],
            ['name' => 'Нарушение координации', 'description' => 'Проблемы с равновесием и движением'],
            ['name' => 'Изменение голоса', 'description' => 'Изменение звучания голоса'],
            ['name' => 'Затрудненное дыхание', 'description' => 'Одышка или затрудненное дыхание'],
            ['name' => 'Учащенное дыхание', 'description' => 'Повышенная частота дыхания'],
            ['name' => 'Бледность слизистых', 'description' => 'Бледность десен и других слизистых'],
            ['name' => 'Желтушность', 'description' => 'Желтое окрашивание кожи и слизистых'],
            ['name' => 'Потеря веса', 'description' => 'Снижение массы тела'],
            ['name' => 'Увеличение веса', 'description' => 'Повышение массы тела'],
            ['name' => 'Проблемы с зубами', 'description' => 'Болезни зубов и десен'],
            ['name' => 'Проблемы с ушами', 'description' => 'Болезни ушей'],
            ['name' => 'Паразиты', 'description' => 'Наличие внешних или внутренних паразитов'],
            ['name' => 'Тремор', 'description' => 'Дрожание конечностей или всего тела'],
            ['name' => 'Слепота', 'description' => 'Потеря зрения'],
            ['name' => 'Глухота', 'description' => 'Потеря слуха'],
            ['name' => 'Анорексия', 'description' => 'Полный отказ от пищи'],
            ['name' => 'Полифагия', 'description' => 'Патологически повышенный аппетит'],
            ['name' => 'Полидипсия', 'description' => 'Патологически повышенная жажда'],
            ['name' => 'Полиурия', 'description' => 'Патологически повышенное мочеиспускание'],
            ['name' => 'Олигурия', 'description' => 'Сниженное мочеиспускание'],
            ['name' => 'Анурия', 'description' => 'Отсутствие мочеиспускания'],
            ['name' => 'Дисфагия', 'description' => 'Затрудненное глотание'],
            ['name' => 'Одышка', 'description' => 'Затрудненное дыхание'],
            ['name' => 'Тахипноэ', 'description' => 'Учащенное дыхание'],
            ['name' => 'Брадипноэ', 'description' => 'Замедленное дыхание'],
            ['name' => 'Тахикардия', 'description' => 'Учащенное сердцебиение'],
            ['name' => 'Брадикардия', 'description' => 'Замедленное сердцебиение'],
            ['name' => 'Аритмия', 'description' => 'Нарушение сердечного ритма'],
            ['name' => 'Цианоз', 'description' => 'Синюшность кожи и слизистых'],
            ['name' => 'Анемия', 'description' => 'Снижение уровня гемоглобина в крови'],
            ['name' => 'Лихорадка', 'description' => 'Повышение температуры тела с ознобом'],
            ['name' => 'Гипотермия', 'description' => 'Снижение температуры тела'],
            ['name' => 'Гипертермия', 'description' => 'Повышение температуры тела'],
            ['name' => 'Асцит', 'description' => 'Скопление жидкости в брюшной полости'],
            ['name' => 'Плевральный выпот', 'description' => 'Скопление жидкости в плевральной полости'],
            ['name' => 'Перикардиальный выпот', 'description' => 'Скопление жидкости в перикарде'],
            ['name' => 'Лимфаденопатия', 'description' => 'Увеличение лимфатических узлов'],
            ['name' => 'Спленомегалия', 'description' => 'Увеличение селезенки'],
            ['name' => 'Гепатомегалия', 'description' => 'Увеличение печени'],
            ['name' => 'Желтуха', 'description' => 'Желтое окрашивание кожи и слизистых из-за повышения билирубина'],
            ['name' => 'Метеоризм', 'description' => 'Вздутие живота из-за скопления газов'],
            ['name' => 'Колики', 'description' => 'Приступообразные боли в животе'],
            ['name' => 'Тенезмы', 'description' => 'Болезненные позывы к дефекации'],
            ['name' => 'Недержание мочи', 'description' => 'Непроизвольное мочеиспускание'],
            ['name' => 'Недержание кала', 'description' => 'Непроизвольная дефекация'],
            ['name' => 'Задержка мочи', 'description' => 'Невозможность мочеиспускания'],
            ['name' => 'Задержка кала', 'description' => 'Невозможность дефекации'],
            ['name' => 'Ректальное кровотечение', 'description' => 'Кровотечение из прямой кишки'],
            ['name' => 'Носовое кровотечение', 'description' => 'Кровотечение из носа'],
            ['name' => 'Кровотечение из ушей', 'description' => 'Кровотечение из слуховых проходов'],
            ['name' => 'Кровотечение из глаз', 'description' => 'Кровотечение из глаз'],
            ['name' => 'Кровотечение из рта', 'description' => 'Кровотечение из ротовой полости'],
            ['name' => 'Кровотечение из влагалища', 'description' => 'Кровотечение из половых путей у самок'],
            ['name' => 'Кровотечение из препуция', 'description' => 'Кровотечение из препуция у самцов'],
            ['name' => 'Выделения из влагалища', 'description' => 'Патологические выделения из половых путей у самок'],
            ['name' => 'Выделения из препуция', 'description' => 'Патологические выделения из препуция у самцов'],
            ['name' => 'Бесплодие', 'description' => 'Невозможность размножения'],
            ['name' => 'Аборт', 'description' => 'Прерывание беременности'],
            ['name' => 'Преждевременные роды', 'description' => 'Роды раньше срока'],
            ['name' => 'Затрудненные роды', 'description' => 'Осложненные роды'],
            ['name' => 'Мастит', 'description' => 'Воспаление молочных желез'],
            ['name' => 'Агалактия', 'description' => 'Отсутствие молока у кормящих самок'],
            ['name' => 'Галакторея', 'description' => 'Патологическое выделение молока'],
            ['name' => 'Орхит', 'description' => 'Воспаление яичек у самцов'],
            ['name' => 'Простатит', 'description' => 'Воспаление предстательной железы у самцов'],
            ['name' => 'Баланопостит', 'description' => 'Воспаление головки полового члена и препуция у самцов'],
            ['name' => 'Вагинит', 'description' => 'Воспаление влагалища у самок'],
            ['name' => 'Метрит', 'description' => 'Воспаление матки у самок'],
            ['name' => 'Пиометра', 'description' => 'Скопление гноя в матке у самок'],
            ['name' => 'Крипторхизм', 'description' => 'Неопущение яичек в мошонку у самцов'],
            ['name' => 'Фимоз', 'description' => 'Сужение отверстия препуция у самцов'],
            ['name' => 'Парафимоз', 'description' => 'Ущемление головки полового члена препуцием у самцов'],
            ['name' => 'Гипоспадия', 'description' => 'Аномалия развития уретры у самцов'],
            ['name' => 'Эписпадия', 'description' => 'Аномалия развития уретры'],
            ['name' => 'Мочекаменная болезнь', 'description' => 'Образование камней в мочевыводящих путях'],
            ['name' => 'Желчнокаменная болезнь', 'description' => 'Образование камней в желчном пузыре'],
            ['name' => 'Панкреолитиаз', 'description' => 'Образование камней в поджелудочной железе'],
            ['name' => 'Слюннокаменная болезнь', 'description' => 'Образование камней в слюнных железах'],
            ['name' => 'Зубной камень', 'description' => 'Отложение минеральных веществ на зубах'],
            ['name' => 'Кариес', 'description' => 'Разрушение твердых тканей зубов'],
            ['name' => 'Пульпит', 'description' => 'Воспаление пульпы зуба'],
            ['name' => 'Периодонтит', 'description' => 'Воспаление тканей вокруг корня зуба'],
            ['name' => 'Абсцесс зуба', 'description' => 'Гнойное воспаление тканей вокруг зуба'],
            ['name' => 'Перелом зуба', 'description' => 'Нарушение целостности зуба'],
            ['name' => 'Вывих зуба', 'description' => 'Смещение зуба из лунки'],
            ['name' => 'Резорбция корня', 'description' => 'Рассасывание корня зуба'],
            ['name' => 'Гиперплазия десен', 'description' => 'Разрастание тканей десен'],
            ['name' => 'Рецессия десен', 'description' => 'Опущение десен'],
            ['name' => 'Стоматит', 'description' => 'Воспаление слизистой оболочки рта'],
            ['name' => 'Глоссит', 'description' => 'Воспаление языка'],
            ['name' => 'Хейлит', 'description' => 'Воспаление губ'],
            ['name' => 'Гингивит', 'description' => 'Воспаление десен'],
            ['name' => 'Пародонтит', 'description' => 'Воспаление тканей пародонта'],
            ['name' => 'Пародонтоз', 'description' => 'Дистрофическое поражение пародонта'],
            ['name' => 'Остеомиелит', 'description' => 'Воспаление костного мозга и кости'],
            ['name' => 'Периостит', 'description' => 'Воспаление надкостницы'],
            ['name' => 'Артрит', 'description' => 'Воспаление суставов'],
            ['name' => 'Артроз', 'description' => 'Дегенеративное заболевание суставов'],
            ['name' => 'Бурсит', 'description' => 'Воспаление суставной сумки'],
            ['name' => 'Тендинит', 'description' => 'Воспаление сухожилий'],
            ['name' => 'Миозит', 'description' => 'Воспаление мышц'],
            ['name' => 'Миопатия', 'description' => 'Заболевание мышц'],
            ['name' => 'Неврит', 'description' => 'Воспаление нервов'],
            ['name' => 'Невралгия', 'description' => 'Боль по ходу нерва'],
            ['name' => 'Парез', 'description' => 'Снижение двигательной функции'],
            ['name' => 'Атаксия', 'description' => 'Нарушение координации движений'],
            ['name' => 'Эпилепсия', 'description' => 'Неврологическое заболевание с приступами'],
            ['name' => 'Менингит', 'description' => 'Воспаление оболочек головного мозга'],
            ['name' => 'Миелит', 'description' => 'Воспаление спинного мозга'],
            ['name' => 'Полиневрит', 'description' => 'Воспаление множественных нервов'],
            ['name' => 'Грыжа межпозвоночного диска', 'description' => 'Выпячивание межпозвоночного диска'],
            ['name' => 'Спондилез', 'description' => 'Дегенеративное заболевание позвоночника'],
            ['name' => 'Спондилит', 'description' => 'Воспаление позвонков'],
            ['name' => 'Сколиоз', 'description' => 'Искривление позвоночника'],
            ['name' => 'Кифоз', 'description' => 'Искривление позвоночника в грудном отделе'],
            ['name' => 'Лордоз', 'description' => 'Искривление позвоночника в поясничном отделе'],
            ['name' => 'Ушиб', 'description' => 'Закрытое повреждение тканей'],
            ['name' => 'Рана', 'description' => 'Открытое повреждение тканей'],
            ['name' => 'Ожог', 'description' => 'Повреждение тканей термическим, химическим или электрическим фактором'],
            ['name' => 'Отморожение', 'description' => 'Повреждение тканей низкой температурой'],
            ['name' => 'Укус', 'description' => 'Повреждение тканей зубами'],
            ['name' => 'Царапина', 'description' => 'Поверхностное повреждение кожи'],
            ['name' => 'Ссадина', 'description' => 'Поверхностное повреждение кожи с нарушением целостности'],
            ['name' => 'Фурункул', 'description' => 'Гнойное воспаление волосяного фолликула'],
            ['name' => 'Карбункул', 'description' => 'Гнойное воспаление нескольких волосяных фолликулов'],
            ['name' => 'Гидраденит', 'description' => 'Гнойное воспаление потовых желез'],
            ['name' => 'Лимфаденит', 'description' => 'Воспаление лимфатических узлов'],
            ['name' => 'Лимфангит', 'description' => 'Воспаление лимфатических сосудов'],
            ['name' => 'Тромбофлебит', 'description' => 'Воспаление вен с образованием тромбов'],
            ['name' => 'Флебит', 'description' => 'Воспаление вен'],
            ['name' => 'Артериит', 'description' => 'Воспаление артерий'],
            ['name' => 'Аневризма', 'description' => 'Расширение кровеносного сосуда'],
            ['name' => 'Тромбоз', 'description' => 'Образование тромбов в кровеносных сосудах'],
            ['name' => 'Эмболия', 'description' => 'Закупорка кровеносного сосуда эмболом'],
            ['name' => 'Варикоз', 'description' => 'Расширение вен'],
            ['name' => 'Геморрой', 'description' => 'Расширение вен прямой кишки'],
            ['name' => 'Атеросклероз', 'description' => 'Отложение холестерина в стенках артерий'],
            ['name' => 'Гипертония', 'description' => 'Повышенное артериальное давление'],
            ['name' => 'Гипотония', 'description' => 'Пониженное артериальное давление'],
            ['name' => 'Экстрасистолия', 'description' => 'Внеочередные сердечные сокращения'],
            ['name' => 'Фибрилляция предсердий', 'description' => 'Мерцательная аритмия'],
            ['name' => 'Фибрилляция желудочков', 'description' => 'Желудочковая аритмия'],
            ['name' => 'Блокада сердца', 'description' => 'Нарушение проведения импульсов в сердце'],
            ['name' => 'Кардиомиопатия', 'description' => 'Заболевание сердечной мышцы'],
            ['name' => 'Миокардит', 'description' => 'Воспаление сердечной мышцы'],
            ['name' => 'Эндокардит', 'description' => 'Воспаление внутренней оболочки сердца'],
            ['name' => 'Перикардит', 'description' => 'Воспаление наружной оболочки сердца'],
            ['name' => 'Порок сердца', 'description' => 'Врожденное или приобретенное нарушение строения сердца'],
            ['name' => 'Стеноз клапанов', 'description' => 'Сужение отверстий клапанов сердца'],
            ['name' => 'Недостаточность клапанов', 'description' => 'Неполное закрытие клапанов сердца'],
            ['name' => 'Пролапс клапанов', 'description' => 'Выпячивание створок клапанов сердца'],
            ['name' => 'Инфаркт миокарда', 'description' => 'Омертвение участка сердечной мышцы'],
            ['name' => 'Стенокардия', 'description' => 'Боль в сердце при физической нагрузке'],
            ['name' => 'Ишемическая болезнь сердца', 'description' => 'Заболевание сердца из-за недостатка кровоснабжения'],
            ['name' => 'Ревматизм', 'description' => 'Системное воспалительное заболевание'],
            ['name' => 'Ревматоидный артрит', 'description' => 'Аутоиммунное заболевание суставов'],
            ['name' => 'Системная красная волчанка', 'description' => 'Аутоиммунное системное заболевание'],
            ['name' => 'Склеродермия', 'description' => 'Аутоиммунное заболевание соединительной ткани'],
            ['name' => 'Дерматомиозит', 'description' => 'Аутоиммунное заболевание мышц и кожи'],
            ['name' => 'Васкулит', 'description' => 'Воспаление кровеносных сосудов'],
            ['name' => 'Гранулематоз', 'description' => 'Образование гранулем в тканях'],
            ['name' => 'Саркоидоз', 'description' => 'Системное заболевание с образованием гранулем'],
            ['name' => 'Амилоидоз', 'description' => 'Отложение амилоида в тканях'],
            ['name' => 'Гемохроматоз', 'description' => 'Наследственное заболевание с накоплением железа'],
            ['name' => 'Болезнь Вильсона', 'description' => 'Наследственное заболевание с накоплением меди'],
            ['name' => 'Муковисцидоз', 'description' => 'Наследственное заболевание с поражением желез'],
            ['name' => 'Фенилкетонурия', 'description' => 'Наследственное нарушение обмена веществ'],
            ['name' => 'Галактоземия', 'description' => 'Наследственное нарушение обмена галактозы'],
            ['name' => 'Болезнь Гоше', 'description' => 'Наследственное заболевание с накоплением липидов'],
            ['name' => 'Болезнь Ниманна-Пика', 'description' => 'Наследственное заболевание с накоплением липидов'],
            ['name' => 'Болезнь Тея-Сакса', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Хантингтона', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Паркинсона', 'description' => 'Дегенеративное заболевание нервной системы'],
            ['name' => 'Болезнь Альцгеймера', 'description' => 'Дегенеративное заболевание головного мозга'],
            ['name' => 'Рассеянный склероз', 'description' => 'Аутоиммунное заболевание нервной системы'],
            ['name' => 'Боковой амиотрофический склероз', 'description' => 'Дегенеративное заболевание нервной системы'],
            ['name' => 'Миастения', 'description' => 'Аутоиммунное заболевание нервно-мышечной системы'],
            ['name' => 'Мышечная дистрофия', 'description' => 'Наследственное заболевание мышц'],
            ['name' => 'Спинальная мышечная атрофия', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Шарко-Мари-Тута', 'description' => 'Наследственное заболевание периферических нервов'],
            ['name' => 'Болезнь Фридрейха', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Атаксия-телеангиэктазия', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Крейтцфельдта-Якоба', 'description' => 'Прионное заболевание нервной системы'],
            ['name' => 'Болезнь Куру', 'description' => 'Прионное заболевание нервной системы'],
            ['name' => 'Синдром Герстмана-Штраусслера-Шейнкера', 'description' => 'Прионное заболевание нервной системы'],
            ['name' => 'Фатальная семейная бессонница', 'description' => 'Прионное заболевание нервной системы'],
            ['name' => 'Болезнь Каннавана', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Александера', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Пелицеуса-Мерцбахера', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Адренолейкодистрофия', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Метахроматическая лейкодистрофия', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Краббе', 'description' => 'Наследственное заболевание нервной системы'],
            ['name' => 'Болезнь Фабри', 'description' => 'Наследственное заболевание с накоплением липидов'],
            ['name' => 'Болезнь Помпе', 'description' => 'Наследственное заболевание с накоплением гликогена'],
            ['name' => 'Болезнь Мак-Ардла', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь Андерсена', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь Форбса', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь Герса', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь Томсона', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь Таруи', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь Кори', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь Херса', 'description' => 'Наследственное нарушение обмена гликогена'],
            ['name' => 'Болезнь фон Гирке', 'description' => 'Наследственное нарушение обмена гликогена']
        ];

        foreach ($symptoms as $symptom) {
            DictionarySymptom::firstOrCreate(
                ['name' => $symptom['name']],
                $symptom
            );
        }
    }
} 