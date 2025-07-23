<?php

declare(strict_types=1);

namespace Tests\Text;

use PHPUnit\Framework\TestCase;
use Omega\Text\Regex;
use Omega\Text\Text;

class TextAPITest extends TestCase
{
    /** @var Text */
    private $text;

    protected function setUp(): void
    {
        $this->text = new Text('i love symfony');
    }

    protected function tearDown(): void
    {
        $this->text->reset();
    }

    // api test ----------------------------

    /** @test */
    public function testItCanReturnChartAt()
    {
        $this->assertEquals('o', $this->text->chartAt(3));
    }

    /** @test */
    public function testItCanReturnSlice()
    {
        $this->assertEquals('symfony', $this->text->slice(7));
    }

    /** @test */
    public function testItCanReturnLower()
    {
        $this->assertEquals('i love symfony', $this->text->lower());
    }

    /** @test */
    public function testItCanReturnUpper()
    {
        $this->assertEquals('I LOVE SYMFONY', $this->text->upper());
    }

    /** @test */
    public function testItCanReturnFirstUpper()
    {
        $this->assertEquals('I love symfony', $this->text->firstUpper());
    }

    /** @test */
    public function testItCanReturnFirstUpperAll()
    {
        $this->assertEquals('I Love Symfony', $this->text->firstUpperAll());
    }

    /** @test */
    public function testItCanReturnSnack()
    {
        $this->assertEquals('i_love_symfony', $this->text->snack());
    }

    /** @test */
    public function testItCanReturnKebab()
    {
        $this->assertEquals('i-love-symfony', $this->text->kebab());
    }

    /** @test */
    public function testItCanReturnPascal()
    {
        $this->assertEquals('ILoveSymfony', $this->text->pascal());
    }

    /** @test */
    public function testItCanReturnCamel()
    {
        $this->assertEquals('iLoveSymfony', $this->text->camel());
    }

    /** @test */
    public function testItCanReturnSlug()
    {
        $this->assertEquals('i-love-symfony', $this->text->slug());
    }

    // bool ------------------------------

    /** @test */
    public function testItCanReturnIsEmpty()
    {
        $this->assertFalse($this->text->isEmpty());
    }

    /** @test */
    public function testItCanReturnIs()
    {
        $this->assertFalse($this->text->is(Regex::USER));
    }

    /** @test */
    public function testItCanReturnContains()
    {
        $this->assertTrue($this->text->contains('love'));
    }

    /** @test */
    public function testItCanReturnStartsWith()
    {
        $this->assertTrue($this->text->startsWith('i love'));
    }

    /** @test */
    public function testItCanReturnEndsWith()
    {
        $this->assertTrue($this->text->endsWith('symfony'));
    }

    // int ---------------------------------------

    /** @test */
    public function testItCanReturnLength()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(14, $this->text->length());
    }

    /** @test */
    public function testItCanReturnIndexOf()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(7, $this->text->indexOf('symfony'));
    }

    /** @test */
    public function testItCanReturnLastIndexOf()
    {
        $this->assertIsInt($this->text->length());
        $this->assertEquals(3, $this->text->indexOf('o'));
    }

    /** @test */
    public function testItCanReturnFill()
    {
        $this->text->text('1234');
        $this->assertEquals('001234', $this->text->fill('0', 6));
    }

    /** @test */
    public function testItCanReturnFillEnd()
    {
        $this->text->text('1234');
        $this->assertEquals('123400', $this->text->fillEnd('0', 6));
    }

    /** @test */
    public function testItCanReturnMask()
    {
        $this->text->text('laravel');
        $this->assertEquals('l****el', $this->text->mask('*', 1, 4));

        $this->text->text('laravel');
        $this->assertEquals('l******', $this->text->mask('*', 1));

        $this->text->text('laravel');
        $this->assertEquals('lara*el', $this->text->mask('*', -3, 1));

        $this->text->text('laravel');
        $this->assertEquals('lara***', $this->text->mask('*', -3));
    }

    public function testItCanReturnLimit()
    {
        $this->text->text('laravel');
        $this->assertEquals('laravel...', (string) $this->text->limit(7));
    }

    /** @test */
    public function testItCanReturnAfetText()
    {
        $this->assertEquals('symfony', $this->text->after('love ')->__toString());
    }
}
