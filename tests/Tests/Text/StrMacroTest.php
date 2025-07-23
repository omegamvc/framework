<?php

declare(strict_types=1);

namespace Tests\Text;

use PHPUnit\Framework\TestCase;
use Omega\Macroable\Exceptions\MacroNotFoundException;
use Omega\Text\Str;

class StrMacroTest extends TestCase
{
    /** @test */
    public function testItCanRegisterStringMacro()
    {
        Str::macro('add_prefix', fn ($text, $prefix) => $prefix . $text);

        $this->assertEquals('i love laravel', Str::add_prefix('laravel', 'i love '));

        Str::resetMacro();
    }

    /** @test */
    public function testItCanThrowErrorWhenMacroNotFound()
    {
        $this->expectException(MacroNotFoundException::class);
        Str::hay();
    }

    /** @test */
    public function testIItCanResetStringMacro()
    {
        Str::macro('add_prefix', fn ($text, $prefix) => $prefix . $text);

        $add_prefix = Str::add_prefix('a', 'b');
        $this->assertEquals('ba', $add_prefix);
        Str::resetMacro();

        $this->expectException(MacroNotFoundException::class);

        Str::add_prefix('a', 'b');
        Str::resetMacro();
    }
}
