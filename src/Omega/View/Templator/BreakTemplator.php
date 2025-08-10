<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Omega\View\AbstractTemplatorParse;

use function preg_replace;

class BreakTemplator extends AbstractTemplatorParse
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $template): string
    {
        return preg_replace(
            '/\{%\s*break\s*(\d*)\s*%\}/',
            '<?php break $1; ?>',
            $template
        );
    }
}
