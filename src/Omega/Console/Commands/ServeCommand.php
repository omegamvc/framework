<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\AbstractCommand;
use Omega\Console\Style\Alert;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;

use function Omega\Console\error;
use function shell_exec;

/**
 * @property string  $port
 * @property bool $expose
 */
class ServeCommand extends AbstractCommand
{
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'serve',
            'fn'      => [ServeCommand::class, 'main'],
            'default' => [
                'port'   => 8000,
                'expose' => false,
            ],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'serve' => 'Serve server with port number (default 8000)',
            ],
            'options'   => [
                '--port'   => 'Serve with custom port',
                '--expose' => 'Make server run public network',
            ],
            'relation'  => [
                'serve' => ['--port', '--expose'],
            ],
        ];
    }

    public function main(): int
    {
        $port = $this->port;
        if (!is_numeric($port)) {
            error("Port must be numeric");
            return 0;
        }

        $this->launchServer((int)$port, $this->expose);
        return 1;
    }

    private function launchServer(int $port, bool $expose): void
    {
        $localIP = gethostbyname(gethostname());

        $print = new Style('Server running at:');
        $print
            ->newLines()
            ->push('Local')->tabs()->push("http://localhost:$port")->textBlue();

        if ($expose) {
            $print->newLines()->push('Network')->tabs()->push("http://$localIP:$port")->textBlue();
        }

        $print
            ->newLines(2)
            ->push('ctrl+c to stop server')
            ->newLines()
            ->tap(Alert::render()->info('server running...'))
            ->out(false);

        $address = $expose ? '0.0.0.0' : '127.0.0.1';
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        shell_exec("php -S {$address}:{$port} -t public/");
    }
}
