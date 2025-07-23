<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\Style;

use Omega\Console\Style\ProgressBar;
use Omega\Text\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;
use function range;

/**
 * Unit tests for the ProgressBar class.
 *
 * This test suite verifies that the ProgressBar can:
 * - Renders the progress bar correctly while ticking.
 * - Supports custom format placeholders such as ":custom".
 * - Interpolates dynamic values during progress updates.
 *
 * The tests assert that the visual representation of the bar changes over time,
 * and that dynamic text interpolation works as expected.
 *
 * @category   Omega\Tests
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(ProgressBar::class)]
class ProgressbarTest extends TestCase
{
    /**
     * Test it can render progress bar.
     *
     * @return void
     */
    public function testItCanRenderProgressbar(): void
    {
        $progressbar = new ProgressBar(':progress');
        $progressbar->maks = 10;
        ob_start();
        foreach (range(1, 10) as $ignored) {
            $progressbar->current++;
            $progressbar->tick();
        }
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, '[=>------------------]'));
        $this->assertTrue(Str::contains($out, '[=========>----------]'));
        $this->assertTrue(Str::contains($out, '[====================]'));
    }

    /**
     * Test it can render progress bar using custom tick.
     *
     * @return void
     * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
     */
    public function testItCanRenderProgressBarUsingCustomTick(): void
    {
        $progressbar = new ProgressBar(':progress');
        $progressbar->maks = 10;
        ob_start();
        foreach (range(1, 10) as $ignored) {
            $progressbar->current++;
            $progressbar->tickWith(':progress :custom', [
                ':custom' => fn(): string => "{$progressbar->current}/{$progressbar->maks}",
            ]);
        }
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, '[=>------------------] 1/10'));
        $this->assertTrue(Str::contains($out, '[=========>----------] 5/10'));
        $this->assertTrue(Str::contains($out, '[====================] 10/10'));
    }
}
