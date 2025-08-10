<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Omega\View\AbstractTemplatorParse;

use function preg_replace;

class EachTemplator extends AbstractTemplatorParse
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $template): string
    {
        $template = preg_replace(
            '/{%\s*foreach\s+([^%]+)\s+as\s+([^%]+)\s*=>\s*([^%]+)\s*%}/s',
            '<?php foreach ($1 as $2 => $3): ?>',
            $template
        );

        $template = preg_replace(
            '/{%\s*foreach\s+([^%]+)\s+as\s+([^%]+)\s*%}/s',
            '<?php foreach ($1 as $2): ?>',
            $template
        );

        $template = preg_replace(
            '/{%\s*endforeach\s*%}/s',
            '<?php endforeach; ?>',
            $template
        );

        return $template;
    }
}
