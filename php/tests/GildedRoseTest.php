<?php

declare(strict_types=1);

namespace Tests;

use GildedRose\GildedRose;
use GildedRose\Item;
use PHPUnit\Framework\TestCase;

class GildedRoseTest extends TestCase
{
    public function testBaseItem(): void
    {
        $items = [
            new Item('Elixir of the Mongoose', 0, 0),
            new Item('Elixir of the Mongoose', -1, 20),
        ];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();
        $this->assertSame(0, $items[0]->quality); //Качество не может быть ниже 0
        $this->assertSame(18, $items[1]->quality); //При снижении срока хранения ниже 0, качество уменьшается в 2 раза быстрее
    }

    public function testSulfurasItem(): void
    {
        $items = [new Item('Sulfuras, Hand of Ragnaros', -1, 50)];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();
        $this->assertSame(-1, $items[0]->sellIn); //Срок хранения всегда остается неизменным
        $this->assertSame(80, $items[0]->quality); //Качество всегда равно 80
    }

    public function testConjuredItem(): void
    {
        $items = [new Item('Conjured Mana Cake', 1, 4)];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();
        $this->assertSame(2, $items[0]->quality); //Качество уменьшается в 2 раза быстрее чем у обычных товаров
    }

    public function testAgedBrieItem(): void
    {
        $items = [
            new Item('Aged Brie', 2, 0),
            new Item('Aged Brie', -1, 0),
            new Item('Aged Brie', -1, 49),
        ];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();
        $this->assertSame(1, $items[0]->quality); //Качество увеличивается пропорционально возрасту
        $this->assertSame(2, $items[1]->quality); //Качество увеличивается пропорционально возрасту
        $this->assertSame(50, $items[2]->quality); //Качество не может быть больше 50
    }

    public function testBackstagePassesItem(): void
    {
        $items = [
            new Item('Backstage passes to a TAFKAL80ETC concert', 15, 0),
            new Item('Backstage passes to a TAFKAL80ETC concert', 10, 0),
            new Item('Backstage passes to a TAFKAL80ETC concert', 5, 0),
            new Item('Backstage passes to a TAFKAL80ETC concert', 0, 50),
        ];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateQuality();
        $this->assertSame(1, $items[0]->quality); //Качество увеличивается по мере приближения к сроку хранения на 1
        $this->assertSame(2, $items[1]->quality); //Качество увеличивается на 2, когда до истечения срока хранения 10 или менее дней
        $this->assertSame(3, $items[2]->quality); //Качество увеличивается на 3, если до истечения 5 или менее дней
        $this->assertSame(0, $items[3]->quality); //Качество падает до 0 после даты проведения концерта
    }
}
