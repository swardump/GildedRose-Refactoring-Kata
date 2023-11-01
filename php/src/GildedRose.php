<?php

declare(strict_types=1);

namespace GildedRose;

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
            $item = new EntityFactory($item);
        }
    }
}

/**
 * Класс управляющий созданием объектов в зависимости от наименования товара.
 */
final class EntityFactory
{
    public function __construct(
        public Item $item
    ) {
        $item = match (true) {
            str_starts_with($item->name, 'Sulfuras') => $this->getSulfuras($item),
            str_starts_with($item->name, 'Conjured') => $this->getConjured($item),
            str_starts_with($item->name, 'Aged Brie') => $this->getAgedBrie($item),
            str_starts_with($item->name, 'Backstage passes') => $this->getBackstagePasses($item),
            default => $this->getBaseItem($item)
        };
    }

    private function getSulfuras(Item $item): object
    {
        return new Sulfuras($item);
    }

    private function getConjured(Item $item): object
    {
        return new Conjured($item);
    }

    private function getAgedBrie(Item $item): object
    {
        return new AgedBrie($item);
    }

    private function getBackstagePasses(Item $item): object
    {
        return new BackstagePasses($item);
    }

    private function getBaseItem(Item $item): object
    {
        return new BaseItem($item);
    }
}

/**
 * Базовый класс для обычных товаров, определяющий стандартные свойства и методы.
 */
class BaseItem
{
    public int $index = 1;

    public function __construct(
        public Item $item
    ) {
        $this->decreaseQuality($item);
        $this->decreaseSellIn($item);
    }

    protected function increaseQuality(Item $item): void
    {
        if ($item->quality < 50) {
            $item->quality = $item->quality + $this->index;
        }
    }

    protected function decreaseQuality(Item $item): void
    {
        if ($item->quality > 0) {
            $item->quality = $item->quality - $this->index;
        }
    }

    protected function decreaseSellIn(Item $item): void
    {
        if ($item->sellIn > 0) {
            $item->sellIn = $item->sellIn - $this->index;
        } else {
            $item->sellIn = $item->sellIn - $this->index;
            $this->decreaseQuality($item);
        }
    }
}

/**
 * Данный класс не требует наследования от базового класса итемов по условиям задачи.
 * т.к. качество качество всегда является постоянным значением = 80, независимо от переданного значения в конструктор,
 * срок хранения остается равным переданному значению в конструкторю
 * «Sulfuras» является легендарным товаром, поэтому у него нет срока хранения и не подвержен ухудшению качества;
 * легендарный товар «Sulfuras» имеет качество 80 и оно никогда не меняется.
 */
class Sulfuras extends BaseItem
{
    private const QUALITY = 80;

    public function __construct(
        public Item $item
    ) {
        $item->quality = self::QUALITY;
    }
}

/**
 * Наследуем свойства и методы обычных товаров, увеличивая индекс качества в два раза, в соответствии с условиями задачи.
 * «Conjured» товары теряют качество в два раза быстрее, чем обычные товары.
 */
class Conjured extends BaseItem
{
    public function __construct(
        public Item $item
    ) {
        $this->decreaseSellIn($item);
        $this->decreaseQuality($item);
        $this->decreaseQuality($item);
    }
}


/**
 * Наследуем свойства и методы обычных товаров. Переопределяем функцию increaseQuality.
 * т.к. необходимы дополнительные условия для изменения качества.
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

    protected function increaseQuality(Item $item): void
    {
        if ($item->sellIn >= 0 && $item->quality < 50) {
            $item->quality = $item->quality + $this->index;
        }
        if ($item->sellIn < 0 && $item->quality < 50) {
            $item->quality = $item->quality + $this->index * 2 > 50 ? 50 : $item->quality + $this->index * 2;
        }
    }

    protected function decreaseSellIn(Item $item): void
    {
        $item->sellIn = $item->sellIn - 1;
    }
}


/**
 * Наследуем свойства и методы обычных товаров, переопределяем функции increaseQuality и decreaseSellIn,
 * т.к. необходимы дополнительные условия для изменения качества.
 * Качество «Backstage passes» также, как и «Aged Brie», увеличивается по мере приближения к сроку хранения.
 * Качество увеличивается на 2, когда до истечения срока хранения 10 или менее дней и на 3,
 * если до истечения 5 или менее дней. При этом качество падает до 0 после даты проведения концерта.
 */
class BackstagePasses extends BaseItem
{
    public function __construct(
        public Item $item
    ) {
        $this->increaseQuality($item);
        $this->decreaseSellIn($item);
    }

    protected function increaseQuality(Item $item): void
    {
        if ($item->sellIn < 6) {
            $item->quality = $item->quality + $this->index * 3 > 50 ? 50 : $item->quality + $this->index * 3;
        } elseif ($item->sellIn < 11) {
            $item->quality = $item->quality + $this->index * 2 > 50 ? 50 : $item->quality + $this->index * 2;
        } else {
            $item->quality = $item->quality + $this->index >= 50 ? 50 : $item->quality + $this->index;
        }
    }

    protected function decreaseSellIn(Item $item): void
    {
        if ($item->sellIn <= 0) {
            $item->quality = 0;
            $item->sellIn = $item->sellIn - $this->index;
        }
        if ($item->sellIn > 0) {
            $item->sellIn = $item->sellIn - $this->index;
        }
    }
}
