<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Omega\View\AbstractTemplatorParse;

class ContinueTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        return preg_replace(
            '/\{%\s*continue\s*(\d*)\s*%\}/',
            '<?php continue $1; ?>',
            $template
        );
    }
}
