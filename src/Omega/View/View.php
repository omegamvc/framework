<?php

declare(strict_types=1);

namespace Omega\View;

use Omega\Http\Response;
use Omega\View\Exceptions\ViewFileNotFoundException;

use function file_exists;
use function ob_get_clean;
use function ob_start;

class View
{
    /**
     * Render view template with data.
     *
     * @param string               $viewPath View path location
     * @param array<string, mixed> $portal   Data to push
     * @return Response
     * @throws ViewFileNotFoundException
     */
    public static function render(string $viewPath, array $portal = []): Response
    {
        if (!file_exists($viewPath)) {
            throw new ViewFileNotFoundException($viewPath);
        }

        $auth         = new Portal($portal['auth'] ?? []);
        $meta         = new Portal($portal['meta'] ?? []);
        $content      = new Portal($portal['contents'] ?? []);

        // get render content
        ob_start();
        require_once $viewPath;
        $html = ob_get_clean();

        // send render content to client
        return new Response()
            ->setContent($html)
            ->setResponseCode(Response::HTTP_OK)
            ->removeHeaders([
                'Expires',
                'Pragma',
                'X-Powered-By',
                'Connection',
                'Server',
            ]);
    }
}
