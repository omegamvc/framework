<?php

declare(strict_types=1);

namespace Tests\Text;

use Omega\Macroable\Exceptions\MacroNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Text\Str;

#[CoversClass(MacroNotFoundException::class)]
#[CoversClass(Str::class)]
final class StrMacroTest extends TestCase
{
    /**
     * Test it can register string macro.
     *
     * @return void
     */
    public function testItCanRegisterStringMacro(): void
    {
        Str::macro('addPrefix', fn ($text, $prefix) => $prefix . $text);

        $this->assertEquals('i love laravel', Str::addPrefix('laravel', 'i love '));

        Str::resetMacro();
    }

    /**
     * Test it can throw error when macro not found.
     *
     * @return void
     */
    public function testItCanThrowErrorWhenMacroNotFound(): void
    {
        $this->expectException(MacroNotFoundException::class);
        Str::hay();
    }

    /**
     * Tes it can reset string macro.
     *
     * @return void
     */
    public function testItCanResetStringMacro(): void
    {
        Str::macro('addPrefix', fn ($text, $prefix) => $prefix . $text);

        $addPrefix = Str::addPrefix('a', 'b');
        $this->assertEquals('ba', $addPrefix);
        Str::resetMacro();

        $this->expectException(MacroNotFoundException::class);

        Str::addPrefix('a', 'b');
        Str::resetMacro();
    }
}
