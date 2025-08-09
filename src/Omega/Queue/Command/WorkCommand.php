<?php

/**
 * Part of Omega -  Queue Package.
 * php version 8.3
 *
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */

declare(strict_types=1);

namespace Omega\Queue\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Work command class.
 *
 * The `Work` class represents a unit of work to be  executed within a
 * queue system.  It encapsulates  a specific task or  job that can be
 * processed asynchronously. Instances  of this class  define the work
 * to be performed and can be enqueued in a queue manager for deferred
 * execution.
 *
 * @category    Omega
 * @package     Queue
 * @subpackage  Command
 * @link        https://omegamvc.github.io
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class WorkCommand extends Command
{
    /**
     * Default command name.
     *
     * @var string Holds the default command name.
     */
    protected static $defaultName = 'queue:work';

    /**
     * Command constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('queue');
    }

    /**
     * Configures the current command.
     *
     * This method configures the command description, options, and help information.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Runs tasks that have been queued.')
            ->setHelp('This command waits for and runs queue jobs.');
    }

    /**
     * Executes the current command.
     * *
     * This method executes the queue work, processing and completing jobs
     * that have been enqueued. It provides feedback on job completion and
     * handles exceptions gracefully, updating job status accordingly.
     *
     * @param InputInterface  $input  Holds an instance of InputInterface.
     * @param OutputInterface $output Holds an instance of OutputInterface.
     *
     * @return int Return 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Waiting for jobs.</info>');

        while (true) {
            if ($job = app('queue')->shift()) {
                try {
                    $job->run();

                    $output->writeln("<info>Completed $job->id</info>");

                    $job->is_complete = true;
                    $job->save();

                    return Command::SUCCESS;
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $output->writeln("<error>$message</error>");

                    $job->attempts = $job->attempts + 1;
                    $job->save();
                }
            }
        }
    }
}
