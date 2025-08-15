<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Style\Alert;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;
use function shell_exec;

/**
 * @property string $port
 * @property bool   $expose
 */
class ServeCommand extends Command
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
                'port'   => 8080,
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
                'serve' => 'Serve server with port number (default 8080)',
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

    public function main(): void
    {
        $port    = $this->port;
        $localIP = gethostbyname(gethostname());

        $print = new Style('Server running add:');

        $print
            ->newLines()
            ->push('Local')->tabs()->push("http://localhost:$port")->textBlue();

        if ($this->expose) {
            $print->newLines()->push('Network')->tabs()->push("http://$localIP:$port")->textBlue();
        }

        $print
            ->newLines(2)
            ->push('ctrl+c to stop server')
            ->newLines()
            ->tap(Alert::render()->info('server running...'))
            ->out(false);

        $address = $this->expose ? '0.0.0.0' : '127.0.0.1';

        shell_exec("php -S {$address}:{$port} -t public/");
    }
}
