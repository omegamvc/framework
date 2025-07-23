<?php

declare(strict_types=1);

namespace Tests\View\Templator;

use PHPUnit\Framework\TestCase;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

final class EachTest extends TestCase
{
    /**
     * @return void
     */
    public function testItCanRenderEach()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach $numbers as $number %}{{ $number }}{% endforeach %}');
        $this->assertEquals(
            '<?php foreach ($numbers as $number ): ?><?php echo htmlspecialchars($number ); ?><?php endforeach; ?>',
            $out
        );
    }

    /**
     * @return void
     */
    public function testItCanRenderEachWithKeyValue()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $out       = $templator->templates('{% foreach $numbers as $key => $number %}{{ $number }}{% endforeach %}');
        $this->assertEquals(
            '<?php foreach ($numbers as $key  => $number ): ?><?php echo htmlspecialchars($number ); ?><?php endforeach; ?>',
            $out
        );
    }

    /**
     * @return void
     */
    public function testItCanRenderNestedEach()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $template  = '{% foreach $categories as $category %}{{ $category->name }}{% foreach $category->items as $item %}{{ $item->name }}{% endforeach %}{% endforeach %}';
        $expected  = '<?php foreach ($categories as $category ): ?><?php echo htmlspecialchars($category->name ); ?><?php foreach ($category->items as $item ): ?><?php echo htmlspecialchars($item->name ); ?><?php endforeach; ?><?php endforeach; ?>';

        $out = $templator->templates($template);
        $this->assertEquals($expected, $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderNestedEachWithKeyValue()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $template  = '{% foreach $data as $key => $values %}{{ $key }}{% foreach $values as $index => $item %}{{ $index }}: {{ $item }}{% endforeach %}{% endforeach %}';
        $expected  = '<?php foreach ($data as $key  => $values ): ?><?php echo htmlspecialchars($key ); ?><?php foreach ($values as $index  => $item ): ?><?php echo htmlspecialchars($index ); ?>: <?php echo htmlspecialchars($item ); ?><?php endforeach; ?><?php endforeach; ?>';

        $out = $templator->templates($template);
        $this->assertEquals($expected, $out);
    }

    /**
     * @return void
     */
    public function testItCanRenderMultipleForeachBlocks()
    {
        $templator = new Templator(new TemplatorFinder([__DIR__], ['']), __DIR__);
        $template  = '{% foreach $users as $user %}{{ $user->name }}{% endforeach %}{% foreach $products as $product %}{{ $product->name }}{% endforeach %}';
        $expected  = '<?php foreach ($users as $user ): ?><?php echo htmlspecialchars($user->name ); ?><?php endforeach; ?><?php foreach ($products as $product ): ?><?php echo htmlspecialchars($product->name ); ?><?php endforeach; ?>';

        $out = $templator->templates($template);
        $this->assertEquals($expected, $out);
    }
}
