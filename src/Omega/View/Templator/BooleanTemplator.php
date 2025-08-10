<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Omega\View\AbstractTemplatorParse;

use function preg_replace_callback;

final class BooleanTemplator extends AbstractTemplatorParse
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*bool\(\s*(.+?)\s*\)\s*%}/',
            static fn (array $matches): string => "<?= ({$matches[1]}) ? 'true' : 'false' ?>",
            $template
        );
    }
}
