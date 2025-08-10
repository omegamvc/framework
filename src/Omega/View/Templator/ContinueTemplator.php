<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Omega\View\AbstractTemplatorParse;

use function preg_replace;

class ContinueTemplator extends AbstractTemplatorParse
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $template): string
    {
        return preg_replace(
            '/\{%\s*continue\s*(\d*)\s*%\}/',
            '<?php continue $1; ?>',
            $template
        );
    }
}
