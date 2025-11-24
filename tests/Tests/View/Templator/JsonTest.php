<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

#[CoversClass(Templator::class)]
#[CoversClass(TemplatorFinder::class)]
final class JsonTest extends TestCase
{
    /**
     * Test it can render json.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderJson(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% json($data) %}</body></html>');
        $this->assertEquals(
            '<html><head></head><body><?php echo json_encode($data, 0 | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR, 512); ?></body></html>',
            $out
        );
    }

    /**
     * Test it can render json with optional params.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderJsonWithOptionalParam(): void
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('<html><head></head><body>{% json($data, 1, 500) %}</body></html>');
        $this->assertEquals(
            '<html><head></head><body><?php echo json_encode($data, 1 | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR, 500); ?></body></html>',
            $out
        );
    }
}
