<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\View\Exceptions\DirectiveCanNotBeRegisterException;
use Omega\View\Exceptions\DirectiveNotRegisterException;
use Omega\View\Templator;
use Omega\View\Templator\DirectiveTemplator;
use Omega\View\TemplatorFinder;

#[CoversClass(DirectiveCanNotBeRegisterException::class)]
#[CoversClass(DirectiveNotRegisterException::class)]
#[CoversClass(Templator::class)]
#[CoversClass(DirectiveTemplator::class)]
#[CoversClass(TemplatorFinder::class)]
final class DirectiveTest extends TestCase
{
    /**
     * test it cqn render each break
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderEachBreak(): void
    {
        DirectiveTemplator::register('sum', fn ($a, $b): int => $a + $b);
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% sum(1, 2) %}</body></html>');
        $this->assertEquals("<html><head></head><body><?php echo Omega\View\Templator\DirectiveTemplator::call('sum', 1, 2); ?></body></html>", $out); // phpcs:ignore
    }

    /**
     * Test it throw exception dur directive not register.
     *
     * @return void
     */
    public function testItThrowExceptionDueDirectiveNotRegister(): void
    {
        $this->expectException(DirectiveNotRegisterException::class);
        DirectiveTemplator::call('unknow', 0);
    }

    /**
     * Test it cn not register directive.
     *
     * @return void
     */
    public function testItCanNotRegisterDirective(): void
    {
        $this->expectException(DirectiveCanNotBeRegisterException::class);
        DirectiveTemplator::register('include', fn ($file): string => $file);
    }

    /**
     * Test it can register and call directive.
     *
     * @return void
     */
    public function testItCanRegisterAndCallDirective(): void
    {
        DirectiveTemplator::register('sum', fn ($a, $b): int => $a + $b);
        $this->assertEquals(2, DirectiveTemplator::call('sum', 1, 1));
    }
}
