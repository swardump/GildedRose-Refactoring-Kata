### Руководство для запуска
Для запуска проекта необходимо:
* Php 8+
* Composer
* OS Linux

Cоздать локальную копии проекта
```
git clone https://github.com/swardump/GildedRose-Refactoring-Kata.git
```
или
```
git clone git@github.com:swardump/GildedRose-Refactoring-Kata.git
```

Установить зависимости проекта при помощи Composer
```
cd ./GildedRose-Refactoring-Kata/php
composer install
```
Зависимости которые будут установлены:
* PHPUnit
* ApprovalTests.PHP
* PHPStan
* Easy Coding Standard (ECS)

#### Тестирование
Запуск PHPUnit тестов
```
composer tests
```
Запуск Сoverage report
```
composer test-coverage
```
Отчет будет сформирован по пути `/builds/index.html`, необходимо открыть через браузер.
Так же необходима установка зависимости `XDebug` для генерации отчета.

#### Стандар кода
Проверка кода на соответствие стандарту PSR-12
```
composer check-cs
```

#### Статический анализ
Запуск PHPStan для статического анализа
```
composer phpstan
```