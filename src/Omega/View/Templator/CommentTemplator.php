<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Omega\View\AbstractTemplatorParse;

class CommentTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        return preg_replace(
            '/{#\s*(.*?)\s*#}/',
            '<?php /* $1 */ ?>',
            $template
        );
    }
}
