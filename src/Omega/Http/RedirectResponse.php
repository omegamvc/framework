<?php

declare(strict_types=1);

namespace Omega\Http;

use Exception;

use function htmlspecialchars;

use const ENT_QUOTES;

class RedirectResponse extends Response
{
    /**
     * @param string $url
     * @param int    $responseCode
     * @param array  $headers
     * @return void
     * @throws Exception
     */
    public function __construct(string $url, int $responseCode = 302, array $headers = [])
    {
        parent::__construct('', $responseCode, $headers);

        $this->setTarget($url);
    }

    /**
     * @param string $url
     * @return void
     * @throws Exception
     */
    public function setTarget(string $url): void
    {
        $this->setContent(
            sprintf(
                '<html lang="en">
                            <head>
                                <meta charset="UTF-8" />
                                <meta http-equiv="refresh" content="0;url=\'%1$s\'" />
                                    <title>Redirecting to %1$s</title>
                            </head>
                            <body>
                                Redirecting to <a href="%1$s">%1$s</a>.
                            </body>
                        </html>',
                htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
            )
        );
        $this->setHeaders([
            'Location' => $url
        ]);
    }
}
