<?php

declare(strict_types=1);

namespace GildedRose;

/**
 * Интерфейс создания базового класса товаров
 */
interface ItemInterface
{
    public function decreaseSellIn(Item $item): void;

    public function decreaseQuality(Item $item): void;

    public function checkLastDateOfSale(int $sellIn): bool;

    public function checkMaxQuality(int $quality): int;

    public function checkMinQuality(int $quality): int;
}

/**
 * Интерфейс фабрики для получения соответствующих объектов
 */
interface FactoryItemInterface
{
    public function getItem(Item $item): object;
}


/**
 * Базовый класс для обычных товаров, определяющий стандартные свойства и методы.
 */
class BaseItem implements ItemInterface
{
    public int $qualityIndex = 1; //Индекс отслеживающий изменения качества товаров

    public function __construct(
        public Item $item
    ) {
        $this->decreaseSellIn($item);
        $this->decreaseQuality($item);
    }

    /**
     * Метод для проверки последнего дня продаж
     */
    public function checkLastDateOfSale(int $sellIn): bool
    {
        return $sellIn < 0 ?: false;
    }

    /**
     * Метод для проверки максимального качества, не должно превышать 50
     */
    public function checkMaxQuality(int $quality): int
    {
        if ($quality > 50) {
            $quality = 50;
            return $quality;
        }
        return $quality;
    }

    /**
     * Метод для проверки минимального качества, не должно быть меньше 0
     */
    public function checkMinQuality(int $quality): int
    {
        if ($quality < 0) {
            $quality = 0;
            return $quality;
        }
        return $quality;
    }

    /**
     * Метод уменьшения срока хранения
     */
    public function decreaseSellIn(Item $item): void
    {
        --$item->sellIn;
    }

    /**
     * Метод уменьшения качества товара.
     * При достижении последнего дня продаж товар теряет качество в 2 раза быстрее.
     */
    public function decreaseQuality(Item $item): void
    {
        if ($this->checkLastDateOfSale($item->sellIn)) {
            $item->quality = $this->checkMinQuality($item->quality - $this->qualityIndex * 2);
            return;
        }

        $item->quality = $this->checkMinQuality($item->quality - $this->qualityIndex);
    }
}

/**
 * «Sulfuras» является легендарным товаром, поэтому у него нет срока хранения и не подвержен ухудшению качества;
 * легендарный товар «Sulfuras» имеет качество 80 и оно никогда не меняется.
 */
class Sulfuras extends BaseItem
{
    private const QUALITY = 80; //Для легендарного товара качество являестя постоянно равным 80

    public function __construct(
        public Item $item
    ) {
        $item->quality = self::QUALITY;
    }
}

/**
 * «Conjured» товары теряют качество в два раза быстрее, чем обычные товары.
 */
class Conjured extends BaseItem
{
    public function __construct(
        public Item $item
    ) {
        $this->qualityIndex *= 2;
        parent::__construct($item);
    }
}

/**
 * Определение свойств качества аналогично с классом BackstagePasses.
 * Для товара «Aged Brie» качество увеличивается пропорционально возрасту;
 */
class AgedBrie extends BaseItem
{
    public function __construct(
        public Item $item
    ) {
        $this->decreaseSellIn($item);
        $this->increaseQuality($item);
    }

    public function increaseQuality(Item $item): void
    {
        if ($this->checkLastDateOfSale($item->sellIn)) {
            $item->quality = $this->checkMaxQuality($item->quality + $this->qualityIndex * 2);
            return;
        }

        $item->quality = $this->checkMaxQuality($item->quality + $this->qualityIndex);
    }
}

/**
 * Качество «Backstage passes» также, как и «Aged Brie», увеличивается по мере приближения к сроку хранения.
 * Качество увеличивается на 2, когда до истечения срока хранения 10 или менее дней и на 3,
 * если до истечения 5 или менее дней. При этом качество падает до 0 после даты проведения концерта.
 */
class BackstagePasses extends BaseItem
{
    public function __construct(
        public Item $item
    ) {
        $this->decreaseSellIn($item);
        $this->increaseQuality($item);
    }

    public function increaseQuality(Item $item): void
    {
        if ($this->checkLastDateOfSale($item->sellIn)) {
            $item->quality = 0;
            return;
        }
        if ($item->sellIn < 6) {
            $item->quality = $this->checkMaxQuality($item->quality + $this->qualityIndex * 3);
            return;
        }
        if ($item->sellIn < 11) {
            $item->quality = $this->checkMaxQuality($item->quality + $this->qualityIndex * 2);
            return;
        }

        $item->quality = $this->checkMaxQuality($item->quality + $this->qualityIndex);
    }
}

/**
 * Реализация интерфейса.
 * Фабрика для получения объектов товаров.
 */
final class FactoryItem implements FactoryItemInterface
{
    public function __construct(
        public Item $item
    ) {
        $this->getItem($item);
    }

    public function getItem(Item $item): object
    {
        return match (true) {
            str_starts_with($item->name, 'Sulfuras') => new Sulfuras($item),
            str_starts_with($item->name, 'Conjured') => new Conjured($item),
            str_starts_with($item->name, 'Aged Brie') => new AgedBrie($item),
            str_starts_with($item->name, 'Backstage passes') => new BackstagePasses($item),
            default => new BaseItem($item)
        };
    }
}

final class GildedRose
{
    /**
     * @param Item[] $items
     */
    public function __construct(
        private array $items
    ) {
    }

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            $item = new FactoryItem($item);
        }
    }
}
