<?php

declare(strict_types=1);

namespace Tests\Text;

use PHPUnit\Framework\TestCase;
use Omega\Text\Str;
use Omega\Text\Text;

use function Omega\Text\string;
use function Omega\Text\text;

class TextTest extends TestCase
{
    /** @test */
    public function testItCanCreateNewIntanceUsingConstructor()
    {
        $class = new Text('text');

        $this->assertInstanceOf(Text::class, $class);
    }

    /** @test */
    public function testItCanCreateNewIntanceUsingHelper()
    {
        $this->assertInstanceOf(Text::class, string('text'));
        $this->assertInstanceOf(Text::class, text('text'));
    }

    /** @test */
    public function testItCanCreateNewIntanceUsingSTRClass()
    {
        $this->assertInstanceOf(Text::class, Str::of('text'));
    }

    /** @test */
    public function testItCanSetGetCurrentText()
    {
        $class = new Text('text');

        $this->assertEquals('text', $class->getText());
    }

    /** @test */
    public function testItCanSetGetCurrentTextUsingToString()
    {
        $class = new Text('text');

        $this->assertEquals('text', $class);
    }

    /** @test */
    public function testItCanSetNewTextWhitoutReset()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->text('string');

        $this->assertEquals('string', $class->getText());
        $this->assertCount(5, $class->logs());
    }

    /** @test */
    public function testItCanSetGetLogOfString()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();

        $this->assertIsArray($class->logs());
        foreach ($class->logs() as $log) {
            $this->assertArrayHasKey('function', $log);
            $this->assertArrayHasKey('return', $log);
            $this->assertArrayHasKey('type', $log);

            if ($log['type'] === 'string') {
                $this->assertIsString($log['return']);
            }
        }
    }

    /** @test */
    public function testItCanSetReset()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->reset();

        $this->assertEquals('text', $class->getText());
        $this->assertEmpty($class->logs());
    }

    /** @test */
    public function testItCanSetRefresh()
    {
        $class = new Text('text');
        $class->upper()->lower()->firstUpper();
        $class->refresh('string');

        $this->assertEquals('string', $class->getText());
        $this->assertEmpty($class->logs());
    }

    /** @test */
    public function testItCanChainNonStringAndContinueChainWithoutBreak()
    {
        $class = new Text('text');
        $class->upper()->firstUpper();

        $this->assertTrue($class->startsWith('T'));
        $this->assertTrue($class->length() === 4);

        $class->lower();
        $this->assertTrue($class->startsWith('t'));
    }
}
